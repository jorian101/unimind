<?php
/**
 * SyncController - Refactorizado con Database Singleton y ModelFactory
 */
session_start();
require_once __DIR__ . '/../utils/ModelFactory.php';
require_once __DIR__ . '/../database/Database.php';

/**
 * Endpoint simple de sincronización offline -> server
 * URL: controllers/SyncController.php
 * Método: POST (JSON body)
 * Body esperado: { items: [ { client_uuid, id_test, respuestas: {id_item: id_opcion}, meta?: {...} }, ... ] }
 */

header('Content-Type: application/json');

// Autenticación: sesiones PHP (cookies HttpOnly). Requerimos usuario autenticado.
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$MAX_BATCH_ITEMS = 200;
$MAX_PAYLOAD_SIZE = 512 * 1024; // 512 KB

$contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
if ($contentLength > $MAX_PAYLOAD_SIZE) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Payload demasiado grande']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

$items = $data['items'] ?? null;
if (!is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato inválido: se espera { items: [...] }']);
    exit;
}

if (count($items) > $MAX_BATCH_ITEMS) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Batch demasiado grande']);
    exit;
}

// Usar Database Singleton y ModelFactory
$conn = Database::getInstance()->getConnection();
$model = ModelFactory::create('estudiante', 'tests');

$responses = [];
$id_usuario = $_SESSION['id_usuario'];

// Logging: crear entrada inicial en sync_logs
$logId = null;
$startedAt = microtime(true);
try {
    $insertLog = $conn->prepare("INSERT INTO sync_logs (client_uuid, request_payload, status, created_at) VALUES (:client_uuid, :req, :status, NOW())");
    // store entire payload; client_uuid nullable at batch level
    $insertLog->bindValue(':client_uuid', null);
    $insertLog->bindValue(':req', $raw);
    $insertLog->bindValue(':status', 'processing');
    $insertLog->execute();
    $logId = $conn->lastInsertId();
} catch (Exception $e) {
    // If sync_logs table does not exist or insert fails, continue without logging
    $logId = null;
}

foreach ($items as $it) {
    $mapping = ['client_uuid' => $it['client_uuid'] ?? null, 'server_id' => null, 'status' => 'error', 'error' => null];

    // Basic validation
    if (!isset($it['id_test']) || !isset($it['respuestas'])) {
        $mapping['error'] = 'id_test o respuestas faltantes';
        $responses[] = $mapping;
        continue;
    }

    $client_uuid = $it['client_uuid'] ?? null;
    $id_test = (int)$it['id_test'];
    $respuestas = $it['respuestas'];

    try {
        // Start transaction per item to avoid partial inserts
        $conn->beginTransaction();

        // If Aplicaciones has client_uuid column, try idempotency
        $serverExistingId = null;
        $hasClientUuidColumn = false;
        try {
            $colCheck = $conn->query("SHOW COLUMNS FROM Aplicaciones LIKE 'client_uuid'")->fetch();
            $hasClientUuidColumn = $colCheck ? true : false;
        } catch (Exception $e) {
            // ignore
        }

        if ($client_uuid && $hasClientUuidColumn) {
            $stmt = $conn->prepare('SELECT id_aplicacion FROM Aplicaciones WHERE client_uuid = :cu LIMIT 1');
            $stmt->bindParam(':cu', $client_uuid);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row && isset($row['id_aplicacion'])) {
                $serverExistingId = (int)$row['id_aplicacion'];
            }
        }

        if ($serverExistingId) {
            // Already exists: nothing to do
            $mapping['server_id'] = $serverExistingId;
            $mapping['status'] = 'exists';
            $conn->commit();
            $responses[] = $mapping;
            continue;
        }

        // Create application
        $id_aplicacion = $model->iniciarAplicacion($id_usuario, $id_test);
        if (!$id_aplicacion) throw new Exception('No se pudo crear aplicacion');

        // Insert respuestas
        foreach ($respuestas as $id_item => $id_opcion) {
            $ok = $model->registrarRespuesta($id_aplicacion, (int)$id_item, (int)$id_opcion);
            if (!$ok) throw new Exception("Error al registrar respuesta item $id_item");
        }

        // Finalizar
        $resultado = $model->finalizarAplicacion($id_aplicacion);
        if ($resultado === null) throw new Exception('Error al finalizar aplicacion');

        // If client_uuid available, update record
        if ($client_uuid && $hasClientUuidColumn) {
            $upd = $conn->prepare('UPDATE Aplicaciones SET client_uuid = :cu WHERE id_aplicacion = :id');
            $upd->bindParam(':cu', $client_uuid);
            $upd->bindParam(':id', $id_aplicacion);
            $upd->execute();
        }

        $conn->commit();

        $mapping['server_id'] = $id_aplicacion;
        $mapping['status'] = 'created';
        $responses[] = $mapping;
    } catch (Exception $e) {
        try { $conn->rollBack(); } catch (Exception $_) {}
        $mapping['error'] = $e->getMessage();
        $responses[] = $mapping;
    }
}

// Actualizar log con resultado y duración si existe
$endedAt = microtime(true);
$durationMs = (int)(($endedAt - $startedAt) * 1000);
$overallStatus = 'success';
$errorMessages = [];
foreach ($responses as $r) {
    if (isset($r['status']) && $r['status'] === 'error') {
        $overallStatus = 'partial_error';
        if (isset($r['error'])) $errorMessages[] = $r['error'];
    }
}

try {
    if ($logId) {
        $upd = $conn->prepare('UPDATE sync_logs SET response_payload = :resp, status = :status, duration_ms = :dur, error_message = :errs WHERE id = :id');
        $respJson = json_encode(['mappings' => $responses]);
        $errs = !empty($errorMessages) ? implode(' | ', array_unique($errorMessages)) : null;
        $upd->bindParam(':resp', $respJson);
        $upd->bindParam(':status', $overallStatus);
        $upd->bindParam(':dur', $durationMs);
        $upd->bindParam(':errs', $errs);
        $upd->bindParam(':id', $logId);
        $upd->execute();
    }
} catch (Exception $_) {
    // ignore logging failures
}

echo json_encode(['success' => true, 'mappings' => $responses]);
exit;

?>
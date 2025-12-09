<?php
/**
 * CitasController
 * Controlador MVC para gestión de citas (estudiantes y admin)
 */

require_once __DIR__ . '/../database/Database.php';

class CitasController {
    
    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener citas por fecha (para admin)
     */
    public function getCitasPorFecha(string $fecha): array {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare('
            SELECT c.id_cita, c.fecha_cita, c.motivo, c.estado, u.nombre, u.apellido
            FROM Citas c
            JOIN Usuarios u ON c.id_alumno = u.id_usuario
            WHERE DATE(c.fecha_cita) = ?
            ORDER BY c.fecha_cita ASC
        ');
        $stmt->execute([$fecha]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener citas de un estudiante
     */
    public function getCitasEstudiante(int $idEstudiante): array {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("CALL sp_obtener_citas_por_alumno(?)");
        $stmt->execute([$idEstudiante]);
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $citas;
    }

    /**
     * Agendar cita
     */
    public function agendarCita(int $idAlumno, string $fechaCita, string $motivo): array {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("CALL sp_agendar_cita(?, ?, ?)");
        $stmt->execute([$idAlumno, $fechaCita, $motivo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $result;
    }

    /**
     * Actualizar cita
     */
    public function actualizarCita(int $idCita, int $idAlumno, string $fechaCita, string $motivo): bool {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE Citas 
            SET fecha_cita = ?, motivo = ? 
            WHERE id_cita = ? AND id_alumno = ? AND estado != 'cancelada'
        ");
        
        return $stmt->execute([$fechaCita, $motivo, $idCita, $idAlumno]);
    }

    /**
     * Cancelar cita
     */
    public function cancelarCita(int $idCita, int $idAlumno): bool {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE Citas 
            SET estado = 'cancelada' 
            WHERE id_cita = ? AND id_alumno = ? AND estado != 'cancelada'
        ");
        
        return $stmt->execute([$idCita, $idAlumno]);
    }

    // ========================================
    // MÉTODOS API - ADMIN
    // ========================================

    /**
     * API Admin: GET citas por fecha
     * Query params: ?fecha=YYYY-MM-DD
     */
    public function handleApiAdminGet(): void {
        header('Content-Type: application/json');
        
        try {
            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            $citas = $this->getCitasPorFecha($fecha);
            
            // Formatear para FullCalendar
            $citasFormateadas = [];
            foreach ($citas as $row) {
                $citasFormateadas[] = [
                    'id' => $row['id_cita'],
                    'title' => $row['nombre'] . ' ' . $row['apellido'],
                    'start' => $row['fecha_cita'],
                    'motivo' => $row['motivo'],
                    'estado' => $row['estado']
                ];
            }
            
            echo json_encode($citasFormateadas);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener citas: ' . $e->getMessage()]);
        }
    }

    // ========================================
    // MÉTODOS API - ESTUDIANTE
    // ========================================

    /**
     * API Estudiante: GET listar citas
     * Query params: ?action=list
     */
    private function handleApiEstudianteList(int $idAlumno): void {
        header('Content-Type: application/json');
        
        try {
            $citas = $this->getCitasEstudiante($idAlumno);
            echo json_encode($citas);
            
        } catch (Exception $e) {
            echo json_encode([]);
        }
    }

    /**
     * API Estudiante: POST agendar cita
     */
    private function handleApiEstudianteAgendar(int $idAlumno): void {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $fecha_cita = $data['fecha_cita'] ?? null;
        $motivo = $data['motivo'] ?? '';
        
        if (!$fecha_cita || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        try {
            $result = $this->agendarCita($idAlumno, $fecha_cita, $motivo);
            echo json_encode([
                'success' => true, 
                'message' => $result['Mensaje'] ?? 'Cita agendada', 
                'id_cita' => $result['id_cita'] ?? null
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al agendar cita: ' . $e->getMessage()]);
        }
    }

    /**
     * API Estudiante: POST editar cita
     */
    private function handleApiEstudianteEditar(int $idAlumno): void {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id_cita = $data['id_cita'] ?? null;
        $fecha_cita = $data['fecha_cita'] ?? null;
        $motivo = $data['motivo'] ?? '';
        
        if (!$id_cita || !$fecha_cita || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        try {
            $success = $this->actualizarCita($id_cita, $idAlumno, $fecha_cita, $motivo);
            echo json_encode([
                'success' => $success, 
                'message' => $success ? 'Cita actualizada' : 'No se pudo actualizar'
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cita: ' . $e->getMessage()]);
        }
    }

    /**
     * API Estudiante: POST cancelar cita
     */
    private function handleApiEstudianteCancelar(int $idAlumno): void {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $id_cita = $data['id_cita'] ?? null;
        
        if (!$id_cita) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        try {
            $success = $this->cancelarCita($id_cita, $idAlumno);
            echo json_encode([
                'success' => $success, 
                'message' => $success ? 'Cita cancelada' : 'No se pudo cancelar'
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar cita: ' . $e->getMessage()]);
        }
    }

    /**
     * Router para API de estudiantes
     */
    public function handleApiEstudianteRequest(): void {
        session_start();
        
        if (!isset($_SESSION['id_usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }
        
        $id_alumno = $_SESSION['id_usuario'];
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        if ($method === 'GET' && $action === 'list') {
            $this->handleApiEstudianteList($id_alumno);
            return;
        }

        if ($method === 'POST') {
            if ($action === 'editar') {
                $this->handleApiEstudianteEditar($id_alumno);
                return;
            } elseif ($action === 'cancelar') {
                $this->handleApiEstudianteCancelar($id_alumno);
                return;
            } else {
                // Acción por defecto: agendar
                $this->handleApiEstudianteAgendar($id_alumno);
                return;
            }
        }

        // Método no permitido
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
}

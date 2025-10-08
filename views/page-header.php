<?php
function getPageHeaderConfig($role, $page, $router, $additionalData = null) {
    $title = $router->getPageTitle($role, $page);
    $breadcrumb = $router->getBreadcrumbPath($role, $page);
    
    if ($additionalData) {
        return [
            'title' => $additionalData['name'] ?? $title,
            'breadcrumb' => array_merge($breadcrumb, [$additionalData['name'] ?? $title]),
            'subtitle' => $additionalData['code'] ?? $additionalData['description'] ?? '',
            'section' => $additionalData['section'] ?? $additionalData['category'] ?? '',
            'metadata' => $additionalData['metadata'] ?? []
        ];
    }

    return [
        'title' => $title,
        'breadcrumb' => $breadcrumb,
        'subtitle' => '',
        'section' => '',
        'metadata' => []
    ];
}

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? 'dashboard';

require_once dirname(__DIR__) . '/utils/Router.php';
$router = new Router();

$additionalData = null;
if (isset($_GET['course_id'])) {
    $additionalData = [
        'name' => '25-II ESIS INGENIERÍA WEB Y APLICACIONES MÓVILES–B F2',
        'code' => 'ING-WEB-2025-II',
        'section' => 'Sección B',
        'metadata' => [
            'profesor' => 'Dr. Juan Pérez',
            'creditos' => '4 créditos',
            'horario' => 'Lun-Vie 08:00-10:00'
        ]
    ];
} elseif (isset($_GET['project_id'])) {
    $additionalData = [
        'name' => 'Sistema de Gestión Académica',
        'description' => 'Proyecto Final de Carrera',
        'category' => 'Desarrollo Web',
        'metadata' => [
            'estado' => 'En Progreso',
            'fecha_entrega' => '15 Diciembre 2024'
        ]
    ];
}

$pageHeaderProps = getPageHeaderConfig($currentRole, $currentPage, $router, $additionalData);
?>

<header id="page-header" class="page-header">
    <div class="page-header__content">
        <?php if (count($pageHeaderProps['breadcrumb']) > 1): ?>
        <nav class="page-header__breadcrumb-nav">
            <?php foreach ($pageHeaderProps['breadcrumb'] as $index => $item): ?>
                <span class="page-header__breadcrumb-item <?php echo $index === count($pageHeaderProps['breadcrumb']) - 1 ? 'page-header__breadcrumb-item--current' : 'page-header__breadcrumb-item--navigable'; ?>"
                      data-breadcrumb-index="<?php echo $index; ?>"
                      data-breadcrumb-page="<?php echo strtolower(str_replace(' ', '-', $item)); ?>">
                    <?php echo htmlspecialchars($item); ?>
                </span>
                <?php if ($index < count($pageHeaderProps['breadcrumb']) - 1): ?>
                    <span class="page-header__breadcrumb-separator">›</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="page-header__title-section">
            <h1 class="page-header__title"><?php echo htmlspecialchars($pageHeaderProps['title']); ?></h1>
            
            <?php if (!empty($pageHeaderProps['subtitle'])): ?>
                <p class="page-header__subtitle"><?php echo htmlspecialchars($pageHeaderProps['subtitle']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($pageHeaderProps['section'])): ?>
                <span class="page-header__section-badge"><?php echo htmlspecialchars($pageHeaderProps['section']); ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($pageHeaderProps['metadata'])): ?>
        <div class="page-header__metadata">
            <?php foreach ($pageHeaderProps['metadata'] as $key => $value): ?>
                <span class="page-header__metadata-item">
                    <strong><?php echo htmlspecialchars(ucfirst($key)); ?>:</strong>
                    <?php echo htmlspecialchars($value); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="page-header__actions" id="page-header-actions">
    </div>
</header>
        <?php if (!empty($pageHeaderProps['metadata'])): ?>
        <div class="page-header__metadata">
            <?php foreach ($pageHeaderProps['metadata'] as $key => $value): ?>
                <span class="page-header__metadata-item">
                    <strong><?php echo htmlspecialchars(ucfirst($key)); ?>:</strong>
                    <?php echo htmlspecialchars($value); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</header>
    <!-- Acciones específicas de la página (opcionales) -->
    <div class="page-header__actions" id="page-header-actions">
        <!-- Las acciones se pueden agregar dinámicamente según la página -->
    </div>
</header>

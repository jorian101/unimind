<?php
require_once dirname(__DIR__) . '/page-header-component.php';
renderPageHeader('Inicio', ['Inicio']);
?>

<div class="inicio-content">
    <div class="welcome-section">
        <h2>¡Bienvenido a tu panel de estudiante!</h2>
        <p>Desde aquí puedes acceder a tus cursos, calendario, archivos y más.</p>
    </div>
    
    <div class="quick-actions">
        <div class="action-card">
            <i class="fas fa-book"></i>
            <h3>Mis Cursos</h3>
            <p>Accede a tus cursos activos y materiales.</p>
        </div>
        <div class="action-card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Calendario</h3>
            <p>Revisa tus citas y eventos programados.</p>
        </div>
        <div class="action-card">
            <i class="fas fa-folder-open"></i>
            <h3>Archivos Privados</h3>
            <p>Gestiona tus documentos personales.</p>
        </div>
        <div class="action-card">
            <i class="fas fa-columns"></i>
            <h3>Test de Estres y Personalidad</h3>
            <p>Accede a evaluaciones psicológicas.</p>
        </div>
    </div>
</div>

<style>
.inicio-content {
    max-width: 1000px;
    margin: 0 auto;
}

.welcome-section {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, var(--pri-50) 0%, var(--bg-100) 100%);
    border-radius: 12px;
    border: 1px solid var(--pri-200);
}

.welcome-section h2 {
    color: var(--pri-800);
    margin-bottom: 1rem;
}

.welcome-section p {
    color: var(--pri-600);
    font-size: 1.1rem;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.action-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid var(--bg-500);
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.action-card i {
    font-size: 2.5rem;
    color: var(--pri-500);
    margin-bottom: 1rem;
}

.action-card h3 {
    color: var(--pri-800);
    margin-bottom: 0.5rem;
}

.action-card p {
    color: var(--pri-600);
    font-size: 0.9rem;
}
</style>

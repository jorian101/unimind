<?php
require_once dirname(__DIR__) . '/./pageHeader.php';
renderPageHeader('Dashboard', ['Dashboard']);
?>

<div class="dashboard-content">
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h3>📝 Tests Pendientes</h3>
                <span class="badge urgent">2</span>
            </div>
            <div class="card-content">
                <p>Tienes 2 evaluaciones pendientes por completar</p>
                <button class="btn-primary">Ver Tests</button>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3>📅 Próximas Citas</h3>
                <span class="badge info">1</span>
            </div>
            <div class="card-content">
                <p>Consulta psicológica - 15 Oct, 10:00 AM</p>
                <button class="btn-secondary">Ver Calendario</button>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3>💡 Recomendaciones</h3>
                <span class="badge success">3</span>
            </div>
            <div class="card-content">
                <p>Nuevas recomendaciones personalizadas disponibles</p>
                <button class="btn-secondary">Ver Recomendaciones</button>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <h3>📈 Progreso</h3>
            </div>
            <div class="card-content">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 75%"></div>
                </div>
                <p>75% completado este mes</p>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-header h3 {
    margin: 0;
    color: var(--pri-700);
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: bold;
    color: white;
}

.badge.urgent {
    background: #e74c3c;
}

.badge.info {
    background: #3498db;
}

.badge.success {
    background: #27ae60;
}

.card-content p {
    margin: 0 0 1rem 0;
    color: #666;
}

.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-primary {
    background: var(--pri-500);
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.progress-bar {
    background: #e2e8f0;
    border-radius: 8px;
    height: 8px;
    margin-bottom: 0.5rem;
}

.progress-fill {
    background: var(--pri-500);
    height: 100%;
    border-radius: 8px;
    transition: width 0.3s ease;
}
</style>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.dashboard-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid var(--pri-500);
}

.dashboard-card h3 {
    margin: 0 0 1rem 0;
    color: var(--pri-700);
}
</style>
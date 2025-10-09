<?php
require_once dirname(__DIR__) . '/../utils/page-header-component.php';
renderPageHeader('Tests y Evaluaciones', ['Inicio', 'Tests y Evaluaciones']);
?>

<div class="tests-content">
    <div class="tests-list">
        <div class="test-item">
            <h3>🧠 Evaluación Cognitiva Básica</h3>
            <p>Estado: <span class="status pending">Pendiente</span></p>
            <button class="btn-primary">Iniciar Test</button>
        </div>
        <div class="test-item">
            <h3>📊 Test de Personalidad</h3>
            <p>Estado: <span class="status completed">Completado</span></p>
            <button class="btn-secondary">Ver Resultados</button>
        </div>
        <div class="test-item">
            <h3>🎯 Test de Concentración</h3>
            <p>Estado: <span class="status pending">Pendiente</span></p>
            <button class="btn-primary">Iniciar Test</button>
        </div>
    </div>
</div>

<style>
.tests-list {
    margin-top: 2rem;
}

.test-item {
    background: white;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.test-item h3 {
    margin: 0 0 1rem 0;
    color: var(--pri-700);
}

.status.pending {
    color: #e67e22;
    font-weight: bold;
}

.status.completed {
    color: #27ae60;
    font-weight: bold;
}

.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 1rem;
}

.btn-primary {
    background: var(--pri-500);
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}
</style>
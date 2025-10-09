<?php
require_once dirname(__DIR__) . '/./pageHeader.php';
renderPageHeader('Recomendaciones', ['Inicio', 'Recomendaciones']);
?>

<div class="recommendations-content">
    <div class="recommendations-list">
        <div class="recommendation-item">
            <div class="recommendation-header">
                <h3>🎯 Mejora en Concentración</h3>
                <span class="priority high">Alta Prioridad</span>
            </div>
            <p>Basado en tus resultados, recomendamos practicar técnicas de mindfulness durante 10 minutos diarios.</p>
            <div class="recommendation-actions">
                <button class="btn-primary">Ver Detalles</button>
                <button class="btn-secondary">Marcar como Vista</button>
            </div>
        </div>
        
        <div class="recommendation-item">
            <div class="recommendation-header">
                <h3>📚 Estrategias de Estudio</h3>
                <span class="priority medium">Prioridad Media</span>
            </div>
            <p>Te sugerimos implementar la técnica Pomodoro para optimizar tus sesiones de estudio.</p>
            <div class="recommendation-actions">
                <button class="btn-primary">Ver Detalles</button>
                <button class="btn-secondary">Marcar como Vista</button>
            </div>
        </div>
        
        <div class="recommendation-item">
            <div class="recommendation-header">
                <h3>🧘‍♀️ Gestión del Estrés</h3>
                <span class="priority medium">Prioridad Media</span>
            </div>
            <p>Considera incorporar ejercicios de respiración antes de los exámenes para reducir la ansiedad.</p>
            <div class="recommendation-actions">
                <button class="btn-primary">Ver Detalles</button>
                <button class="btn-secondary">Marcar como Vista</button>
            </div>
        </div>
    </div>
</div>

<style>
.recommendations-list {
    margin-top: 2rem;
}

.recommendation-item {
    background: white;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.recommendation-header h3 {
    margin: 0;
    color: var(--pri-700);
}

.priority.high {
    background: #e74c3c;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.priority.medium {
    background: #f39c12;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.recommendation-actions {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
}

.btn-primary, .btn-secondary {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
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
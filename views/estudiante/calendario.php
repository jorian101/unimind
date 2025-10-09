<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader('Calendario Académico', ['Inicio', 'Calendario']);
?>

<div class="calendar-content">
    <div class="calendar-header">
        <h2>📅 Mi Calendario Académico</h2>
        <p>Gestiona tus horarios de clases, exámenes y eventos académicos</p>
    </div>
    
    <div class="calendar-grid">
        <div class="calendar-section">
            <h3>Próximos Eventos</h3>
            
            <div class="event-item">
                <div class="event-date">
                    <div class="date-day">18</div>
                    <div class="date-month">OCT</div>
                </div>
                <div class="event-details">
                    <h4>Examen - Ingeniería Web</h4>
                    <p><strong>Hora:</strong> 8:00 AM - 10:00 AM</p>
                    <p><strong>Aula:</strong> Lab. 302</p>
                    <p><strong>Profesor:</strong> Ing. López</p>
                </div>
            </div>
            
            <div class="event-item">
                <div class="event-date">
                    <div class="date-day">20</div>
                    <div class="date-month">OCT</div>
                </div>
                <div class="event-details">
                    <h4>Entrega - Tesis I</h4>
                    <p><strong>Hora:</strong> 11:59 PM</p>
                    <p><strong>Tipo:</strong> Documento Digital</p>
                    <p><strong>Tutor:</strong> Dr. Ramírez</p>
                </div>
            </div>
        </div>
        
        <div class="calendar-section">
            <h3>Horario de Clases</h3>
            <div class="schedule-grid">
                <div class="schedule-item">
                    <strong>Lunes</strong>
                    <span>Ingeniería Web - 8:00 AM</span>
                    <span>Filosofía - 2:00 PM</span>
                </div>
                <div class="schedule-item">
                    <strong>Martes</strong>
                    <span>Tesis I - 10:00 AM</span>
                </div>
                <div class="schedule-item">
                    <strong>Miércoles</strong>
                    <span>Seguridad Informática - 8:00 AM</span>
                    <span>Ingeniería Web - 2:00 PM</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem;
    background: linear-gradient(135deg, var(--pri-50) 0%, var(--bg-100) 100%);
    border-radius: 12px;
    border: 1px solid var(--pri-200);
}

.calendar-header h2 {
    color: var(--pri-800);
    margin-bottom: 0.5rem;
}

.calendar-header p {
    color: var(--pri-600);
}

.calendar-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.calendar-section h3 {
    color: var(--pri-700);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--pri-200);
}

.event-item {
    background: white;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.event-date {
    background: var(--pri-500);
    color: white;
    padding: 0.75rem;
    border-radius: 8px;
    text-align: center;
    min-width: 60px;
}

.date-day {
    font-size: 1.25rem;
    font-weight: bold;
}

.date-month {
    font-size: 0.8rem;
}

.event-details h4 {
    margin: 0 0 0.5rem 0;
    color: var(--pri-700);
}

.event-details p {
    margin: 0.25rem 0;
    color: #666;
    font-size: 0.9rem;
}

.schedule-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.schedule-item {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.schedule-item strong {
    color: var(--pri-700);
    font-size: 1.1rem;
}

.schedule-item span {
    color: #666;
    font-size: 0.9rem;
    padding: 0.25rem 0;
}

@media (max-width: 768px) {
    .calendar-grid {
        grid-template-columns: 1fr;
    }
    
    .event-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>
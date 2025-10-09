<?php
require_once dirname(__DIR__) . '/../utils/page-header-component.php';
renderPageHeader('Calendario de Citas', ['Inicio', 'Calendario de Citas']);
?>

<div class="calendar-content">
    <div class="calendar-header">
        <button class="btn-primary">📝 Agendar Nueva Cita</button>
    </div>
    
    <div class="appointments-list">
        <h3>Próximas Citas</h3>
        
        <div class="appointment-item">
            <div class="appointment-date">
                <div class="date-day">15</div>
                <div class="date-month">OCT</div>
            </div>
            <div class="appointment-details">
                <h4>Consulta Psicológica</h4>
                <p><strong>Hora:</strong> 10:00 AM - 11:00 AM</p>
                <p><strong>Psicólogo:</strong> Dr. María García</p>
                <p><strong>Modalidad:</strong> Presencial - Sala 201</p>
            </div>
            <div class="appointment-actions">
                <button class="btn-secondary">Reagendar</button>
                <button class="btn-danger">Cancelar</button>
            </div>
        </div>
        
        <div class="appointment-item">
            <div class="appointment-date">
                <div class="date-day">22</div>
                <div class="date-month">OCT</div>
            </div>
            <div class="appointment-details">
                <h4>Seguimiento Académico</h4>
                <p><strong>Hora:</strong> 2:00 PM - 3:00 PM</p>
                <p><strong>Consejero:</strong> Lic. Carlos Ruiz</p>
                <p><strong>Modalidad:</strong> Virtual - Zoom</p>
            </div>
            <div class="appointment-actions">
                <button class="btn-secondary">Reagendar</button>
                <button class="btn-danger">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-header {
    margin: 2rem 0;
}

.appointments-list h3 {
    margin: 2rem 0 1rem 0;
    color: var(--pri-700);
}

.appointment-item {
    background: white;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.appointment-date {
    background: var(--pri-500);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
    min-width: 80px;
}

.date-day {
    font-size: 1.5rem;
    font-weight: bold;
}

.date-month {
    font-size: 0.9rem;
}

.appointment-details {
    flex: 1;
}

.appointment-details h4 {
    margin: 0 0 0.5rem 0;
    color: var(--pri-700);
}

.appointment-details p {
    margin: 0.25rem 0;
    color: #666;
}

.appointment-actions {
    display: flex;
    flex-direction: column;
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

.btn-danger {
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>
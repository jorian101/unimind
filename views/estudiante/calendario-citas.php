<?php
require_once dirname(__DIR__) . '/pageHeader.php';
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
/* ...existing styles from calendario.php... */
</style>

<?php
// views/administrador/citas.php
require_once __DIR__ . '/../header.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<style>

body {
    background-color: #f6f6f6;
    font-family: 'Inter', sans-serif;
}
.admin-citas-container {
    max-width: 98vw;
    margin: 32px auto;
    padding: 0 2vw;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 4px 24px #0002;
}
.citas-title {
    font-size: 2rem;
    font-weight: 700;
    color: #6b1a1a;
    margin-bottom: 8px;
    margin-top: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.citas-calendar {
    background: #f3f4f6;
    border-radius: 16px;
    padding: 40px 48px 40px 48px;
    box-shadow: 0 2px 8px #0001;
    margin-bottom: 40px;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.citas-details {
    background: #f9fafb;
    border-radius: 16px;
    padding: 24px 32px;
    box-shadow: 0 2px 8px #0001;
}
.citas-details-title {
    font-size: 1.3rem;
    color: #6b1a1a;
    font-weight: 600;
    margin-bottom: 12px;
}
.citas-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px #0001;
}
.citas-table th, .citas-table td {
    padding: 12px 16px;
    text-align: left;
}
.citas-table th {
    background: #f3f4f6;
    color: #6b1a1a;
    font-weight: 600;
}
.citas-table tr {
    border-bottom: 1px solid #e5e7eb;
}
.citas-table tr:last-child {
    border-bottom: none;
}
@media (max-width: 800px) {
    .admin-citas-container { padding: 16px 8px; }
    .citas-calendar { padding: 8px; }
    .citas-details { padding: 12px 4px; }
}

.fc-daygrid-day.fc-day-selected {
    background: #fde2e2 !important;
    border-radius: 8px;
    box-shadow: 0 0 0 2px #6b1a1a33;
}
</style>
<div class="admin-citas-container">
    <div class="citas-title">
        <span>📅</span> Gestión de Citas
    </div>
    <div style="margin-bottom:24px;display:flex;gap:24px;flex-wrap:wrap;align-items:center">
        <label>Estado:
            <select id="filtro-estado" style="padding:6px 12px;border-radius:8px;border:1px solid #ddd;">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </label>
        <label>Alumno:
            <input type="text" id="filtro-alumno" placeholder="Nombre o apellido" style="padding:6px 12px;border-radius:8px;border:1px solid #ddd;" />
        </label>
        <button id="btn-filtrar" style="padding:7px 18px;border-radius:8px;background:#6b1a1a;color:#fff;border:none;font-weight:600;cursor:pointer;">Filtrar</button>
        <button id="btn-limpiar" style="padding:7px 18px;border-radius:8px;background:#fff;color:#6b1a1a;border:2px solid #6b1a1a;font-weight:600;cursor:pointer;">Limpiar</button>
        <button id="btn-hoy" style="padding:7px 18px;border-radius:8px;background:#3b82f6;color:#fff;border:none;font-weight:600;cursor:pointer;">Hoy</button>
    </div>
    <div class="citas-calendar">
        <div id="calendar"></div>
    </div>
    <div class="citas-details" id="citas-details">
        <div class="citas-details-title">Detalles de las citas del día</div>
        <table class="citas-table" id="citas-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Alumno</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <!-- Las filas se llenan dinámicamente -->
            </tbody>
        </table>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
const calendarEl = document.getElementById('calendar');
const citasTableBody = document.querySelector('#citas-table tbody');

let eventosCitas = [];
let diaSeleccionado = null;

// Datos de prueba si la BD está vacía
const datosPrueba = [
    { id: 1, title: 'Juan Pérez', start: '2025-11-30T10:00:00', motivo: 'Orientación académica', estado: 'pendiente' },
    { id: 2, title: 'Lucía Gómez', start: '2025-11-30T12:30:00', motivo: 'Problema personal', estado: 'confirmada' },
    { id: 3, title: 'Pedro Díaz', start: '2025-12-01T09:00:00', motivo: 'Revisión de test', estado: 'pendiente' },
    { id: 4, title: 'Sofía Mora', start: '2025-12-01T11:00:00', motivo: 'Consulta de resultados', estado: 'cancelada' },
    { id: 5, title: 'Elena Vargas', start: '2025-12-02T14:00:00', motivo: 'Seguimiento académico', estado: 'pendiente' },
    { id: 6, title: 'David Rios', start: '2025-12-02T16:00:00', motivo: 'Problema familiar', estado: 'confirmada' }
];

const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    height: 600,
    contentHeight: 600,
    aspectRatio: 1.7,
    events: function(fetchInfo, successCallback, failureCallback) {
        fetch('api/citas-admin.php?fecha=' + fetchInfo.startStr)
            .then(res => res.json())
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    eventosCitas = data;
                    successCallback(data);
                } else {
                    eventosCitas = datosPrueba;
                    successCallback(datosPrueba);
                }
            })
            .catch(() => {
                eventosCitas = datosPrueba;
                successCallback(datosPrueba);
            });
    },
    eventClick: function(info) {
        const fecha = info.event.start;
        resaltarDia(fecha);
        mostrarCitasDelDia(fecha);
    },
    dateClick: function(info) {
        resaltarDia(info.date);
        mostrarCitasDelDia(info.date);
    },
});
calendar.render();

function resaltarDia(fecha) {
    diaSeleccionado = fecha.toISOString().slice(0, 10);
    document.querySelectorAll('.fc-daygrid-day').forEach(el => {
        el.classList.remove('fc-day-selected');
        if (el.dataset.date === diaSeleccionado) {
            el.classList.add('fc-day-selected');
        }
    });
}

function mostrarCitasDelDia(fecha) {
    const dia = fecha.toISOString().slice(0, 10);
    fetch('api/citas-admin.php?fecha=' + dia)
        .then(res => res.json())
        .then(citasDia => {
            if (!Array.isArray(citasDia) || citasDia.length === 0) {
                renderCitasFiltradas(datosPrueba.filter(c => c.start.startsWith(dia)));
            } else {
                renderCitasFiltradas(citasDia);
            }
        })
        .catch(() => {
            renderCitasFiltradas(datosPrueba.filter(c => c.start.startsWith(dia)));
        });
}

function renderCitasFiltradas(citasDia) {
    const estado = document.getElementById('filtro-estado').value;
    const alumno = document.getElementById('filtro-alumno').value.trim().toLowerCase();
    let filtradas = citasDia;
    if (estado) filtradas = filtradas.filter(c => c.estado === estado);
    if (alumno) filtradas = filtradas.filter(c => c.title.toLowerCase().includes(alumno));
    citasTableBody.innerHTML = '';
    if (filtradas.length === 0) {
        citasTableBody.innerHTML = '<tr><td colspan="4">No hay citas para este filtro.</td></tr>';
        return;
    }
    for (const cita of filtradas) {
        const hora = cita.start.slice(11, 16);
        citasTableBody.innerHTML += `<tr>
            <td>${hora}</td>
            <td>${cita.title}</td>
            <td>${cita.motivo}</td>
            <td><span style="color:${cita.estado==='pendiente'?'#6366f1':cita.estado==='confirmada'?'#10b981':'#ef4444'};font-weight:600;">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</span></td>
        </tr>`;
    }
}

document.getElementById('btn-filtrar').onclick = function() {
    if (diaSeleccionado) {
        mostrarCitasDelDia(new Date(diaSeleccionado));
    } else {
        mostrarCitasDelDia(new Date());
    }
};

document.getElementById('btn-limpiar').onclick = function() {
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-alumno').value = '';
    if (diaSeleccionado) {
        mostrarCitasDelDia(new Date(diaSeleccionado));
    } else {
        mostrarCitasDelDia(new Date());
    }
};

document.getElementById('btn-hoy').onclick = function() {
    const hoy = new Date();
    resaltarDia(hoy);
    mostrarCitasDelDia(hoy);
    calendar.gotoDate(hoy);
};

// Mostrar citas del día actual al cargar y resaltar
const hoy = new Date();
resaltarDia(hoy);
mostrarCitasDelDia(hoy);
</script>
<?php
require_once __DIR__ . '/../footer.php';
?>

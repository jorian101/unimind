<?php

require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../utils/asset-version.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<link rel="stylesheet" href="views/administrador/citas.css?v=<?php echo asset_version('views/administrador/citas.css'); ?>">

<div class="admin-citas-container">
    <div class="page-header">
        <div class="header-content">
            <h1><span>📅</span> Gestión de Citas</h1>
            <p class="subtitle">Calendario y detalles — vista tipo tarjetas</p>
        </div>
    </div>

    <div class="filters-section">
        <label>Estado:
            <select id="filtro-estado">
                <option value="">Todos</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </label>
        <label>Alumno:
            <input type="text" id="filtro-alumno" placeholder="Nombre o apellido" class="citas-input" />
        </label>
        <button id="btn-filtrar" class="citas-btn primary">Filtrar</button>
        <button id="btn-limpiar" class="citas-btn ghost">Limpiar</button>
        <button id="btn-hoy" class="citas-btn info">Hoy</button>
    </div>

    <!-- Calendario en la parte superior -->
    <div class="citas-calendar-section">
        <div class="citas-calendar">
            <!-- Título y botones de mes eliminados -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Detalles de citas en la parte inferior -->
    <div class="citas-details-section">
        <div class="citas-details" id="citas-details">
            <div class="citas-details-title">Detalles de las citas del día</div>

            <!-- Grid de tarjetas para pantallas medianas/grandes -->
            <div class="appointments-grid" id="appointments-grid"></div>

            <!-- Tabla como fallback en móviles -->
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
</div>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
const calendarEl = document.getElementById('calendar');
const citasTableBody = document.querySelector('#citas-table tbody');
const appointmentsGrid = document.getElementById('appointments-grid');

let eventosCitas = [];
let diaSeleccionado = null;



function getCalendarHeight() {
    const w = window.innerWidth;
    // Alturas optimizadas para layout vertical - calendario más compacto
    if (w >= 1920) return 750;
    if (w >= 1600) return 680;
    if (w >= 1440) return 620;
    if (w >= 1024) return 560;
    if (w >= 768) return 500;
    if (w >= 375) return 420;
    return 360;
}
function getToolbarConfig() {
    const w = window.innerWidth;
    if (w <= 374) return { left: 'prev,next', center: 'title', right: 'dayGridMonth' };
    if (w <= 390) return { left: 'prev,next', center: 'title', right: 'dayGridMonth' };
    if (w < 768) return { left: 'prev,next', center: 'title', right: 'dayGridMonth,listWeek' };
    if (w < 1024) return { left: 'prev', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' };
    return { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' };
}

function getAspectRatio() {
    const w = window.innerWidth;
    if (w >= 1920) return 2.3;
    if (w >= 1600) return 2.1;
    if (w >= 1440) return 1.9;
    if (w >= 1024) return 1.6;
    if (w >= 768) return 1.4;
    return 1.2;
}

function getDayMaxEventRows() {
    const w = window.innerWidth;
    if (w >= 1600) return 6;
    if (w >= 1440) return 5;
    if (w >= 1024) return 4;
    if (w >= 768) return 3;
    return 2;
}

const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    height: getCalendarHeight(),
    contentHeight: getCalendarHeight(),
    aspectRatio: getAspectRatio(),
    headerToolbar: false,
    dayMaxEventRows: getDayMaxEventRows(),
    events: function(fetchInfo, successCallback, failureCallback) {
        // Obtener todas las citas del rango del mes
        const start = fetchInfo.startStr;
        const end = fetchInfo.endStr;
        fetch(`api/citas-admin.php?desde=${start}&hasta=${end}`)
            .then(res => res.json())
            .then(data => {
                // Agrupar por día y contar
                const conteoPorDia = {};
                if (Array.isArray(data)) {
                    data.forEach(cita => {
                        const fecha = cita.start.slice(0, 10);
                        conteoPorDia[fecha] = (conteoPorDia[fecha] || 0) + 1;
                    });
                }
                // Generar eventos: solo cantidad por día
                const eventos = Object.entries(conteoPorDia).map(([fecha, cantidad]) => ({
                    title: cantidad + ' cita' + (cantidad > 1 ? 's' : ''),
                    start: fecha
                }));
                eventosCitas = eventos;
                successCallback(eventos);
            })
            .catch(() => {
                eventosCitas = [];
                successCallback([]);
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

// Update calendar height on resize to keep it large on wide screens
window.addEventListener('resize', function() {
    const h = getCalendarHeight();
    try {
        calendar.setOption('height', h);
        calendar.setOption('contentHeight', h);
        // update toolbar and layout responsively
        calendar.setOption('headerToolbar', getToolbarConfig());
        calendar.setOption('aspectRatio', getAspectRatio());
        calendar.setOption('dayMaxEventRows', getDayMaxEventRows());
    } catch (e) {
        // ignore if calendar not ready
    }
});

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
    if (!fecha) {
        // Mostrar todas las citas del mes actual
        const hoy = new Date();
        const mes = hoy.getMonth() + 1;
        const anio = hoy.getFullYear();
        // Buscar el primer y último día del mes
        const primerDia = `${anio}-${mes.toString().padStart(2, '0')}-01`;
        const ultimoDia = new Date(anio, mes, 0).getDate();
        const ultimoDiaStr = `${anio}-${mes.toString().padStart(2, '0')}-${ultimoDia}`;
        fetch(`api/citas-admin.php?desde=${primerDia}&hasta=${ultimoDiaStr}`)
            .then(res => res.json())
            .then(citasMes => {
                renderCitasFiltradas(Array.isArray(citasMes) ? citasMes : []);
            })
            .catch(() => {
                renderCitasFiltradas([]);
            });
        return;
    }
    const dia = fecha.toISOString().slice(0, 10);
    fetch('api/citas-admin.php?fecha=' + dia)
        .then(res => res.json())
        .then(citasDia => {
            renderCitasFiltradas(Array.isArray(citasDia) ? citasDia : []);
        })
        .catch(() => {
            renderCitasFiltradas([]);
        });
}

function renderCitasFiltradas(citasDia) {
    const estado = document.getElementById('filtro-estado').value;
    const alumno = document.getElementById('filtro-alumno').value.trim().toLowerCase();
    let filtradas = citasDia;
    if (estado) filtradas = filtradas.filter(c => c.estado === estado);
    if (alumno) filtradas = filtradas.filter(c => c.title.toLowerCase().includes(alumno));
    // Render table rows (mobile) and cards (desktop) simultaneously; CSS will show/hide.
    citasTableBody.innerHTML = '';
    appointmentsGrid.innerHTML = '';
    if (!Array.isArray(filtradas) || filtradas.length === 0) {
        citasTableBody.innerHTML = '<tr><td colspan="4">No hay citas para este filtro.</td></tr>';
        appointmentsGrid.innerHTML = '<div class="empty-state">No hay citas para este filtro.</div>';
        return;
    }

    let cardsHtml = '';
    for (const cita of filtradas) {
        const hora = cita.start.slice(11, 16);
        const estadoClass = cita.estado === 'pendiente' ? 'estado-pendiente' : (cita.estado === 'confirmada' ? 'estado-confirmada' : 'estado-cancelada');
        // Table row
        citasTableBody.innerHTML += `<tr>
            <td>${hora}</td>
            <td>${cita.title}</td>
            <td>${cita.motivo}</td>
            <td><span class="estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</span></td>
        </tr>`;
        // Card markup
        cardsHtml += `<div class="cita-card">
            <div class="cita-card-header">
                <div class="cita-time">${hora}</div>
                <div class="cita-title">${cita.title}</div>
            </div>
            <div class="cita-motivo">${cita.motivo}</div>
            <div class="cita-estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</div>
        </div>`;
    }

    appointmentsGrid.innerHTML = cardsHtml;
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

// Mostrar todas las citas del mes al cargar (sin selección)
mostrarCitasDelDia(null);
</script>
<?php
require_once __DIR__ . '/../footer.php';
?>

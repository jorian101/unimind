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

            <!-- Botón para crear nueva cita -->
            <button id="btn-nueva-cita" class="citas-btn primary" style="margin-bottom:1rem;">Nueva Cita</button>

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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Las filas se llenan dinámicamente -->
                </tbody>
            </table>
        </div>

        <!-- Modal para crear/editar cita -->
        <div id="modal-cita" class="modal">
            <div class="modal-content" style="max-width:420px;">
                <button class="close" id="close-modal-cita" aria-label="Cerrar">×</button>
                <h3 id="modal-cita-title">Nueva Cita</h3>
                <form id="form-cita">
                    <input type="hidden" id="cita-id" name="id_cita" />
                    <div>
                        <label for="cita-alumno">Alumno (ID o nombre)</label>
                        <input type="text" id="cita-alumno" name="alumno" required autocomplete="off" />
                    </div>
                    <div>
                        <label for="cita-fecha">Fecha y hora</label>
                        <input type="datetime-local" id="cita-fecha" name="fecha_cita" required />
                    </div>
                    <div>
                        <label for="cita-motivo">Motivo</label>
                        <input type="text" id="cita-motivo" name="motivo" required />
                    </div>
                    <div>
                        <label for="cita-estado">Estado</label>
                        <select id="cita-estado" name="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div id="cita-msg" class="modal-msg" style="display:none;"></div>
                    <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:1rem;">
                        <button type="button" id="cancelar-modal-cita" class="citas-btn ghost">Cancelar</button>
                        <button type="submit" class="citas-btn primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de confirmación para eliminar/cancelar -->
        <div id="modal-confirmar" class="modal">
            <div class="modal-content" style="max-width:340px;">
                <button class="close" id="close-modal-confirmar" aria-label="Cerrar">×</button>
                <h3 id="modal-confirmar-title">¿Confirmar acción?</h3>
                <div id="modal-confirmar-msg">¿Estás seguro de que deseas continuar?</div>
                <div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:1rem;">
                    <button type="button" id="cancelar-modal-confirmar" class="citas-btn ghost">Cancelar</button>
                    <button type="button" id="confirmar-modal-confirmar" class="citas-btn danger">Sí, continuar</button>
                </div>
            </div>
        </div>

        <script>
        // Lógica JS para CRUD de citas (solo estructura, AJAX a implementar)
        document.addEventListener('DOMContentLoaded', function() {
            // Botón nueva cita
            document.getElementById('btn-nueva-cita').onclick = function() {
                abrirModalCita();
            };
            // Cerrar modal cita
            document.getElementById('close-modal-cita').onclick = cerrarModalCita;
            document.getElementById('cancelar-modal-cita').onclick = cerrarModalCita;
            // Cerrar modal confirmar
            document.getElementById('close-modal-confirmar').onclick = cerrarModalConfirmar;
            document.getElementById('cancelar-modal-confirmar').onclick = cerrarModalConfirmar;
            // Guardar cita (crear/editar)
            document.getElementById('form-cita').onsubmit = async function(e) {
                e.preventDefault();
                const idCita = document.getElementById('cita-id').value.trim();
                const alumnoInput = document.getElementById('cita-alumno').value.trim();
                const fechaCita = document.getElementById('cita-fecha').value;
                const motivo = document.getElementById('cita-motivo').value.trim();
                // Permitir ID o nombre, pero para backend necesitamos ID
                let idAlumno = alumnoInput;
                if (isNaN(Number(idAlumno))) {
                    // Buscar ID por nombre (simple fetch, solo si no es número)
                    idAlumno = await buscarIdAlumnoPorNombre(alumnoInput);
                    if (!idAlumno) {
                        mostrarMsgCita('No se encontró el alumno especificado.', 'error');
                        return;
                    }
                }
                const payload = {
                    id_alumno: idAlumno,
                    fecha_cita: fechaCita,
                    motivo: motivo
                };
                let url = 'api/citas-admin.php?action=crear';
                let successMsg = 'Cita creada correctamente';
                if (idCita) {
                    payload.id_cita = idCita;
                    url = 'api/citas-admin.php?action=editar';
                    successMsg = 'Cita actualizada correctamente';
                }
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        mostrarMsgCita(successMsg, 'success');
                        setTimeout(() => {
                            cerrarModalCita();
                            if (diaSeleccionado) {
                                mostrarCitasDelDia(new Date(diaSeleccionado));
                            } else {
                                mostrarCitasDelDia(new Date());
                            }
                        }, 800);
                    } else {
                        mostrarMsgCita(data.message || 'Error al guardar cita', 'error');
                    }
                } catch (err) {
                    mostrarMsgCita('Error de red o servidor', 'error');
                }
            };
        });

        // Buscar ID de alumno por nombre (simple, solo para demo; en producción usar autocomplete)
        async function buscarIdAlumnoPorNombre(nombre) {
            if (!nombre) return null;
            // Buscar por nombre exacto (puede mejorarse con endpoint dedicado)
            try {
                const res = await fetch('api/usuarios-buscar.php?nombre=' + encodeURIComponent(nombre));
                const data = await res.json();
                if (Array.isArray(data) && data.length > 0) {
                    return data[0].id_usuario;
                }
            } catch (e) {}
            return null;
        }

        function abrirModalCita(cita = null) {
            document.getElementById('modal-cita-title').textContent = cita ? 'Editar Cita' : 'Nueva Cita';
            document.getElementById('cita-id').value = cita?.id || '';
            document.getElementById('cita-alumno').value = cita?.alumno || '';
            document.getElementById('cita-fecha').value = cita?.fecha_cita || '';
            document.getElementById('cita-motivo').value = cita?.motivo || '';
            document.getElementById('cita-estado').value = cita?.estado || 'pendiente';
            document.getElementById('cita-msg').style.display = 'none';
            document.getElementById('modal-cita').style.display = 'flex';
            document.getElementById('modal-cita').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function cerrarModalCita() {
            document.getElementById('modal-cita').style.display = 'none';
            document.getElementById('modal-cita').classList.remove('active');
            document.body.style.overflow = '';
        }
        function mostrarMsgCita(msg, tipo) {
            const el = document.getElementById('cita-msg');
            el.textContent = msg;
            el.className = 'modal-msg ' + (tipo === 'error' ? 'error' : 'success');
            el.style.display = 'block';
        }
        function abrirModalConfirmar(msg, onConfirm) {
            document.getElementById('modal-confirmar-msg').textContent = msg;
            document.getElementById('modal-confirmar').style.display = 'flex';
            document.getElementById('modal-confirmar').classList.add('active');
            document.body.style.overflow = 'hidden';
            document.getElementById('confirmar-modal-confirmar').onclick = function() {
                cerrarModalConfirmar();
                if (typeof onConfirm === 'function') onConfirm();
            };
        }
        function cerrarModalConfirmar() {
            document.getElementById('modal-confirmar').style.display = 'none';
            document.getElementById('modal-confirmar').classList.remove('active');
            document.body.style.overflow = '';
        }
        </script>
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
        // Table row con acciones
        citasTableBody.innerHTML += `<tr>
            <td>${hora}</td>
            <td>${cita.title}</td>
            <td>${cita.motivo}</td>
            <td><span class="estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</span></td>
            <td>
                <button class="citas-btn ghost btn-editar-cita" data-id="${cita.id}" data-alumno="${cita.title}" data-fecha="${cita.start}" data-motivo="${cita.motivo}" data-estado="${cita.estado}">Editar</button>
                <button class="citas-btn danger btn-eliminar-cita" data-id="${cita.id}">Eliminar</button>
            </td>
        </tr>`;
        // Card markup con acciones
        cardsHtml += `<div class="cita-card">
            <div class="cita-card-header">
                <div class="cita-time">${hora}</div>
                <div class="cita-title">${cita.title}</div>
            </div>
            <div class="cita-motivo">${cita.motivo}</div>
            <div class="cita-estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</div>
            <div class="cita-actions">
                <button class="citas-btn ghost btn-editar-cita" data-id="${cita.id}" data-alumno="${cita.title}" data-fecha="${cita.start}" data-motivo="${cita.motivo}" data-estado="${cita.estado}">Editar</button>
                <button class="citas-btn danger btn-eliminar-cita" data-id="${cita.id}">Eliminar</button>
            </div>
        </div>`;
    }

    appointmentsGrid.innerHTML = cardsHtml;

    // Asignar eventos a los botones de editar y eliminar
    document.querySelectorAll('.btn-editar-cita').forEach(btn => {
        btn.onclick = function() {
            abrirModalCita({
                id: btn.getAttribute('data-id'),
                alumno: btn.getAttribute('data-alumno'),
                fecha_cita: btn.getAttribute('data-fecha')?.slice(0,16),
                motivo: btn.getAttribute('data-motivo'),
                estado: btn.getAttribute('data-estado')
            });
        };
    });
    document.querySelectorAll('.btn-eliminar-cita').forEach(btn => {
        btn.onclick = function() {
            const idCita = btn.getAttribute('data-id');
            const alumnoNombre = btn.getAttribute('data-alumno');
            abrirModalConfirmar('¿Eliminar esta cita?', async function() {
                // Buscar ID de alumno por nombre (igual que en editar)
                let idAlumno = alumnoNombre;
                if (isNaN(Number(idAlumno))) {
                    idAlumno = await buscarIdAlumnoPorNombre(alumnoNombre);
                    if (!idAlumno) {
                        alert('No se encontró el alumno para eliminar.');
                        return;
                    }
                }
                try {
                    const res = await fetch('api/citas-admin.php?action=eliminar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_cita: idCita, id_alumno: idAlumno })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (diaSeleccionado) {
                            mostrarCitasDelDia(new Date(diaSeleccionado));
                        } else {
                            mostrarCitasDelDia(new Date());
                        }
                    } else {
                        alert(data.message || 'No se pudo eliminar la cita');
                    }
                } catch (err) {
                    alert('Error de red o servidor al eliminar cita');
                }
            });
        };
    });
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

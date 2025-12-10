<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../utils/asset-version.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<link rel="stylesheet" href="views/administrador/citas.css?v=<?php echo asset_version('views/administrador/citas.css'); ?>">
<style>
	/* Asegurar que la tabla de citas del estudiante siempre sea visible */
	#citas-table {
		display: table !important;
	}
</style>

<div class="admin-citas-container">
	<div class="citas-title">
		<span>📅</span> Mis Citas Agendadas
	</div>
	<div class="citas-controls">
		<button id="btn-nueva-cita" class="citas-btn primary">Agendar nueva cita</button>
		<button id="btn-hoy" class="citas-btn info">Hoy</button>
	</div>
	<div class="citas-calendar">
		<!-- Título y botones de mes eliminados para estudiante -->
		<div id="calendar"></div>
	</div>
	<div class="citas-details" id="citas-details">
		<div class="citas-details-title">Detalles de mis citas</div>
		<table class="citas-table" id="citas-table">
			<thead>
				<tr>
					<th>Hora</th>
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
</div>

<!-- Modal para agendar/editar cita -->
<div id="modal-cita" class="modal">
	<div class="modal-content" style="max-width:420px;">
		<button class="close" id="close-modal-cita" aria-label="Cerrar">×</button>
		<h3 id="modal-cita-title">Agendar cita</h3>
		<form id="form-cita">
			<input type="hidden" id="cita-id" />
			<div>
				<label for="fecha-cita">Fecha y hora</label>
				<input type="datetime-local" id="fecha-cita" required />
			</div>
			<div>
				<label for="motivo-cita">Motivo</label>
				<input type="text" id="motivo-cita" maxlength="255" required />
			</div>
			<div id="cita-msg" class="modal-msg" style="display:none;"></div>
			<div style="display:flex; gap:0.5rem; justify-content:flex-end; margin-top:1rem;">
				<button type="button" id="btn-cita-cancel" class="citas-btn ghost">Cancelar</button>
				<button type="submit" class="citas-btn primary">Guardar</button>
			</div>
		</form>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
const calendarEl = document.getElementById('calendar');
const citasTableBody = document.querySelector('#citas-table tbody');
let eventosCitas = [];
let diaSeleccionado = null;
let calendar = null;

function fetchCitas() {
	return fetch('api/citas-estudiante.php?action=list')
		.then(res => res.json())
		.then(data => Array.isArray(data) ? data : [])
		.catch(() => []);
}

function renderCalendar(events) {
	// Agrupar por día y contar citas
	const conteoPorDia = {};
	events.forEach(cita => {
		const fecha = cita.fecha_cita.slice(0, 10);
		conteoPorDia[fecha] = (conteoPorDia[fecha] || 0) + 1;
	});
	const eventos = Object.entries(conteoPorDia).map(([fecha, cantidad]) => ({
		title: cantidad + ' cita' + (cantidad > 1 ? 's' : ''),
		start: fecha
	}));
	
	// Destruir calendario anterior si existe
	if (calendar) {
		calendar.destroy();
	}
	
	calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		locale: 'es',
		height: 600,
		contentHeight: 600,
		aspectRatio: 1.7,
		events: eventos,
		eventClick: function(info) {
			resaltarDia(info.event.start);
			mostrarCitasDelDia(info.event.start);
		},
		dateClick: function(info) {
			resaltarDia(info.date);
			mostrarCitasDelDia(info.date);
		},
	});
	calendar.render();
}

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
		const primerDia = `${anio}-${mes.toString().padStart(2, '0')}-01`;
		const ultimoDia = new Date(anio, mes, 0).getDate();
		const ultimoDiaStr = `${anio}-${mes.toString().padStart(2, '0')}-${ultimoDia.toString().padStart(2, '0')}`;
		const citasMes = eventosCitas.filter(c => c.fecha_cita >= primerDia && c.fecha_cita <= ultimoDiaStr);
		renderCitasFiltradas(citasMes);
		return;
	}
	const dia = typeof fecha === 'string' ? fecha : fecha.toISOString().slice(0, 10);
	const citasDia = eventosCitas.filter(c => c.fecha_cita.startsWith(dia));
	renderCitasFiltradas(citasDia);
}

function renderCitasFiltradas(citasDia) {
	citasTableBody.innerHTML = '';
	const detallesTitulo = document.querySelector('.citas-details-title');
	
	if (citasDia.length === 0) {
		citasTableBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#999;">No hay citas para este período.</td></tr>';
		if (detallesTitulo) {
			detallesTitulo.textContent = 'Detalles de mis citas';
		}
		return;
	}
	
	// Actualizar título si hay día seleccionado
	if (detallesTitulo) {
		if (diaSeleccionado) {
			const fecha = new Date(diaSeleccionado + 'T00:00:00');
			const formato = fecha.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
			detallesTitulo.textContent = `Citas del ${formato}`;
		} else {
			const hoy = new Date();
			const mes = hoy.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
			detallesTitulo.textContent = `Citas de ${mes}`;
		}
	}
	
	for (const cita of citasDia) {
		const hora = cita.fecha_cita.slice(11, 16);
		const estadoClass = cita.estado === 'pendiente' ? 'estado-pendiente' : (cita.estado === 'confirmada' ? 'estado-confirmada' : 'estado-cancelada');
		citasTableBody.innerHTML += `<tr>
			<td>${hora}</td>
			<td>${cita.motivo}</td>
			<td><span class="estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</span></td>
			<td>
				<button class="citas-btn ghost btn-editar-cita" data-id="${cita.id_cita}" data-fecha="${cita.fecha_cita}" data-motivo="${cita.motivo}">Editar</button>
				<button class="citas-btn danger btn-eliminar-cita" data-id="${cita.id_cita}">Cancelar</button>
			</td>
		</tr>`;
	}
	// Asignar eventos a botones de editar
	document.querySelectorAll('.btn-editar-cita').forEach(btn => {
		btn.onclick = function() {
			abrirModalEditarCita({
				id_cita: btn.getAttribute('data-id'),
				fecha_cita: btn.getAttribute('data-fecha'),
				motivo: btn.getAttribute('data-motivo')
			});
		};
	});
	// Asignar eventos a botones de cancelar
	document.querySelectorAll('.btn-eliminar-cita').forEach(btn => {
		btn.onclick = function() {
			abrirModalConfirmar('¿Deseas cancelar esta cita?', async function() {
				await cancelarCita(btn.getAttribute('data-id'));
			});
		};
	});
}

function abrirModalEditarCita(cita) {
	document.getElementById('modal-cita-title').textContent = 'Editar cita';
	document.getElementById('cita-id').value = cita.id_cita;
	document.getElementById('fecha-cita').value = cita.fecha_cita.slice(0, 16);
	document.getElementById('motivo-cita').value = cita.motivo;
	document.getElementById('cita-msg').style.display = 'none';
	document.getElementById('modal-cita').style.display = 'flex';
	document.getElementById('modal-cita').classList.add('active');
	document.body.style.overflow = 'hidden';
}

function abrirModalNuevaCita() {
	document.getElementById('modal-cita-title').textContent = 'Agendar cita';
	document.getElementById('cita-id').value = '';
	document.getElementById('fecha-cita').value = '';
	document.getElementById('motivo-cita').value = '';
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
	if (confirm(msg)) {
		if (typeof onConfirm === 'function') onConfirm();
	}
}

async function guardarCita(e) {
	e.preventDefault();
	const id_cita = document.getElementById('cita-id').value.trim();
	const fecha_cita = document.getElementById('fecha-cita').value;
	const motivo = document.getElementById('motivo-cita').value.trim();
	
	if (!fecha_cita || !motivo) {
		mostrarMsgCita('Debes completar todos los campos.', 'error');
		return;
	}
	
	const payload = { fecha_cita, motivo };
	let url = 'api/citas-estudiante.php';
	let action = '';
	
	if (id_cita) {
		payload.id_cita = id_cita;
		action = '?action=editar';
	}
	
	try {
		const res = await fetch(url + action, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(payload)
		});
		const data = await res.json();
		if (data.success) {
			mostrarMsgCita('Cita guardada correctamente', 'success');
			setTimeout(() => {
				cerrarModalCita();
				cargarCitas();
			}, 800);
		} else {
			mostrarMsgCita(data.message || 'Error al guardar la cita', 'error');
		}
	} catch (err) {
		mostrarMsgCita('Error de red o servidor', 'error');
	}
}

async function cancelarCita(id_cita) {
	try {
		const res = await fetch('api/citas-estudiante.php?action=cancelar', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ id_cita })
		});
		const data = await res.json();
		if (data.success) {
			cargarCitas();
		} else {
			alert(data.message || 'Error al cancelar la cita');
		}
	} catch (err) {
		alert('Error de red o servidor');
	}
}

function cargarCitas() {
	fetchCitas().then(citas => {
		eventosCitas = citas;
		renderCalendar(citas);
		// Mostrar todas las citas del mes al cargar (sin selección)
		mostrarCitasDelDia(null);
	});
}

document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('btn-nueva-cita').onclick = abrirModalNuevaCita;
	document.getElementById('btn-hoy').onclick = function() {
		const hoy = new Date();
		resaltarDia(hoy);
		mostrarCitasDelDia(hoy);
	};
	document.getElementById('form-cita').onsubmit = guardarCita;
	document.getElementById('close-modal-cita').onclick = cerrarModalCita;
	document.getElementById('btn-cita-cancel').onclick = cerrarModalCita;
	
	// Inicializar
	cargarCitas();
});
</script>
<?php
require_once __DIR__ . '/../footer.php';
?>

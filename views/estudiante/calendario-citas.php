<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../utils/asset-version.php';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<link rel="stylesheet" href="views/administrador/citas.css?v=<?php echo asset_version('views/administrador/citas.css'); ?>">

<div class="admin-citas-container">
	<div class="citas-title">
		<span>📅</span> Mis Citas Agendadas
	</div>
	<div class="citas-controls">
		<button id="btn-nueva-cita" class="citas-btn primary">Agendar nueva cita</button>
		<button id="btn-hoy" class="citas-btn info">Hoy</button>
	</div>
	<div class="citas-calendar">
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
<div class="modal" id="modal-cita" tabindex="-1" style="display:none;">
<div class="modal" id="modal-cita" tabindex="-1" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.25); z-index:9999; align-items:center; justify-content:center;">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="modal-cita-title">Agendar cita</h5>
		<button type="button" class="close" id="modal-cita-close">&times;</button>
	  </div>
	  <div class="modal-body">
		<form id="form-cita">
		  <input type="hidden" id="cita-id" />
		  <div class="form-group">
			<label for="fecha-cita">Fecha y hora:</label>
			<input type="datetime-local" id="fecha-cita" class="form-control" required />
		  </div>
		  <div class="form-group">
			<label for="motivo-cita">Motivo:</label>
			<input type="text" id="motivo-cita" class="form-control" maxlength="255" required />
		  </div>
		</form>
				<div id="modal-cita-feedback" style="color:#c72344; font-weight:600; margin-top:8px; display:none;"></div>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="citas-btn primary" id="btn-cita-save">Guardar</button>
		<button type="button" class="citas-btn ghost" id="btn-cita-cancel">Cancelar</button>
	  </div>
	</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
const calendarEl = document.getElementById('calendar');
const citasTableBody = document.querySelector('#citas-table tbody');
let eventosCitas = [];
let diaSeleccionado = null;

function fetchCitas() {
	return fetch('api/citas-estudiante.php?action=list')
		.then(res => res.json())
		.then(data => Array.isArray(data) ? data : [])
		.catch(() => []);
}

function renderCalendar(events) {
	const calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		locale: 'es',
		height: 600,
		contentHeight: 600,
		aspectRatio: 1.7,
		events: events.map(cita => ({
			id: cita.id_cita,
			title: cita.motivo,
			start: cita.fecha_cita,
			estado: cita.estado
		})),
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
	const dia = fecha.toISOString().slice(0, 10);
	const citasDia = eventosCitas.filter(c => c.fecha_cita.startsWith(dia));
	renderCitasFiltradas(citasDia);
}

function renderCitasFiltradas(citasDia) {
	citasTableBody.innerHTML = '';
	if (citasDia.length === 0) {
		citasTableBody.innerHTML = '<tr><td colspan="4">No hay citas para este día.</td></tr>';
		return;
	}
	for (const cita of citasDia) {
		const hora = cita.fecha_cita.slice(11, 16);
		const estadoClass = cita.estado === 'pendiente' ? 'estado-pendiente' : (cita.estado === 'confirmada' ? 'estado-confirmada' : 'estado-cancelada');
		citasTableBody.innerHTML += `<tr>
			<td>${hora}</td>
			<td>${cita.motivo}</td>
			<td><span class="estado ${estadoClass}">${cita.estado.charAt(0).toUpperCase()+cita.estado.slice(1)}</span></td>
			<td>
				<button class="citas-btn info" onclick="abrirModalEditarCita(${cita.id_cita})">Editar</button>
				<button class="citas-btn ghost" onclick="cancelarCita(${cita.id_cita})">Cancelar</button>
			</td>
		</tr>`;
	}
}

function abrirModalNuevaCita() {
	document.getElementById('modal-cita-title').textContent = 'Agendar cita';
	document.getElementById('cita-id').value = '';
	document.getElementById('fecha-cita').value = '';
	document.getElementById('motivo-cita').value = '';
	document.getElementById('modal-cita').style.display = 'block';
}

function abrirModalEditarCita(id_cita) {
	const cita = eventosCitas.find(c => c.id_cita === id_cita);
	if (!cita) return;
	document.getElementById('modal-cita-title').textContent = 'Editar cita';
	document.getElementById('cita-id').value = cita.id_cita;
	document.getElementById('fecha-cita').value = cita.fecha_cita.slice(0,16);
	document.getElementById('motivo-cita').value = cita.motivo;
	document.getElementById('modal-cita').style.display = 'block';
}

function cerrarModalCita() {
	document.getElementById('modal-cita').style.display = 'none';
}

function guardarCita() {
	const id_cita = document.getElementById('cita-id').value;
	const fecha_cita = document.getElementById('fecha-cita').value;
	const motivo = document.getElementById('motivo-cita').value;
	if (!fecha_cita || !motivo) {
		alert('Debes completar todos los campos.');
		return;
	}
	const payload = { fecha_cita, motivo };
	let url = 'api/citas-estudiante.php';
	let method = 'POST';
	let action = '';
	if (id_cita) {
		payload.id_cita = id_cita;
		action = '?action=editar';
	}
	fetch(url + action, {
		method,
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify(payload)
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			cerrarModalCita();
			cargarCitas();
		} else {
			alert(data.message || 'Error al guardar la cita');
		}
	});
}

function cancelarCita(id_cita) {
	if (!confirm('¿Seguro que deseas cancelar esta cita?')) return;
	fetch('api/citas-estudiante.php?action=cancelar', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ id_cita })
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			cargarCitas();
		} else {
			alert(data.message || 'Error al cancelar la cita');
		}
	});
}

function cargarCitas() {
	fetchCitas().then(citas => {
		eventosCitas = citas;
		renderCalendar(citas);
		// Mostrar citas del día actual
		const hoy = new Date();
		resaltarDia(hoy);
		mostrarCitasDelDia(hoy);
	});
}

document.getElementById('btn-nueva-cita').onclick = abrirModalNuevaCita;
document.getElementById('btn-hoy').onclick = function() {
	const hoy = new Date();
	resaltarDia(hoy);
	mostrarCitasDelDia(hoy);
};
document.getElementById('btn-cita-save').onclick = guardarCita;
document.getElementById('btn-cita-cancel').onclick = cerrarModalCita;
document.getElementById('modal-cita-close').onclick = cerrarModalCita;

// Inicializar
cargarCitas();
</script>
<?php
require_once __DIR__ . '/../footer.php';
?>

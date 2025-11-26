// prof-tabs.js: Tab switching and historial loading

document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(tc => tc.classList.remove('active'));
            btn.classList.add('active');
            document.querySelector('.tab-content--' + btn.dataset.tab).classList.add('active');
            if (btn.dataset.tab === 'historial') {
                loadHistorial();
            }
        });
    });
});

function loadHistorial() {
    fetch('/unimind/api/prof_historial.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('historialTableBody');
            tbody.innerHTML = '';
            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.nombre_curso}</td><td>${row.nombre_test}</td><td>${row.cant_estudiantes}</td><td>${row.fecha_aplicacion}</td>`;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4">Sin datos</td></tr>';
            }
        })
        .catch(() => {
            const tbody = document.getElementById('historialTableBody');
            tbody.innerHTML = '<tr><td colspan="4">Error al cargar historial</td></tr>';
        });
}

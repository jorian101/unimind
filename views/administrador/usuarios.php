<?php
// views/administrador/usuarios.php
// Dashboard CRUD de usuarios
require_once __DIR__ . '/../../controllers/UserController.php';
$controller = new UserController();
$usuarios = $controller->getAllUsuarios();
$roles = $controller->getRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Usuarios</title>
    <link rel="stylesheet" href="../sidebar.css">
    <link rel="stylesheet" href="../layout.css">
    <link rel="stylesheet" href="usuarios.css">
    <style>
        .usuarios-dashboard { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #0002; padding: 32px; }
        .usuarios-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .usuarios-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .usuarios-table th, .usuarios-table td { padding: 12px 8px; border-bottom: 1px solid #eee; text-align: left; }
        .usuarios-table th { background: #f7f7f7; }
        .usuarios-actions button { margin-right: 8px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #0006; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #fff; padding: 32px; border-radius: 10px; min-width: 350px; max-width: 400px; }
        .modal-header { font-size: 1.2em; margin-bottom: 16px; }
        .modal-actions { margin-top: 24px; text-align: right; }
        .btn { background: #1976d2; color: #fff; border: none; border-radius: 6px; padding: 8px 16px; cursor: pointer; }
        .btn-danger { background: #d32f2f; }
        .btn-secondary { background: #aaa; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <div class="usuarios-dashboard" style="max-width:100vw; width:96vw; min-height:80vh; padding:64px 4vw;">
        <div class="usuarios-header">
            <h2>Gestión de Usuarios</h2>
            <button class="btn" onclick="openUsuarioModal()">+ Nuevo Usuario</button>
        </div>
        <div style="margin-bottom:24px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <input type="text" id="busqueda-usuario" placeholder="Buscar por nombre, usuario o email" style="padding:8px; min-width:220px;">
            <select id="filtro-rol" style="padding:8px; min-width:140px;">
                <option value="">Todos los roles</option>
                <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol ?>"><?= $rol ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <table class="usuarios-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="usuarios-list">
                <!-- Renderizado JS -->
            </tbody>
        </table>
    </div>
    <!-- Modal Usuario -->
    <div class="modal" id="usuario-modal">
        <div class="modal-content">
            <div class="modal-header" id="usuario-modal-title">Nuevo Usuario</div>
            <form id="usuario-form">
                <input type="hidden" name="id" id="usuario-id">
                <div>
                    <label>Usuario:</label><br>
                    <input type="text" name="codigo_usuario" id="usuario-codigo" required style="width:100%">
                </div>
                <div style="margin-top:12px;">
                    <label>Nombre:</label><br>
                    <input type="text" name="nombre" id="usuario-nombre" required style="width:100%">
                </div>
                <div style="margin-top:12px;">
                    <label>Email:</label><br>
                    <input type="email" name="email" id="usuario-email" required style="width:100%">
                </div>
                <div style="margin-top:12px;">
                    <label>Rol:</label><br>
                    <select name="rol" id="usuario-rol" required style="width:100%">
                        <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol ?>"><?= $rol ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-top:12px;">
                    <label>Contraseña:</label><br>
                    <input type="password" name="password" id="usuario-password" style="width:100%">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUsuarioModal()">Cancelar</button>
                    <button type="submit" class="btn" id="usuario-save-btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    // --- Modal logic ---
    function openUsuarioModal(id = null) {
        document.getElementById('usuario-modal').style.display = 'flex';
        if (id) {
            // Editar usuario
            const row = document.querySelector('tr[data-id="' + id + '"]');
            document.getElementById('usuario-modal-title').textContent = 'Editar Usuario';
            document.getElementById('usuario-id').value = id;
            document.getElementById('usuario-codigo').value = row.children[1].textContent;
            document.getElementById('usuario-nombre').value = row.children[2].textContent;
            document.getElementById('usuario-email').value = row.children[3].textContent;
            document.getElementById('usuario-rol').value = row.children[4].textContent;
            document.getElementById('usuario-password').value = '';
        } else {
            // Nuevo usuario
            document.getElementById('usuario-modal-title').textContent = 'Nuevo Usuario';
            document.getElementById('usuario-form').reset();
            document.getElementById('usuario-id').value = '';
        }
    }
    function closeUsuarioModal() {
        document.getElementById('usuario-modal').style.display = 'none';
    }

    // --- CRUD AJAX ---
    document.getElementById('usuario-form').onsubmit = function(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        fetch('../../controllers/UserController.php', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                location.reload();
            } else {
                alert(resp.message || 'Error al guardar usuario');
            }
        });
    };
    function editUsuario(id) {
        openUsuarioModal(id);
    }
    function deleteUsuario(id) {
        if (!confirm('¿Seguro que deseas eliminar este usuario?')) return;
        fetch('../../controllers/UserController.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'delete', id })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                location.reload();
            } else {
                alert(resp.message || 'Error al eliminar usuario');
            }
        });
    }

    // --- Búsqueda, filtro y paginación ---
    const usuariosRaw = <?php echo json_encode($usuarios); ?>;
    let usuariosFiltrados = [...usuariosRaw];
    let paginaActual = 1;
    const usuariosPorPagina = 10;

    function renderUsuarios() {
        const tbody = document.getElementById('usuarios-list');
        tbody.innerHTML = '';
        const inicio = (paginaActual - 1) * usuariosPorPagina;
        const fin = inicio + usuariosPorPagina;
        const paginaUsuarios = usuariosFiltrados.slice(inicio, fin);
        for (const usuario of paginaUsuarios) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', usuario.id);
            tr.innerHTML = `
                <td>${usuario.id}</td>
                <td>${usuario.codigo_usuario || ''}</td>
                <td>${usuario.nombre}</td>
                <td>${usuario.email}</td>
                <td>${usuario.rol}</td>
                <td class="usuarios-actions">
                    <button class="btn" onclick="editUsuario(${usuario.id})">Editar</button>
                    <button class="btn btn-danger" onclick="deleteUsuario(${usuario.id})">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        }
        renderPaginacion();
    }

    function renderPaginacion() {
        let paginador = document.getElementById('usuarios-paginacion');
        if (!paginador) {
            paginador = document.createElement('div');
            paginador.id = 'usuarios-paginacion';
            paginador.style = 'margin:24px 0;text-align:center;';
            document.querySelector('.usuarios-dashboard').appendChild(paginador);
        }
        paginador.innerHTML = '';
        const totalPaginas = Math.ceil(usuariosFiltrados.length / usuariosPorPagina);
        if (totalPaginas <= 1) return;
        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'btn' + (i === paginaActual ? ' btn-secondary' : '');
            btn.style = 'margin:0 4px;';
            btn.onclick = () => { paginaActual = i; renderUsuarios(); };
            paginador.appendChild(btn);
        }
    }

    function aplicarBusquedaYFiltro() {
        const texto = document.getElementById('busqueda-usuario').value.toLowerCase();
        const rol = document.getElementById('filtro-rol').value;
        usuariosFiltrados = usuariosRaw.filter(u => {
            const coincideTexto =
                u.nombre.toLowerCase().includes(texto) ||
                (u.codigo_usuario || '').toLowerCase().includes(texto) ||
                (u.email || '').toLowerCase().includes(texto);
            const coincideRol = !rol || u.rol === rol;
            return coincideTexto && coincideRol;
        });
        paginaActual = 1;
        renderUsuarios();
    }

    document.getElementById('busqueda-usuario').addEventListener('input', aplicarBusquedaYFiltro);
    document.getElementById('filtro-rol').addEventListener('change', aplicarBusquedaYFiltro);

    // Inicializar
    aplicarBusquedaYFiltro();
    </script>
</body>
</html>

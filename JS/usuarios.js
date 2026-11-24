document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM Y ESTADO DE SELECCIÓN
    // --------------------------------------------------------------------------
    const tablaUsuariosCuerpo = document.getElementById('tabla-usuarios-cuerpo');
    const inputBuscar = document.getElementById('input-buscar');

    // Botones del Header (Eliminar, Agregar, Editar)
    const btnHeaderEliminar = document.getElementById('btn-header-eliminar');
    const btnHeaderAgregar = document.getElementById('btn-header-agregar');
    const btnHeaderEditar = document.getElementById('btn-header-editar');

    // Elementos de la ventana emergente modal
    const modal = document.getElementById('modal-usuario');
    const modalTitulo = document.getElementById('modal-titulo');
    const formUsuario = document.getElementById('form-usuario');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');
    const btnCancelarModal = document.getElementById('btn-cancelar-modal');

    // Campos del formulario
    const inputId = document.getElementById('usuario-id');
    const inputNombre = document.getElementById('nombre');
    const inputApellidoPaterno = document.getElementById('apellido_paterno');
    const inputApellidoMaterno = document.getElementById('apellido_materno');
    const inputTelefono = document.getElementById('telefono');
    const inputCorreo = document.getElementById('correo');
    const selectRol = document.getElementById('rol');
    const inputClave = document.getElementById('clave');
    const labelClave = document.getElementById('label-clave');

    // Guarda el ID y el Rol del usuario seleccionado al hacer clic en una fila
    let usuarioSeleccionadoId = null;
    let usuarioSeleccionadoRol = null;

    function escaparHTML(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    // --------------------------------------------------------------------------
    // 2. CONSULTAR Y OBTENER USUARIOS (READ / SEARCH)
    // --------------------------------------------------------------------------
    async function cargarUsuarios(terminoBusqueda = '') {
        usuarioSeleccionadoId = null;
        usuarioSeleccionadoRol = null;
        btnHeaderEliminar.disabled = false;
        btnHeaderEliminar.title = '';

        try {
            const url = `../controllers/UsuarioController.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                renderizarUsuarios(resultado.datos);
            } else {
                tablaUsuariosCuerpo.innerHTML = `
                    <tr>
                        <td colspan="5" class="mensaje-tabla-vacia">
                            No fue posible obtener la lista de usuarios.
                        </td>
                    </tr>`;
            }
        } catch (error) {
            console.error('Error al conectar con el controlador:', error);
            tablaUsuariosCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        Error de conexión al cargar los usuarios.
                    </td>
                </tr>`;
        }
    }

    // --------------------------------------------------------------------------
    // 3. DIBUJAR FILAS ESTILO CAJAS INDIVIDUALES (RENDER)
    // --------------------------------------------------------------------------
    function renderizarUsuarios(usuarios) {
        tablaUsuariosCuerpo.innerHTML = '';

        if (usuarios.length === 0) {
            tablaUsuariosCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        No hay usuarios registrados o coincidentes.
                    </td>
                </tr>`;
            return;
        }

        usuarios.forEach(usuario => {
            const fila = document.createElement('tr');
            fila.dataset.id = usuario.id_empleado;
            fila.dataset.rol = usuario.rol;
            fila.title = 'Clic para seleccionar | Doble clic para editar';

            const telefono = usuario.telefono ? escaparHTML(usuario.telefono) : '';

            fila.innerHTML = `
                <td><div class="celda-caja celda-codigo">${usuario.id_empleado}</div></td>
                <td><div class="celda-caja">${escaparHTML(usuario.nombre_completo)}</div></td>
                <td><div class="celda-caja">${escaparHTML(usuario.correo)}</div></td>
                <td><div class="celda-caja">${telefono}</div></td>
                <td><div class="celda-caja">${escaparHTML(usuario.rol)}</div></td>
            `;

            fila.addEventListener('click', () => {
                seleccionarFila(fila, usuario.id_empleado, usuario.rol);
            });

            fila.addEventListener('dblclick', () => {
                seleccionarFila(fila, usuario.id_empleado, usuario.rol);
                abrirModalEditar(usuario.id_empleado);
            });

            tablaUsuariosCuerpo.appendChild(fila);
        });
    }

    function seleccionarFila(filaElemento, id, rol) {
        document.querySelectorAll('#tabla-usuarios-cuerpo tr').forEach(tr => {
            tr.classList.remove('fila-seleccionada');
        });

        filaElemento.classList.add('fila-seleccionada');
        usuarioSeleccionadoId = id;
        usuarioSeleccionadoRol = rol;

        // Si el usuario seleccionado es Administrador, bloquea el botón Eliminar
        if (rol === 'Administrador') {
            btnHeaderEliminar.disabled = true;
            btnHeaderEliminar.title = 'No se puede eliminar a un usuario con rol Administrador';
        } else {
            btnHeaderEliminar.disabled = false;
            btnHeaderEliminar.title = '';
        }
    }

    // --------------------------------------------------------------------------
    // 4. MANEJO DE LA VENTANA MODAL (CREAR / EDITAR / CERRAR)
    // --------------------------------------------------------------------------
    function abrirModalAgregar() {
        formUsuario.reset();
        inputId.value = '';

        inputClave.required = true;
        labelClave.textContent = 'Contraseña *:';
        inputClave.placeholder = 'Ingresa una contraseña';

        modalTitulo.textContent = 'Agregar Nuevo Usuario';
        modal.classList.remove('hidden');
    }

    async function abrirModalEditar(id) {
        try {
            const respuesta = await fetch(`../controllers/UsuarioController.php?accion=obtener_uno&id=${id}`);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                const usuario = resultado.datos;

                inputId.value = usuario.id_empleado;
                inputNombre.value = usuario.nombre;
                inputApellidoPaterno.value = usuario.apellido_paterno;
                inputApellidoMaterno.value = usuario.apellido_materno || '';
                inputTelefono.value = usuario.telefono || '';
                inputCorreo.value = usuario.correo;
                selectRol.value = usuario.rol;

                inputClave.value = '';
                inputClave.required = false;
                labelClave.textContent = 'Nueva Contraseña (opcional):';
                inputClave.placeholder = 'Dejar en blanco para mantener la actual';

                modalTitulo.textContent = `Editar Usuario (#${usuario.id_empleado})`;
                modal.classList.remove('hidden');
            } else {
                alert(resultado.mensaje);
            }
        } catch (error) {
            console.error('Error al recuperar datos del usuario:', error);
            alert('Error de comunicación al cargar la información del usuario.');
        }
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        formUsuario.reset();
    }

    // --------------------------------------------------------------------------
    // 5. GUARDAR O ACTUALIZAR REGISTRO (CREATE / UPDATE)
    // --------------------------------------------------------------------------
    formUsuario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const regexLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
        const regexTelefono = /^[0-9]{10}$/;
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        const nombre = inputNombre.value.trim();
        const apPaterno = inputApellidoPaterno.value.trim();
        const apMaterno = inputApellidoMaterno.value.trim();
        const telefono = inputTelefono.value.trim();
        const correo = inputCorreo.value.trim();
        const clave = inputClave.value;
        const esEdicion = inputId.value !== '';

        if (nombre.length < 2 || !regexLetras.test(nombre)) {
            alert('El nombre es obligatorio y solo debe contener letras.');
            return;
        }
        if (apPaterno.length < 2 || !regexLetras.test(apPaterno)) {
            alert('El apellido paterno es obligatorio y solo debe contener letras.');
            return;
        }
        if (apMaterno !== '' && !regexLetras.test(apMaterno)) {
            alert('El apellido materno solo debe contener letras.');
            return;
        }
        if (telefono !== '' && !regexTelefono.test(telefono)) {
            alert('El teléfono debe tener exactamente 10 números.');
            return;
        }
        if (!regexCorreo.test(correo)) {
            alert('Ingresa un correo electrónico válido (ejemplo@dominio.com).');
            return;
        }
        if (!esEdicion && clave.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres.');
            return;
        }
        if (esEdicion && clave !== '' && clave.length < 6) {
            alert('La nueva contraseña debe tener al menos 6 caracteres.');
            return;
        }

        const formData = new FormData(formUsuario);
        const accion = esEdicion ? 'actualizar' : 'crear';

        try {
            const respuesta = await fetch(`../controllers/UsuarioController.php?accion=${accion}`, {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cerrarModal();
                cargarUsuarios(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al procesar el formulario:', error);
            alert('No se pudo guardar la información en la base de datos.');
        }
    });

    // --------------------------------------------------------------------------
    // 6. ELIMINAR USUARIO (DELETE / BORRADO LÓGICO)
    // --------------------------------------------------------------------------
    async function eliminarUsuario(id) {
        const confirmacion = confirm(`¿Estás seguro de que deseas eliminar al usuario #${id}?`);
        if (!confirmacion) return;

        const formData = new FormData();
        formData.append('id_empleado', id);

        try {
            const respuesta = await fetch('../controllers/UsuarioController.php?accion=eliminar', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cargarUsuarios(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al intentar eliminar:', error);
            alert('Error en el servidor al intentar eliminar el usuario.');
        }
    }

    // --------------------------------------------------------------------------
    // 7. EVENTOS DE LOS BOTONES DEL HEADER (ELIMINAR, AGREGAR, EDITAR)
    // --------------------------------------------------------------------------
    btnHeaderAgregar.addEventListener('click', abrirModalAgregar);

    btnHeaderEditar.addEventListener('click', () => {
        if (!usuarioSeleccionadoId) {
            alert('Selecciona primero un usuario de la tabla haciendo clic sobre su fila.');
            return;
        }
        abrirModalEditar(usuarioSeleccionadoId);
    });

    btnHeaderEliminar.addEventListener('click', () => {
        if (!usuarioSeleccionadoId) {
            alert('Selecciona primero un usuario de la tabla haciendo clic sobre su fila.');
            return;
        }
        if (usuarioSeleccionadoRol === 'Administrador') {
            alert('No se puede eliminar a un usuario con el rol de Administrador.');
            return;
        }
        eliminarUsuario(usuarioSeleccionadoId);
    });

    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    inputBuscar.addEventListener('input', (e) => {
        cargarUsuarios(e.target.value);
    });

    // --------------------------------------------------------------------------
    // 8. INICIALIZACIÓN
    // --------------------------------------------------------------------------
    cargarUsuarios();
});
document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM Y ESTADO DE SELECCIÓN
    // --------------------------------------------------------------------------
    const tablaProveedoresCuerpo = document.getElementById('tabla-proveedores-cuerpo');
    const inputBuscar = document.getElementById('input-buscar');

    // Botones del Header (Eliminar, Agregar, Editar)
    const btnHeaderEliminar = document.getElementById('btn-header-eliminar');
    const btnHeaderAgregar = document.getElementById('btn-header-agregar');
    const btnHeaderEditar = document.getElementById('btn-header-editar');

    // Elementos de la ventana emergente modal
    const modal = document.getElementById('modal-proveedor');
    const modalTitulo = document.getElementById('modal-titulo');
    const formProveedor = document.getElementById('form-proveedor');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');
    const btnCancelarModal = document.getElementById('btn-cancelar-modal');

    // Campos del formulario
    const inputId = document.getElementById('proveedor-id');
    const inputNombre = document.getElementById('nombre');
    const inputTelefono = document.getElementById('telefono');
    const inputCorreo = document.getElementById('correo');
    const inputDireccion = document.getElementById('direccion');

    // Guarda el ID del proveedor seleccionado al hacer clic en una fila
    let proveedorSeleccionadoId = null;

    function escaparHTML(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    // --------------------------------------------------------------------------
    // 2. CONSULTAR Y OBTENER PROVEEDORES (READ / SEARCH)
    // --------------------------------------------------------------------------
    async function cargarProveedores(terminoBusqueda = '') {
        proveedorSeleccionadoId = null;

        try {
            const url = `../controllers/ProveedorController.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                renderizarProveedores(resultado.datos);
            } else {
                tablaProveedoresCuerpo.innerHTML = `
                    <tr>
                        <td colspan="5" class="mensaje-tabla-vacia">
                            No fue posible obtener el directorio de proveedores.
                        </td>
                    </tr>`;
            }
        } catch (error) {
            console.error('Error al conectar con el controlador:', error);
            tablaProveedoresCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        Error de conexión al cargar los proveedores.
                    </td>
                </tr>`;
        }
    }

    // --------------------------------------------------------------------------
    // 3. DIBUJAR FILAS ESTILO CAJAS INDIVIDUALES (RENDER)
    // --------------------------------------------------------------------------
    function renderizarProveedores(proveedores) {
        tablaProveedoresCuerpo.innerHTML = '';

        if (proveedores.length === 0) {
            tablaProveedoresCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        No hay proveedores registrados o coincidentes.
                    </td>
                </tr>`;
            return;
        }

        proveedores.forEach(prov => {
            const fila = document.createElement('tr');
            fila.dataset.id = prov.id_proveedor;
            fila.title = 'Clic para seleccionar | Doble clic para editar';

            const telefono = prov.telefono ? escaparHTML(prov.telefono) : '';
            const correo = prov.correo ? escaparHTML(prov.correo) : '';
            const direccion = prov.direccion ? escaparHTML(prov.direccion) : '';

            fila.innerHTML = `
                <td><div class="celda-caja celda-codigo">${prov.id_proveedor}</div></td>
                <td><div class="celda-caja">${escaparHTML(prov.nombre)}</div></td>
                <td><div class="celda-caja">${telefono}</div></td>
                <td><div class="celda-caja">${correo}</div></td>
                <td><div class="celda-caja">${direccion}</div></td>
            `;

            fila.addEventListener('click', () => {
                seleccionarFila(fila, prov.id_proveedor);
            });

            fila.addEventListener('dblclick', () => {
                seleccionarFila(fila, prov.id_proveedor);
                abrirModalEditar(prov.id_proveedor);
            });

            tablaProveedoresCuerpo.appendChild(fila);
        });
    }

    function seleccionarFila(filaElemento, id) {
        document.querySelectorAll('#tabla-proveedores-cuerpo tr').forEach(tr => {
            tr.classList.remove('fila-seleccionada');
        });

        filaElemento.classList.add('fila-seleccionada');
        proveedorSeleccionadoId = id;
    }

    // --------------------------------------------------------------------------
    // 4. MANEJO DE LA VENTANA MODAL (CREAR / EDITAR / CERRAR)
    // --------------------------------------------------------------------------
    function abrirModalAgregar() {
        formProveedor.reset();
        inputId.value = '';
        modalTitulo.textContent = 'Agregar Nuevo Proveedor';
        modal.classList.remove('hidden');
    }

    async function abrirModalEditar(id) {
        try {
            const respuesta = await fetch(`../controllers/ProveedorController.php?accion=obtener_uno&id=${id}`);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                const prov = resultado.datos;

                inputId.value = prov.id_proveedor;
                inputNombre.value = prov.nombre;
                inputTelefono.value = prov.telefono || '';
                inputCorreo.value = prov.correo || '';
                inputDireccion.value = prov.direccion || '';

                modalTitulo.textContent = `Editar Proveedor (#${prov.id_proveedor})`;
                modal.classList.remove('hidden');
            } else {
                alert(resultado.mensaje);
            }
        } catch (error) {
            console.error('Error al recuperar datos del proveedor:', error);
            alert('Error de comunicación al cargar la información del proveedor.');
        }
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        formProveedor.reset();
    }

    // --------------------------------------------------------------------------
    // 5. GUARDAR O ACTUALIZAR REGISTRO (CREATE / UPDATE)
    // --------------------------------------------------------------------------
    formProveedor.addEventListener('submit', async (e) => {
        e.preventDefault();

        const regexTelefono = /^[0-9]{10}$/;
        const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        const nombre = inputNombre.value.trim();
        const telefono = inputTelefono.value.trim();
        const correo = inputCorreo.value.trim();

        if (nombre.length < 2) {
            alert('El nombre del proveedor debe tener al menos 2 caracteres.');
            return;
        }
        if (telefono !== '' && !regexTelefono.test(telefono)) {
            alert('El teléfono debe contener exactamente 10 dígitos numéricos.');
            return;
        }
        if (correo !== '' && !regexCorreo.test(correo)) {
            alert('Ingresa un correo electrónico válido con @ y dominio.');
            return;
        }

        const formData = new FormData(formProveedor);
        const esEdicion = inputId.value !== '';
        const accion = esEdicion ? 'actualizar' : 'crear';

        try {
            const respuesta = await fetch(`../controllers/ProveedorController.php?accion=${accion}`, {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cerrarModal();
                cargarProveedores(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al procesar el formulario:', error);
            alert('No se pudo guardar la información en la base de datos.');
        }
    });

    // --------------------------------------------------------------------------
    // 6. ELIMINAR PROVEEDOR (DELETE / BORRADO LÓGICO)
    // --------------------------------------------------------------------------
    async function eliminarProveedor(id) {
        const confirmacion = confirm(`¿Estás seguro de que deseas eliminar al proveedor #${id}?`);
        if (!confirmacion) return;

        const formData = new FormData();
        formData.append('id_proveedor', id);

        try {
            const respuesta = await fetch('../controllers/ProveedorController.php?accion=eliminar', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cargarProveedores(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al intentar eliminar:', error);
            alert('Error en el servidor al intentar eliminar el proveedor.');
        }
    }

    // --------------------------------------------------------------------------
    // 7. EVENTOS DE LOS BOTONES DEL HEADER (ELIMINAR, AGREGAR, EDITAR)
    // --------------------------------------------------------------------------
    btnHeaderAgregar.addEventListener('click', abrirModalAgregar);

    btnHeaderEditar.addEventListener('click', () => {
        if (!proveedorSeleccionadoId) {
            alert('Selecciona primero un proveedor de la tabla haciendo clic sobre su fila.');
            return;
        }
        abrirModalEditar(proveedorSeleccionadoId);
    });

    btnHeaderEliminar.addEventListener('click', () => {
        if (!proveedorSeleccionadoId) {
            alert('Selecciona primero un proveedor de la tabla haciendo clic sobre su fila.');
            return;
        }
        eliminarProveedor(proveedorSeleccionadoId);
    });

    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    inputBuscar.addEventListener('input', (e) => {
        cargarProveedores(e.target.value);
    });

    // --------------------------------------------------------------------------
    // 8. INICIALIZACIÓN
    // --------------------------------------------------------------------------
    cargarProveedores();
});
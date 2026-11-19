document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM Y ESTADO DE SELECCIÓN
    // --------------------------------------------------------------------------
    const tablaClientesCuerpo = document.getElementById('tabla-clientes-cuerpo');
    const inputBuscar = document.getElementById('input-buscar');

    // Botones del Header (Eliminar, Agregar, Editar)
    const btnHeaderEliminar = document.getElementById('btn-header-eliminar');
    const btnHeaderAgregar = document.getElementById('btn-header-agregar');
    const btnHeaderEditar = document.getElementById('btn-header-editar');

    // Elementos de la ventana emergente modal
    const modal = document.getElementById('modal-cliente');
    const modalTitulo = document.getElementById('modal-titulo');
    const formCliente = document.getElementById('form-cliente');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');
    const btnCancelarModal = document.getElementById('btn-cancelar-modal');

    // Campos del formulario
    const inputCodigo = document.getElementById('cliente-codigo');
    const inputNombre = document.getElementById('nombre');
    const inputTelefono = document.getElementById('telefono');
    const inputRfc = document.getElementById('rfc');
    const inputDireccion = document.getElementById('direccion');

    // Guarda el código del cliente seleccionado al hacer clic en una fila
    let clienteSeleccionadoId = null;

    function escaparHTML(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    // --------------------------------------------------------------------------
    // 2. CONSULTAR Y OBTENER CLIENTES (READ / SEARCH)
    // --------------------------------------------------------------------------
    async function cargarClientes(terminoBusqueda = '') {
        // Reinicia la selección al recargar o filtrar la tabla
        clienteSeleccionadoId = null;

        try {
            const url = `../controllers/ClienteController.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                renderizarClientes(resultado.datos);
            } else {
                tablaClientesCuerpo.innerHTML = `
                    <tr>
                        <td colspan="5" class="mensaje-tabla-vacia">
                            No fue posible obtener la lista de clientes.
                        </td>
                    </tr>`;
            }
        } catch (error) {
            console.error('Error al conectar con el controlador:', error);
            tablaClientesCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        Error de conexión al cargar los clientes.
                    </td>
                </tr>`;
        }
    }

    // --------------------------------------------------------------------------
    // 3. DIBUJAR FILAS ESTILO CAJAS INDIVIDUALES (RENDER)
    // --------------------------------------------------------------------------
    function renderizarClientes(clientes) {
        tablaClientesCuerpo.innerHTML = '';

        if (clientes.length === 0) {
            tablaClientesCuerpo.innerHTML = `
                <tr>
                    <td colspan="5" class="mensaje-tabla-vacia">
                        No hay clientes registrados o coincidentes.
                    </td>
                </tr>`;
            return;
        }

        clientes.forEach(cliente => {
            const fila = document.createElement('tr');
            fila.dataset.id = cliente.codigo;
            fila.title = 'Clic para seleccionar | Doble clic para editar';

            const telefono = cliente.telefono ? escaparHTML(cliente.telefono) : '';
            const rfc = cliente.rfc ? escaparHTML(cliente.rfc) : '';
            const direccion = cliente.direccion ? escaparHTML(cliente.direccion) : '';

            // Cada celda lleva su caja visual como en el boceto
            fila.innerHTML = `
                <td><div class="celda-caja celda-codigo">${cliente.codigo}</div></td>
                <td><div class="celda-caja">${escaparHTML(cliente.nombre)}</div></td>
                <td><div class="celda-caja">${telefono}</div></td>
                <td><div class="celda-caja">${rfc}</div></td>
                <td><div class="celda-caja">${direccion}</div></td>
            `;

            // Evento: seleccionar fila al hacer clic
            fila.addEventListener('click', () => {
                seleccionarFila(fila, cliente.codigo);
            });

            // Atajo: abrir modal de edición al hacer doble clic en la fila
            fila.addEventListener('dblclick', () => {
                seleccionarFila(fila, cliente.codigo);
                abrirModalEditar(cliente.codigo);
            });

            tablaClientesCuerpo.appendChild(fila);
        });
    }

    function seleccionarFila(filaElemento, codigo) {
        // Quita el resaltado de cualquier otra fila seleccionada previamente
        document.querySelectorAll('#tabla-clientes-cuerpo tr').forEach(tr => {
            tr.classList.remove('fila-seleccionada');
        });

        // Marca la fila actual y guarda su código
        filaElemento.classList.add('fila-seleccionada');
        clienteSeleccionadoId = codigo;
    }

    // --------------------------------------------------------------------------
    // 4. MANEJO DE LA VENTANA MODAL (CREAR / EDITAR / CERRAR)
    // --------------------------------------------------------------------------
    function abrirModalAgregar() {
        formCliente.reset();
        inputCodigo.value = '';
        modalTitulo.textContent = 'Agregar Nuevo Cliente';
        modal.classList.remove('hidden');
    }

    async function abrirModalEditar(codigo) {
        try {
            const respuesta = await fetch(`../controllers/ClienteController.php?accion=obtener_uno&id=${codigo}`);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                const cliente = resultado.datos;

                inputCodigo.value = cliente.codigo;
                inputNombre.value = cliente.nombre;
                inputTelefono.value = cliente.telefono || '';
                inputRfc.value = cliente.rfc || '';
                inputDireccion.value = cliente.direccion || '';

                modalTitulo.textContent = `Editar Cliente (#${cliente.codigo})`;
                modal.classList.remove('hidden');
            } else {
                alert(resultado.mensaje);
            }
        } catch (error) {
            console.error('Error al recuperar datos del cliente:', error);
            alert('Error de comunicación al cargar la información del cliente.');
        }
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        formCliente.reset();
    }

    // --------------------------------------------------------------------------
    // 5. GUARDAR O ACTUALIZAR REGISTRO (CREATE / UPDATE)
    // --------------------------------------------------------------------------
    formCliente.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formCliente);
        const esEdicion = inputCodigo.value !== '';
        const accion = esEdicion ? 'actualizar' : 'crear';

        try {
            const respuesta = await fetch(`../controllers/ClienteController.php?accion=${accion}`, {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cerrarModal();
                cargarClientes(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al procesar el formulario:', error);
            alert('No se pudo guardar la información en la base de datos.');
        }
    });

    // --------------------------------------------------------------------------
    // 6. ELIMINAR CLIENTE (DELETE / BORRADO LÓGICO)
    // --------------------------------------------------------------------------
    async function eliminarCliente(codigo) {
        const confirmacion = confirm(`¿Estás seguro de que deseas eliminar al cliente #${codigo}?`);
        if (!confirmacion) return;

        const formData = new FormData();
        formData.append('codigo', codigo);

        try {
            const respuesta = await fetch('../controllers/ClienteController.php?accion=eliminar', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cargarClientes(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al intentar eliminar:', error);
            alert('Error en el servidor al intentar eliminar el cliente.');
        }
    }

    // --------------------------------------------------------------------------
    // 7. EVENTOS DE LOS BOTONES DEL HEADER (ELIMINAR, AGREGAR, EDITAR)
    // --------------------------------------------------------------------------
    btnHeaderAgregar.addEventListener('click', abrirModalAgregar);

    btnHeaderEditar.addEventListener('click', () => {
        if (!clienteSeleccionadoId) {
            alert('Selecciona primero un cliente de la tabla haciendo clic sobre su fila.');
            return;
        }
        abrirModalEditar(clienteSeleccionadoId);
    });

    btnHeaderEliminar.addEventListener('click', () => {
        if (!clienteSeleccionadoId) {
            alert('Selecciona primero un cliente de la tabla haciendo clic sobre su fila.');
            return;
        }
        eliminarCliente(clienteSeleccionadoId);
    });

    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    inputBuscar.addEventListener('input', (e) => {
        cargarClientes(e.target.value);
    });

    // --------------------------------------------------------------------------
    // 8. INICIALIZACIÓN
    // --------------------------------------------------------------------------
    cargarClientes();
});
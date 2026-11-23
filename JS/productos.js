// Garantiza que el código se ejecute una vez que la estructura HTML del DOM esté completamente cargada
document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM
    // --------------------------------------------------------------------------
    const contenedorProductos = document.getElementById('contenedor-productos');
    const inputBuscar = document.getElementById('input-buscar');
    const selectOrdenar = document.getElementById('select-ordenar');

    // Elementos estructurales de la ventana emergente modal
    const modal = document.getElementById('modal-producto');
    const modalTitulo = document.getElementById('modal-titulo');
    const formProducto = document.getElementById('form-producto');
    const btnAbrirAgregar = document.getElementById('btn-abrir-agregar');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal');
    const btnCancelarModal = document.getElementById('btn-cancelar-modal');

    // Campos de entrada dentro del formulario del modal
    const inputCodigo = document.getElementById('producto-codigo');
    const inputNombre = document.getElementById('nombre');
    const selectTipo = document.getElementById('tipo_medicamento');
    const inputLaboratorio = document.getElementById('laboratorio');
    const inputLote = document.getElementById('lote');
    const inputCaducidad = document.getElementById('fecha_caducidad');
    const inputPrecio = document.getElementById('precio_venta');
    const inputExistencia = document.getElementById('existencia');
    const inputImagen = document.getElementById('imagen');
    const inputArchivoImagen = document.getElementById('archivo_imagen');

    // Variable donde guardamos los productos traídos de la BD para poder reordenarlos sin volver a consultar
    let productosEnMemoria = [];

    // --------------------------------------------------------------------------
    // MÉTODO DE ORDENAMIENTO POR INSERCIÓN (INSERTION SORT)
    // --------------------------------------------------------------------------
    function compararProductos(a, b, criterio) {
        switch (criterio) {
            case 'precio_asc':
                return parseFloat(a.precio_venta) - parseFloat(b.precio_venta);

            case 'precio_desc':
                return parseFloat(b.precio_venta) - parseFloat(a.precio_venta);

            case 'nombre_asc':
                return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });

            case 'existencia_asc':
                return parseInt(a.existencia, 10) - parseInt(b.existencia, 10);

            case 'caducidad_asc':
                return new Date(a.fecha_caducidad) - new Date(b.fecha_caducidad);

            case 'defecto':
            default:
                return parseInt(b.codigo, 10) - parseInt(a.codigo, 10);
        }
    }

    function ordenarPorInsercion(listaProductos, criterio) {
        const arreglo = [...listaProductos];
        const total = arreglo.length;

        for (let i = 1; i < total; i++) {
            const productoActual = arreglo[i];
            let j = i - 1;

            while (j >= 0 && compararProductos(arreglo[j], productoActual, criterio) > 0) {
                arreglo[j + 1] = arreglo[j];
                j--;
            }

            arreglo[j + 1] = productoActual;
        }

        return arreglo;
    }

    function ordenarYMostrarProductos() {
        const criterio = selectOrdenar ? selectOrdenar.value : 'defecto';
        const productosOrdenados = ordenarPorInsercion(productosEnMemoria, criterio);
        renderizarProductos(productosOrdenados);
    }

    // --------------------------------------------------------------------------
    // 2. CONSULTAR Y OBTENER PRODUCTOS (READ / SEARCH)
    // --------------------------------------------------------------------------
    async function cargarProductos(terminoBusqueda = '') {
        try {
            const url = `../controllers/ProductoController.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                productosEnMemoria = resultado.datos;
                ordenarYMostrarProductos();
            } else {
                contenedorProductos.innerHTML = `<p class="mensaje-vacio">No fue posible obtener la lista de productos.</p>`;
            }
        } catch (error) {
            console.error('Error al conectar con el controlador:', error);
            contenedorProductos.innerHTML = `<p class="mensaje-vacio">Error de conexión al cargar los productos.</p>`;
        }
    }

    // --------------------------------------------------------------------------
    // 3. DIBUJAR TARJETAS EN EL DOM (RENDER)
    // --------------------------------------------------------------------------
    function renderizarProductos(productos) {
        contenedorProductos.innerHTML = '';

        if (productos.length === 0) {
            contenedorProductos.innerHTML = `<p class="mensaje-vacio">No hay productos registrados o coincidentes.</p>`;
            return;
        }

        productos.forEach(prod => {
            const precioFormateado = parseFloat(prod.precio_venta).toFixed(2);
            const nombreImagen = prod.imagen ? prod.imagen : 'imagenMedicinaDefecto.jpg';
            const rutaImagen = `../assets/${nombreImagen}`;

            const tarjeta = document.createElement('div');
            tarjeta.classList.add('producto');

            tarjeta.innerHTML = `
                <img src="${rutaImagen}" alt="${prod.nombre}" onerror="this.src='../assets/imagenMedicinaDefecto.jpg'">
                <h2>Nombre: ${prod.nombre}</h2>
                <h2>Precio: $${precioFormateado}</h2>
                <h2>Existencia: ${prod.existencia}</h2>
                <div class="acciones-tarjeta">
                    <button type="button" class="btn-editar-card" data-id="${prod.codigo}">Editar</button>
                    <button type="button" class="btn-eliminar-card" data-id="${prod.codigo}">Eliminar</button>
                </div>
            `;

            contenedorProductos.appendChild(tarjeta);
        });

        asignarEventosTarjetas();
    }

    // --------------------------------------------------------------------------
    // 4. ASIGNAR LISTENERS A BOTONES DINÁMICOS (EDITAR Y ELIMINAR)
    // --------------------------------------------------------------------------
    function asignarEventosTarjetas() {
        document.querySelectorAll('.btn-editar-card').forEach(boton => {
            boton.addEventListener('click', (e) => {
                const idProducto = e.target.getAttribute('data-id');
                abrirModalEditar(idProducto);
            });
        });

        document.querySelectorAll('.btn-eliminar-card').forEach(boton => {
            boton.addEventListener('click', (e) => {
                const idProducto = e.target.getAttribute('data-id');
                eliminarProducto(idProducto);
            });
        });
    }

    // --------------------------------------------------------------------------
    // 5. MANEJO DE LA VENTANA MODAL (CREAR / EDITAR / CERRAR)
    // --------------------------------------------------------------------------
    function abrirModalAgregar() {
        formProducto.reset();
        inputCodigo.value = '';
        inputImagen.value = 'imagenMedicinaDefecto.jpg';
        if (inputArchivoImagen) inputArchivoImagen.value = '';
        modalTitulo.textContent = 'Agregar Nuevo Producto';
        modal.classList.remove('hidden');
    }

    async function abrirModalEditar(codigo) {
        try {
            const respuesta = await fetch(`../controllers/ProductoController.php?accion=obtener_uno&id=${codigo}`);
            const resultado = await respuesta.json();

            if (resultado.exito) {
                const prod = resultado.datos;

                inputCodigo.value = prod.codigo;
                inputNombre.value = prod.nombre;
                selectTipo.value = prod.tipo_medicamento;
                inputLaboratorio.value = prod.laboratorio;
                inputLote.value = prod.lote || '';
                inputCaducidad.value = prod.fecha_caducidad;
                inputPrecio.value = prod.precio_venta;
                inputExistencia.value = prod.existencia;
                inputImagen.value = prod.imagen || 'imagenMedicinaDefecto.jpg';
                if (inputArchivoImagen) inputArchivoImagen.value = '';

                modalTitulo.textContent = `Editar Producto (#${prod.codigo})`;
                modal.classList.remove('hidden');
            } else {
                alert(resultado.mensaje);
            }
        } catch (error) {
            console.error('Error al recuperar datos del producto:', error);
            alert('Error de comunicación al cargar la información del producto.');
        }
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        formProducto.reset();
    }

    // --------------------------------------------------------------------------
    // 6. GUARDAR O ACTUALIZAR REGISTRO (CREATE / UPDATE)
    // --------------------------------------------------------------------------
    formProducto.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formProducto);
        const esEdicion = inputCodigo.value !== '';
        const accion = esEdicion ? 'actualizar' : 'crear';

        try {
            const respuesta = await fetch(`../controllers/ProductoController.php?accion=${accion}`, {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cerrarModal();
                cargarProductos(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al procesar el formulario:', error);
            alert('No se pudo guardar la información en la base de datos.');
        }
    });

    // --------------------------------------------------------------------------
    // 7. ELIMINAR PRODUCTO (DELETE / BORRADO LÓGICO)
    // --------------------------------------------------------------------------
    async function eliminarProducto(codigo) {
        const confirmacion = confirm(`¿Estás seguro de que deseas eliminar el producto #${codigo}?`);
        if (!confirmacion) return;

        const formData = new FormData();
        formData.append('codigo', codigo);

        try {
            const respuesta = await fetch('../controllers/ProductoController.php?accion=eliminar', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                cargarProductos(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al intentar eliminar:', error);
            alert('Error en el servidor al intentar eliminar el producto.');
        }
    }

    // --------------------------------------------------------------------------
    // 8. ESCUCHADORES DE EVENTOS DE INTERFAZ FIJOS
    // --------------------------------------------------------------------------
    btnAbrirAgregar.addEventListener('click', abrirModalAgregar);
    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    if (inputArchivoImagen) {
        inputArchivoImagen.addEventListener('change', () => {
            if (inputArchivoImagen.files && inputArchivoImagen.files.length > 0) {
                inputImagen.value = inputArchivoImagen.files[0].name;
            }
        });
    }

    inputBuscar.addEventListener('input', (e) => {
        cargarProductos(e.target.value);
    });

    if (selectOrdenar) {
        selectOrdenar.addEventListener('change', ordenarYMostrarProductos);
    }

    // --------------------------------------------------------------------------
    // 9. INICIALIZACIÓN
    // --------------------------------------------------------------------------
    cargarProductos();

});
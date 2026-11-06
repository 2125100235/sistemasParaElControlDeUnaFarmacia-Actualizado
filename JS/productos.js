// Garantiza que el código se ejecute una vez que la estructura HTML del DOM esté completamente cargada
document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM
    // --------------------------------------------------------------------------
    // Contenedor principal donde se inyectan las tarjetas de los productos
    const contenedorProductos = document.getElementById('contenedor-productos');
    // Entrada de texto de la barra de búsqueda en tiempo real
    const inputBuscar = document.getElementById('input-buscar');

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

    // --------------------------------------------------------------------------
    // 2. CONSULTAR Y OBTENER PRODUCTOS (READ / SEARCH)
    // --------------------------------------------------------------------------
    async function cargarProductos(terminoBusqueda = '') {
        try {
            // Construye la URL hacia el backend enviando la acción y el filtro de búsqueda
            const url = `../php/api_productos.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;

            // Realiza la petición asíncrona GET utilizando Fetch API
            const respuesta = await fetch(url);

            // Convierte la respuesta recibida del servidor de formato JSON a un objeto JavaScript
            const resultado = await respuesta.json();

            // Si la consulta fue exitosa, envía los registros a la función de renderizado
            if (resultado.exito) {
                renderizarProductos(resultado.datos);
            } else {
                // Notifica en pantalla si ocurrió un problema al consultar
                contenedorProductos.innerHTML = `<p class="mensaje-vacio">No fue posible obtener la lista de productos.</p>`;
            }
        } catch (error) {
            // Captura errores de red o servidor en la consola
            console.error('Error al conectar con la API:', error);
            contenedorProductos.innerHTML = `<p class="mensaje-vacio">Error de conexión al cargar los productos.</p>`;
        }
    }

    // 3. DIBUJAR TARJETAS EN EL DOM (RENDER)

    function renderizarProductos(productos) {
        // Limpia el contenido anterior del contenedor de productos
        contenedorProductos.innerHTML = '';

        // Si la consulta no devuelve ningún registro, muestra un mensaje descriptivo
        if (productos.length === 0) {
            contenedorProductos.innerHTML = `<p class="mensaje-vacio">No hay productos registrados o coincidentes.</p>`;
            return;
        }

        // Recorre cada producto obtenido de la base de datos
        productos.forEach(prod => {
            // Formatea el precio de venta a exactamente 2 decimales
            const precioFormateado = parseFloat(prod.precio_venta).toFixed(2);
            
            // Define el nombre de la imagen a mostrar (usa por defecto placeholderFotoUsuario.png)
            const nombreImagen = prod.imagen ? prod.imagen : 'placeholderFotoUsuario.png';
            const rutaImagen = `../assets/${nombreImagen}`;

            // Crea un contenedor DIV para alojar la tarjeta individual
            const tarjeta = document.createElement('div');
            // Le asigna la clase CSS 'producto'
            tarjeta.classList.add('producto');

            // Inyecta la estructura HTML de la tarjeta del producto
            tarjeta.innerHTML = `
                <img src="${rutaImagen}" alt="${prod.nombre}">
                <h2>Nombre: ${prod.nombre}</h2>
                <h2>Precio: $${precioFormateado}</h2>
                <h2>Existencia: ${prod.existencia}</h2>
                <div class="acciones-tarjeta">
                    <button type="button" class="btn-editar-card" data-id="${prod.codigo}">Editar</button>
                    <button type="button" class="btn-eliminar-card" data-id="${prod.codigo}">Eliminar</button>
                </div>
            `;

            // Agrega la tarjeta creada al contenedor HTML del catálogo
            contenedorProductos.appendChild(tarjeta);
        });

        // Enlaza los botones de editar y eliminar recién insertados en el DOM
        asignarEventosTarjetas();
    }

    // 4. ASIGNAR LISTENERS A BOTONES DINÁMICOS (EDITAR Y ELIMINAR)
    function asignarEventosTarjetas() {
        // Recorre todos los botones con la clase .btn-editar-card
        document.querySelectorAll('.btn-editar-card').forEach(boton => {
            boton.addEventListener('click', (e) => {
                // Extrae el código del producto del atributo data-id
                const idProducto = e.target.getAttribute('data-id');
                // Llama a la función para cargar datos y abrir el modal en modo edición
                abrirModalEditar(idProducto);
            });
        });

        // Recorre todos los botones con la clase .btn-eliminar-card
        document.querySelectorAll('.btn-eliminar-card').forEach(boton => {
            boton.addEventListener('click', (e) => {
                // Extrae el código del producto del atributo data-id
                const idProducto = e.target.getAttribute('data-id');
                // Llama a la función para procesar la baja del registro
                eliminarProducto(idProducto);
            });
        });
    }

    // --------------------------------------------------------------------------
    // 5. MANEJO DE LA VENTANA MODAL (CREAR / EDITAR / CERRAR)
    // --------------------------------------------------------------------------
    function abrirModalAgregar() {
        // Limpia todos los inputs del formulario
        formProducto.reset();
        // Vacía el campo oculto de código para indicar que se creará un registro NUEVO
        inputCodigo.value = '';
        // Establece el título de la ventana flotante
        modalTitulo.textContent = 'Agregar Nuevo Producto';
        // Remueve la clase 'hidden' para hacer visible el modal
        modal.classList.remove('hidden');
    }

    async function abrirModalEditar(codigo) {
        try {
            // Solicita a la API los datos del producto específico mediante su ID
            const respuesta = await fetch(`../php/api_productos.php?accion=obtener_uno&id=${codigo}`);
            const resultado = await respuesta.json();

            // Comprueba si se recuperó el registro exitosamente
            if (resultado.exito) {
                const prod = resultado.datos;

                // Rellena cada input con los valores provenientes de la base de datos
                inputCodigo.value = prod.codigo;
                inputNombre.value = prod.nombre;
                selectTipo.value = prod.tipo_medicamento;
                inputLaboratorio.value = prod.laboratorio;
                inputLote.value = prod.lote || '';
                inputCaducidad.value = prod.fecha_caducidad;
                inputPrecio.value = prod.precio_venta;
                inputExistencia.value = prod.existencia;
                inputImagen.value = prod.imagen || '';

                // Cambia el título del modal y lo muestra en pantalla
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
        // Agrega la clase 'hidden' para ocultar la ventana modal
        modal.classList.add('hidden');
        // Resetea el formulario para limpiar los datos ingresados
        formProducto.reset();
    }

    // --------------------------------------------------------------------------
    // 6. GUARDAR O ACTUALIZAR REGISTRO (CREATE / UPDATE)
    // --------------------------------------------------------------------------
    formProducto.addEventListener('submit', async (e) => {
        // Cancela la recarga de página por defecto del submit HTML
        e.preventDefault();

        // Empaqueta los campos del formulario en un objeto FormData
        const formData = new FormData(formProducto);

        // Si existe un código guardado en el input oculto es actualización; si está vacío es creación
        const esEdicion = inputCodigo.value !== '';
        const accion = esEdicion ? 'actualizar' : 'crear';

        try {
            // Realiza la petición POST asíncrona hacia la API en PHP
            const respuesta = await fetch(`../php/api_productos.php?accion=${accion}`, {
                method: 'POST',
                body: formData
            });

            // Parsea la respuesta del servidor a formato JSON
            const resultado = await respuesta.json();

            // Si la operación fue exitosa en la base de datos
            if (resultado.exito) {
                // Cierra el modal flotante
                cerrarModal();
                // Actualiza el catálogo dinámicamente sin parpadear ni recargar la pantalla
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
        // Solicita confirmación previa al usuario
        const confirmacion = confirm(`¿Estás seguro de que deseas eliminar el producto #${codigo}?`);
        if (!confirmacion) return;

        // Prepara los datos del ID a enviar mediante POST
        const formData = new FormData();
        formData.append('codigo', codigo);

        try {
            // Envía la solicitud de eliminación a la API PHP
            const respuesta = await fetch('../php/api_productos.php?accion=eliminar', {
                method: 'POST',
                body: formData
            });

            const resultado = await respuesta.json();

            if (resultado.exito) {
                // Vuelve a consultar y redibujar el catálogo de productos
                cargarProductos(inputBuscar.value);
            } else {
                alert(`Error: ${resultado.mensaje}`);
            }
        } catch (error) {
            console.error('Error al intentar eliminar:', error);
            alert('Error en el servidor al intentar eliminar el producto.');
        }
    } // <-- Corrección: Se cierra únicamente la función con }

    // --------------------------------------------------------------------------
    // 8. ESCUCHADORES DE EVENTOS DE INTERFAZ FIJOS
    // --------------------------------------------------------------------------
    // Abre el modal al hacer clic en '+ Agregar Producto'
    btnAbrirAgregar.addEventListener('click', abrirModalAgregar);

    // Cierra el modal al hacer clic en el botón X o en Cancelar
    btnCerrarModal.addEventListener('click', cerrarModal);
    btnCancelarModal.addEventListener('click', cerrarModal);

    // Ejecuta la búsqueda filtrada en vivo cada vez que se escribe en el input superior
    inputBuscar.addEventListener('input', (e) => {
        cargarProductos(e.target.value);
    });

    // 9. INICIALIZACIÓN
    // Ejecuta la consulta inicial de productos al cargar la pantalla
    cargarProductos();

}); // <-- Cierra el evento DOMContentLoaded inicial
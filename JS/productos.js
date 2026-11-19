// Garantiza que el código se ejecute una vez que la estructura HTML del DOM esté completamente cargada
document.addEventListener('DOMContentLoaded', () => {

    // --------------------------------------------------------------------------
    // 1. SELECCIÓN DE ELEMENTOS DEL DOM
    // --------------------------------------------------------------------------
    // Contenedor principal donde se inyectan las tarjetas de los productos
    const contenedorProductos = document.getElementById('contenedor-productos');
    // Entrada de texto de la barra de búsqueda en tiempo real
    const inputBuscar = document.getElementById('input-buscar');
    // NUEVO: Selector desplegable para elegir el criterio de ordenamiento
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

    //  Variable donde guardamos los productos traídos de la BD para poder reordenarlos sin volver a consultar
    let productosEnMemoria = [];

   
    //  MÉTODO DE ORDENAMIENTO POR INSERCIÓN (INSERTION SORT)
   
    // Esta función compara dos productos ('a' y 'b') según el criterio elegido.
    // Devuelve un número mayor a 0 si 'a' debe ir DESPUÉS de 'b'.
    function compararProductos(a, b, criterio) {
        // Revisa qué opción seleccionó el usuario en el menú desplegable
        switch (criterio) {
            case 'precio_asc':
                // Resta los precios: si 'a' cuesta más que 'b', el resultado es positivo y 'a' se mueve a la derecha
                return parseFloat(a.precio_venta) - parseFloat(b.precio_venta);

            case 'precio_desc':
                // Resta al revés: si 'a' cuesta menos que 'b', el resultado es positivo y 'a' se mueve a la derecha
                return parseFloat(b.precio_venta) - parseFloat(a.precio_venta);

            case 'nombre_asc':
                // Compara alfabéticamente los nombres en español (de la A a la Z)
                return a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' });

            case 'existencia_asc':
                // Compara las cantidades en stock para dejar primero los que tienen menos piezas
                return parseInt(a.existencia, 10) - parseInt(b.existencia, 10);

            case 'caducidad_asc':
                // Convierte las fechas a números para dejar primero los productos más próximos a caducar
                return new Date(a.fecha_caducidad) - new Date(b.fecha_caducidad);

            case 'defecto':
            default:
                // Orden original por código de mayor a menor (los últimos agregados aparecen primero)
                return parseInt(b.codigo, 10) - parseInt(a.codigo, 10);
        }
    }

    // Algoritmo principal de Ordenamiento por Inserción
    function ordenarPorInsercion(listaProductos, criterio) {
        // Hace una copia del arreglo original para no modificar directamente los datos guardados
        const arreglo = [...listaProductos];

        // Guarda la cantidad total de productos que hay en la lista
        const total = arreglo.length;

        // Inicia un ciclo desde el segundo elemento (posición 1), porque el primero (posición 0) ya se asume ordenado
        for (let i = 1; i < total; i++) {
            // Guarda en una variable temporal el producto que vamos a acomodar en esta vuelta
            const productoActual = arreglo[i];

            // Crea el índice 'j' para mirar el elemento que está justo a la izquierda de 'productoActual'
            let j = i - 1;

            // Mientras no lleguemos al inicio de la lista (j >= 0) Y el producto de la izquierda deba ir después del actual:
            while (j >= 0 && compararProductos(arreglo[j], productoActual, criterio) > 0) {
                // Empuja el producto de la izquierda una posición hacia la derecha para abrir un hueco
                arreglo[j + 1] = arreglo[j];

                // Retrocede una posición hacia la izquierda para seguir comparando con los anteriores
                j--;
            }

            // Cuando el ciclo se detiene, coloca 'productoActual' en el hueco libre que quedó
            arreglo[j + 1] = productoActual;
        }

        // Regresa la lista ya ordenada
        return arreglo;
    }

    // Función que une el selector, el algoritmo de inserción y el dibujado de tarjetas
    function ordenarYMostrarProductos() {
        // Lee qué criterio está seleccionado actualmente en el desplegable
        const criterio = selectOrdenar ? selectOrdenar.value : 'defecto';

        // Llama al método de inserción para obtener la lista ordenada
        const productosOrdenados = ordenarPorInsercion(productosEnMemoria, criterio);

        // Envía la lista ya ordenada para que se dibujen las tarjetas en pantalla
        renderizarProductos(productosOrdenados);
    }

   
    // 2. CONSULTAR Y OBTENER PRODUCTOS (READ / SEARCH)
    
    async function cargarProductos(terminoBusqueda = '') {
        try {
            // Construye la URL hacia el controlador enviando la acción y el filtro de búsqueda
            const url = `../controllers/ProductoController.php?accion=leer&buscar=${encodeURIComponent(terminoBusqueda)}`;

            // Realiza la petición asíncrona GET utilizando Fetch API
            const respuesta = await fetch(url);

            // Convierte la respuesta recibida del servidor de formato JSON a un objeto JavaScript
            const resultado = await respuesta.json();

            // Si la consulta fue exitosa, guarda los registros en memoria, los ordena y los dibuja
            if (resultado.exito) {
                // Guarda los productos en la variable de memoria
                productosEnMemoria = resultado.datos;
                // Ejecuta el ordenamiento por inserción y dibuja las tarjetas
                ordenarYMostrarProductos();
            } else {
                // Notifica en pantalla si ocurrió un problema al consultar
                contenedorProductos.innerHTML = `<p class="mensaje-vacio">No fue posible obtener la lista de productos.</p>`;
            }
        } catch (error) {
            // Captura errores de red o servidor en la consola
            console.error('Error al conectar con el controlador:', error);
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

    // --------------------------------------------------------------------------
    // 4. ASIGNAR LISTENERS A BOTONES DINÁMICOS (EDITAR Y ELIMINAR)
    // --------------------------------------------------------------------------
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
            // Solicita al controlador los datos del producto específico mediante su ID
            const respuesta = await fetch(`../controllers/ProductoController.php?accion=obtener_uno&id=${codigo}`);
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
            // Realiza la petición POST asíncrona hacia el controlador en PHP
            const respuesta = await fetch(`../controllers/ProductoController.php?accion=${accion}`, {
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
            // Envía la solicitud de eliminación al controlador PHP
            const respuesta = await fetch('../controllers/ProductoController.php?accion=eliminar', {
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
    }

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

    //  Reordena las tarjetas al instante cada vez que cambias la opción del menú desplegable
    if (selectOrdenar) {
        selectOrdenar.addEventListener('change', ordenarYMostrarProductos);
    }

    // --------------------------------------------------------------------------
    // 9. INICIALIZACIÓN
    // --------------------------------------------------------------------------
    // Ejecuta la consulta inicial de productos al cargar la pantalla
    cargarProductos();

});
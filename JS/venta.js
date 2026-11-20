// Definimos la estructura de datos que tendrá cada producto del catálogo (reemplazará a T en la lista)
/**
- @typedef {Object} ProductoCatalogo
- @property {string|number} codigo
- @property {string} nombre
- @property {number} precio
- @property {number} existencia
 */

// Definimos la estructura de datos que tendrá cada artículo en la nota de venta (reemplazará a T en la pila)
/**
- @typedef {Object} ItemVenta
- @property {string|number} codigo
- @property {string} nombre
- @property {number} precio
- @property {number} cantidad
- @property {number} subtotal
 */

// Clase genérica Nodo<T>: el @template T indica que puede guardar cualquier tipo de dato (ProductoCatalogo, ItemVenta, etc.)
/**
- @template T //Declaramos el comodin generico T
 */
class Nodo {
    // El constructor recibe un dato genérico de tipo T, validando que seal el mismo tipo de dato declarado arriba
    /**
- @param {T} dato
     */

    //Esto es lo que se usa al momento en el que escribimos new nodo, recibe un solo parametro (dato) 
    //El dato es el paquete completo de la info, debe recibir o ProductoCatalogo o ItemVenta
    constructor(dato) {
        this.dato = dato; // Guarda el objeto completo (producto o item de venta) dentro del nodo
        /** @type {Nodo<T> | null} */ //Validacion o debe guardar un T o un null en caso de que este vacia, para evitar errores
        this.siguiente = null; // Puntero que apunta al siguiente nodo en memoria (inicia en null)
    }
}

// Clase que gestiona la Lista Enlazada Simple del Catálogo usando nodos genéricos Nodo<ProductoCatalogo>
class ListaProductosCatalogo {
    // Constructor de la lista
    constructor() {
        /** @type {Nodo<ProductoCatalogo> | null} */ //Validacion o guarda un ProductoCatalogo o null para evitar errores
        this.cabeza = null; // Puntero al primer nodo de la lista (nulo al inicializar)
    }

    // Método para insertar un nuevo producto al final de la lista enlazada
    /**
- @param {ProductoCatalogo} producto
     */
    insertar(producto) {
        // Instancia un nuevo nodo genérico guardando el objeto producto en su interior
        const nuevoNodo = new Nodo(producto);

        // Si la lista está vacía, el nuevo nodo se convierte en la cabeza de la lista
        if (this.cabeza === null) {
            this.cabeza = nuevoNodo;
        } else {
            // Si ya hay nodos, creamos un puntero auxiliar 'actual' empezando en la cabeza
            let actual = this.cabeza;

            // Recorremos la lista hasta llegar al último nodo (cuyo puntero 'siguiente' sea null)
            while (actual.siguiente !== null) {
                actual = actual.siguiente; // Avanzamos el puntero al siguiente nodo
            }

            // Conectamos el último nodo existente con el nuevo nodo
            actual.siguiente = nuevoNodo;
        }
    }

    // Algoritmo de Búsqueda Secuencial a través de los nodos de la lista
    /**
- @param {string} criterio //Indicamos que el parametro que se utilizara sera string llamado criterio
- @returns {Nodo<ProductoCatalogo> | null} //Validacion, o retorna Nodo o Nulo
     */
    buscarSecuencial(criterio) {
        // Si el término introducido está vacío o no es válido, retorna null
        if (!criterio || criterio.trim() === "") return null;

        // Limpiamos espacios laterales y convertimos el texto ingresado a minúsculas
        const termino = criterio.trim().toLowerCase();

        // Puntero auxiliar para iniciar la búsqueda desde la cabeza
        let actual = this.cabeza;

        // El RECORRIDO SECUENCIAL se seguirá ejecutando mientras el nodo actual no sea null
        while (actual !== null) {
            // Entramos a 'actual.dato' para leer el código y el nombre del producto guardado en el nodo
            const codigoStr = String(actual.dato.codigo).toLowerCase();
            const nombreStr = String(actual.dato.nombre).toLowerCase();

            // Verificamos coincidencia exacta por código o coincidencia parcial en el nombre (usando indexOf)
            if (codigoStr === termino || nombreStr.indexOf(termino) !== -1) {
                return actual; // Si coincide, retorna el nodo encontrado y detiene la búsqueda
            }

            // Si no coincide, movemos el puntero al siguiente nodo en memoria
            actual = actual.siguiente;
        }

        // Si se recorrió toda la lista y no se encontró coincidencia, retorna null
        return null;
    }
}

// Se crea la instancia global de la Lista Enlazada Simple para el catálogo de productos
const catalogoLista = new ListaProductosCatalogo();

// Se verifica que la variable 'productosDesdeBD' (enviada por PHP) exista y sea un arreglo
if (typeof productosDesdeBD !== 'undefined' && Array.isArray(productosDesdeBD)) {
    // Recorremos cada registro proveniente de la base de datos
    productosDesdeBD.forEach(prod => {
        // Convertimos el precio a decimal flotante o usamos 0.0 si viene vacío
        const precio = parseFloat(prod.precio_venta) || 0.0;
        // Convertimos la existencia a número entero o usamos 0 si viene vacía
        const existencia = parseInt(prod.existencia) || 0;

        // Insertamos el objeto producto dentro de un Nodo<ProductoCatalogo> en la lista
        catalogoLista.insertar({
            codigo: prod.codigo,
            nombre: prod.nombre,
            precio: precio,
            existencia: existencia
        });
    });
}

// Función ejecutada automáticamente cada vez que el cajero escribe en el buscador
function buscarProductoCatalogo() {
    // Capturamos el texto actual ingresado en el input de búsqueda
    const inputBusqueda = document.getElementById("productoInput").value;
    // Referencia al campo de texto del precio
    const inputPrecio = document.getElementById("precioInput");
    // Referencia al elemento HTML donde se muestran las alertas
    const msgBusqueda = document.getElementById("mensaje-busqueda");

    // Ejecutamos la búsqueda secuencial nodal sobre la lista enlazada
    const nodoEncontrado = catalogoLista.buscarSecuencial(inputBusqueda);

    if (nodoEncontrado !== null) {
        // Asignamos el precio desde 'nodoEncontrado.dato.precio' formateado a 2 decimales
        inputPrecio.value = nodoEncontrado.dato.precio.toFixed(2);
        // Mostramos el nombre y el stock disponible leyendo 'nodoEncontrado.dato'
        msgBusqueda.innerText = `Producto encontrado: ${nodoEncontrado.dato.nombre} (Stock: ${nodoEncontrado.dato.existencia})`;
        msgBusqueda.style.color = "#16C79A";
    } else {
        // Si no lo encuentra, vaciamos el campo de precio
        inputPrecio.value = "";
        // Si el usuario ingresó algo pero no hubo coincidencia, mostramos mensaje de error
        if (inputBusqueda.trim() !== "") {
            msgBusqueda.innerText = "Producto no encontrado en el catálogo.";
            msgBusqueda.style.color = "#d9534f";
        } else {
            // Si el input está vacío, borramos el mensaje informativo
            msgBusqueda.innerText = "";
        }
    }
}

// Clase que gestiona la Pila (LIFO) de la nota de venta reutilizando el mismo Nodo<ItemVenta> genérico
class PilaVenta {
    // Constructor de la pila
    // Se indica en el constructor que se utilizara el ItemVenta
    constructor() {
        /** @type {Nodo<ItemVenta> | null} */ //Validacion
        this.tope = null; // Puntero que señala al elemento superior (cima) de la pila
        this.tamano = 0;  // Contador de elementos insertados en la pila
    }

    // Agrega un nuevo nodo en la cima de la pila
    /**
- @param {ItemVenta} item //Indicamos que el parametro que se utilizara debe ser ItemVenta
     */
    push(item) {
        // Creamos una nueva instancia del nodo genérico guardando el artículo de venta
        const nuevoNodo = new Nodo(item);

        // El nuevo nodo apunta hacia el antiguo tope (quedando posicionado arriba de él)
        nuevoNodo.siguiente = this.tope;

        // El tope de la pila se actualiza para apuntar al nuevo nodo recién creado
        this.tope = nuevoNodo;
        // Incrementamos el contador de elementos en la pila
        this.tamano++;

        // Redibujamos la tabla HTML para reflejar los cambios en pantalla
        this.actualizarUI();
    }

    // Elimina y desvincula el nodo ubicado en el tope de la pila
    /**
- @returns {Nodo<ItemVenta> | null} //Retorna o Nodo ItemVenta o nulo para evitar errores
     */
    pop() {
        // Verificamos si la pila está vacía antes de intentar sacar un elemento
        if (this.isEmpty()) {
            alert("No hay productos en la nota de venta para deshacer.");
            return null;
        }

        // Guardamos una referencia temporal del nodo que va a salir
        const nodoExtraido = this.tope;

        // Movemos el tope hacia el nodo que estaba inmediatamente abajo ('siguiente')
        this.tope = this.tope.siguiente;
        // Reducimos el contador de elementos en la pila
        this.tamano--;

        // Redibujamos la tabla HTML sin el elemento eliminado
        this.actualizarUI();
        // Retornamos el nodo que fue extraído de la cima
        return nodoExtraido;
    }

    // Método auxiliar que verifica si la pila está completamente vacía
    isEmpty() {
        return this.tope === null;
    }

    // Método RECURSIVO que recorre la pila nodo por nodo acumulando el subtotal
    /**
- @param {Nodo<ItemVenta> | null} nodo //El parametro que se utilizara es o un ItemVenta o nulo
- @returns {number} //Regresa si o si un numero
     */
    calcularSubtotalRecursivo(nodo) {
        // Caso base: si llegamos al final de la pila (puntero nulo), retornamos 0
        if (nodo === null) {
            return 0;
        }
        //Se utiliza al calcular la recursividad 
        // Caso recursivo: sumamos 'nodo.dato.subtotal' más el resultado de llamar a la función con el siguiente nodo
        return nodo.dato.subtotal + this.calcularSubtotalRecursivo(nodo.siguiente);
    }

    // Método que recorre los nodos de la pila para mostrarlos en la interfaz
    actualizarUI() {
        // Referencia al cuerpo de la tabla HTML donde se dibujan las filas
        const tbody = document.getElementById("cuerpoTablaVenta");
        // Referencia al elemento HTML donde se imprime el subtotal
        const subtotalElem = document.getElementById("subtotal-val");
        // Referencia al elemento HTML donde se imprime el total con IVA
        const totalElem = document.getElementById("total-val");

        // Limpiamos el contenido anterior de la tabla
        tbody.innerHTML = "";

        // Ejecutamos la función recursiva pasando la cima de la pila (this.tope) para obtener el subtotal
        const subtotalAcumulado = this.calcularSubtotalRecursivo(this.tope);

        // Puntero auxiliar para recorrer los nodos desde la cima hasta el fondo
        let actual = this.tope;

        // Mientras queden nodos por recorrer en la pila
        while (actual !== null) {
            // Creamos una fila HTML <tr>
            const fila = document.createElement("tr");

            //Se utiliza al momento de estar dibujando la tabla, con dato.(nombre,cantidad,etc.)
            // Llenamos las celdas leyendo las propiedades dentro de 'actual.dato'
            fila.innerHTML = `
                <td style="font-family: Onest, sans-serif">${actual.dato.nombre}</td>
                <td style="font-family: Onest, sans-serif">${actual.dato.cantidad}</td>
                <td style="font-family: Onest, sans-serif">$${actual.dato.precio.toFixed(2)}</td>
                <td style="font-family: Onest, sans-serif">$${actual.dato.subtotal.toFixed(2)}</td>
            `;

            // Agregamos la fila creada al cuerpo de la tabla
            tbody.appendChild(fila);
            // Avanzamos al siguiente nodo de abajo
            actual = actual.siguiente;
        }

        // Calculamos el total aplicando el 16% de IVA sobre el subtotal recursivo
        const totalConIVA = subtotalAcumulado * 1.16;

        // Imprimimos el subtotal y el total formateados a 2 decimales en la pantalla
        subtotalElem.innerText = subtotalAcumulado.toFixed(2);
        totalElem.innerText = totalConIVA.toFixed(2);
    }

    // Extrae los datos de cada nodo de la pila y los guarda en un arreglo para enviarlos a PHP
    /**
- @returns {ItemVenta[]}
     */
    obtenerArrayProductos() {
        // Creamos un arreglo vacío para almacenar los productos vendidos
        const productos = [];
        // Puntero auxiliar que inicia en la cima de la pila
        let actual = this.tope;

        // Recorremos toda la pila mientras el puntero no sea nulo
        while (actual !== null) {
            // Usamos nuevamente el tipo de dato generico al momento de armar el JSON para PHP
            // Extraemos las propiedades de 'actual.dato' y las agregamos al arreglo
            productos.push({
                codigo: actual.dato.codigo,
                nombre: actual.dato.nombre,
                precio: actual.dato.precio,
                cantidad: actual.dato.cantidad,
                subtotal: actual.dato.subtotal
            });
            // Avanzamos al nodo que está debajo en la pila
            actual = actual.siguiente;
        }

        // Retornamos el arreglo listo para convertirse a JSON
        return productos;
    }

    // Vacía la pila al terminar la venta para atender al siguiente cliente
    limpiar() {
        this.tope = null; // Desvinculamos todos los nodos apuntando el tope a null
        this.tamano = 0;  // Reiniciamos el contador de elementos a 0
        this.actualizarUI(); // Limpiamos la tabla y los totales en pantalla
    }
}

// Instancia global de la pila de productos en la nota de venta
const ventaPila = new PilaVenta();

// GESTIÓN Y BÚSQUEDA DE CLIENTES REGISTRADOS EN LA VENTA
let clienteSeleccionado = null; // null representa "Público en general"

function abrirModalClienteVenta() {
    const modal = document.getElementById("modal-seleccionar-cliente");
    const inputBuscar = document.getElementById("buscarClienteVentaInput");
    if (inputBuscar) inputBuscar.value = "";
    renderizarClientesModal(typeof clientesDesdeBD !== "undefined" ? clientesDesdeBD : []);
    if (modal) modal.classList.remove("hidden");
    inputBuscar?.focus();
}

function cerrarModalClienteVenta() {
    const modal = document.getElementById("modal-seleccionar-cliente");
    if (modal) modal.classList.add("hidden");
}

function filtrarClientesModal() {
    const termino = (document.getElementById("buscarClienteVentaInput")?.value || "").trim().toLowerCase();
    const lista = typeof clientesDesdeBD !== "undefined" ? clientesDesdeBD : [];

    if (!termino) {
        renderizarClientesModal(lista);
        return;
    }

    const filtrados = lista.filter(c => {
        const codigo = String(c.codigo).toLowerCase();
        const nombre = (c.nombre || "").toLowerCase();
        const telefono = (c.telefono || "").toLowerCase();
        const rfc = (c.rfc || "").toLowerCase();
        return codigo.includes(termino) || nombre.includes(termino) || telefono.includes(termino) || rfc.includes(termino);
    });

    renderizarClientesModal(filtrados);
}

function renderizarClientesModal(clientes) {
    const tbody = document.getElementById("tabla-clientes-venta-cuerpo");
    if (!tbody) return;

    tbody.innerHTML = "";

    if (!clientes || clientes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="mensaje-tabla-vacia">
                    No se encontraron clientes registrados en la base de datos.
                </td>
            </tr>
        `;
        return;
    }

    clientes.forEach(cliente => {
        const fila = document.createElement("tr");
        fila.style.cursor = "pointer";
        fila.title = "Haz clic para asignar este cliente a la venta";

        fila.innerHTML = `
            <td><div class="celda-caja celda-codigo">${cliente.codigo}</div></td>
            <td><div class="celda-caja">${cliente.nombre}</div></td>
            <td><div class="celda-caja">${cliente.telefono || ""}</div></td>
            <td><div class="celda-caja">${cliente.rfc || ""}</div></td>
        `;

        fila.addEventListener("click", () => {
            seleccionarClienteParaVenta(cliente);
        });

        tbody.appendChild(fila);
    });
}

function seleccionarClienteParaVenta(cliente) {
    clienteSeleccionado = {
        codigo: parseInt(cliente.codigo),
        nombre: cliente.nombre
    };
    document.getElementById("clienteInput").value = cliente.nombre;
    document.getElementById("clienteCodigoInput").value = cliente.codigo;
    cerrarModalClienteVenta();
}

function seleccionarPublicoGeneral() {
    clienteSeleccionado = null;
    document.getElementById("clienteInput").value = "Público en general";
    document.getElementById("clienteCodigoInput").value = "";
    cerrarModalClienteVenta();
}

// Función ejecutada al hacer clic en el botón "Registrar"
function agregarProducto() {
    // Referencias a los campos de entrada del formulario
    const inputBusqueda = document.getElementById("productoInput");
    const inputPrecio = document.getElementById("precioInput");
    const inputCantidad = document.getElementById("cantidadInput");

    // Extraemos y limpiamos los valores ingresados por el cajero
    const criterio = inputBusqueda.value.trim();
    const precio = parseFloat(inputPrecio.value);
    const cantidad = parseInt(inputCantidad.value);

    // Buscamos el nodo coincidente en la lista enlazada usando la búsqueda secuencial
    const nodoEncontrado = catalogoLista.buscarSecuencial(criterio);

    // Validamos que el texto ingresado exista en el catálogo
    if (!nodoEncontrado) {
        alert("Por favor, busque y seleccione un producto válido del catálogo antes de registrar.");
        return;
    }

    // Validamos que el precio y la cantidad sean números mayores a cero
    if (isNaN(precio) || isNaN(cantidad) || cantidad <= 0 || precio <= 0) {
        alert("Ingrese datos válidos de precio y cantidad.");
        return;
    }

    // Insertamos en la pila un objeto ItemVenta usando los datos de 'nodoEncontrado.dato'
    ventaPila.push({
        codigo: nodoEncontrado.dato.codigo,
        nombre: nodoEncontrado.dato.nombre,
        precio: precio,
        cantidad: cantidad,
        subtotal: precio * cantidad
    });

    // Reseteamos los campos del formulario para capturar el siguiente producto
    inputBusqueda.value = "";
    inputPrecio.value = "";
    inputCantidad.value = "1";
    document.getElementById("mensaje-busqueda").innerText = ""; // Limpia el mensaje de búsqueda
    inputBusqueda.focus(); // Coloca el cursor automáticamente en el buscador
}

// Función ejecutada al hacer clic en el botón "Deshacer"
function deshacerUltimo() {
    ventaPila.pop(); // Saca el último producto agregado en la cima de la pila
}

// Función ejecutada cuando el cajero presiona el botón de cobrar
function finalizarVenta() {
    // Verificamos que la pila tenga al menos un producto antes de cobrar
    if (ventaPila.isEmpty()) {
        alert("Agregue al menos un producto a la lista antes de cobrar.");
        return;
    }

    // Si aún no se ha seleccionado un cliente, preguntamos si desea asociar uno
    if (clienteSeleccionado === null) {
        const deseaAgregarCliente = confirm(
            "¿Desea agregar un cliente registrado a esta venta?\n\n" +
            "• Aceptar: Buscar y seleccionar un cliente.\n" +
            "• Cancelar: Registrar venta como Público en general."
        );

        if (deseaAgregarCliente) {
            abrirModalClienteVenta();
            return;
        }
    }

    // Leemos el monto total con IVA desde la pantalla y lo convertimos a decimal
    const totalConIVA = parseFloat(document.getElementById("total-val").innerText);
    // Obtenemos el arreglo de productos extraídos de la pila
    const productos = ventaPila.obtenerArrayProductos();

    // Empaquetamos el total, el cliente (o null) y los productos para enviarlo al servidor
    const datosVenta = {
        total: totalConIVA,
        codigo_cliente: clienteSeleccionado ? clienteSeleccionado.codigo : null,
        productos: productos
    };

    // Enviamos los datos mediante una petición POST al controlador de ventas en PHP
    fetch('../controllers/VentaController.php?accion=guardar', {
        method: 'POST', // Método POST para enviar nuevos datos al servidor
        headers: { 'Content-Type': 'application/json' }, // Indicamos que el cuerpo viaja en formato JSON
        body: JSON.stringify(datosVenta) // Convertimos el objeto JavaScript a texto JSON
    })
    .then(respuesta => respuesta.json()) // Convertimos la respuesta del servidor de JSON a objeto JS
    .then(data => {
        // Si la venta se registró correctamente en la base de datos
        if (data.success) {
            const nombreClienteFinal = clienteSeleccionado ? clienteSeleccionado.nombre : "Público en general";
            alert(`¡Venta guardada con éxito! Nota #${data.numero_nota}\nCliente: ${nombreClienteFinal}`);
            ventaPila.limpiar(); // Vaciamos la pila y limpiamos la pantalla para la siguiente venta
            seleccionarPublicoGeneral(); // Reiniciamos el campo de cliente a Público en general
        } else {
            alert("Error al guardar: " + data.message); // Mostramos el motivo si ocurrió un error
        }
    })
    .catch(error => {
        // Capturamos e imprimimos en consola cualquier error de red o conexión
        console.error("Error en la conexión:", error);
    });
}
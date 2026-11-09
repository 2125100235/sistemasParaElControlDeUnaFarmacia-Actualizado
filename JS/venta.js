// Clase que define la estructura individual (Nodo) para cada producto del catálogo
class NodoCatalogo {
    // El constructor recibe las propiedades del producto traídas de la base de datos
    constructor(codigo, nombre, precio, existencia) {
        this.codigo = codigo; // Guarda el código o ID único del producto
        this.nombre = nombre; // Guarda el nombre oficial del producto
        this.precio = parseFloat(precio) || 0.0; // Convierte el precio a un número decimal flotante
        this.existencia = parseInt(existencia) || 0; // Convierte el stock/existencia a un número entero
        this.siguiente = null; // Puntero que apunta al siguiente nodo en la lista (inicia en null)
    }
}

// Clase que gestiona la Lista Enlazada Simple del Catálogo
class ListaProductosCatalogo {
    // Constructor de la lista
    constructor() {
        this.cabeza = null; // Puntero al primer nodo de la lista (nulo al inicializar)
    }

    // Método para insertar un nuevo producto al final de la lista enlazada
    insertar(codigo, nombre, precio, existencia) {
        // Instancia un nuevo nodo con la información del producto
        const nuevoNodo = new NodoCatalogo(codigo, nombre, precio, existencia);

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
    buscarSecuencial(criterio) {
        // Si el término introducido está vacío o no es válido, retorna que es nulo
        if (!criterio || criterio.trim() === "") return null;

        // Limpiamos espacios laterales y convertimos el texto ingresado a minúsculas
        const termino = criterio.trim().toLowerCase();

        // Puntero auxiliar para iniciar la búsqueda desde la cabeza
        let actual = this.cabeza;

        // El recorrido secuencial se seguirá ejecutando mientras el nodo actual no sea null
        while (actual !== null) {
            // Convertimos el código y nombre del nodo actual a minúsculas para comparar
            const codigoStr = String(actual.codigo).toLowerCase();
            const nombreStr = String(actual.nombre).toLowerCase();

            // Verificamos coincidencia exacta por código O coincidencia parcial en el nombre (usando indexOf)
            if (codigoStr === termino || nombreStr.indexOf(termino) !== -1) {
                return actual; // Si coincide, retorna el objeto Nodo encontrado y detiene la búsqueda
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

// Se verifica que la variable 'productosDesdeBD' (puesta por medio de PHP) exista y sea un arreglo[
if (typeof productosDesdeBD !== 'undefined' && Array.isArray(productosDesdeBD)) {
    // Recorremos cada registro proveniente de la base de datos
    productosDesdeBD.forEach(prod => {
        // Determinamos el precio
        const precio = prod.precio_venta ?? 0.00;

        // Insertamos cada producto como un nodo en nuestra lista enlazada 'catalogoLista'
        catalogoLista.insertar(prod.codigo, prod.nombre, precio, prod.existencia);
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
        // Asignamos el precio del nodo al input de precio formateado a 2 decimales
        inputPrecio.value = nodoEncontrado.precio.toFixed(2);
        // Mostramos el nombre y el stock disponible del producto encontrado
        msgBusqueda.innerText = `Producto encontrado: ${nodoEncontrado.nombre} (Stock: ${nodoEncontrado.existencia})`;
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

// Clase que define cada elemento (Nodo) que se agregará a la tabla de la venta
class NodoVenta {
    // Recibe el nombre, el codigo, el precio unitario y la cantidad a vender
    constructor(codigo, nombre, precio, cantidad) {
        this.codigo = codigo;
        this.nombre = nombre;
        this.precio = parseFloat(precio);
        this.cantidad = parseInt(cantidad);
        this.subtotal = this.precio * this.cantidad;
        this.siguiente = null; // Puntero al nodo que estará debajo en la pila
    }
}

// Clase que gestiona la pila
class PilaVenta {
    // Constructor de la pila
    constructor() {
        this.tope = null; // Puntero que señala al elemento superior (cima) de la pila
        this.tamano = 0;  // Contador de elementos insertados en la pila
    }

    // Agrega un nuevo nodo en la cima de la pila
    push(codigo, nombre, precio, cantidad) {
        // Creamos una nueva instancia del nodo de venta
        const nuevoNodo = new NodoVenta(codigo, nombre, precio, cantidad);

        // El nuevo nodo apunta hacia el antiguo tope (quedando posicionado arriba de él)
        nuevoNodo.siguiente = this.tope;

        // El tope de la pila se actualiza para apuntar al nuevo nodo recién creado
        this.tope = nuevoNodo;
        this.tamano++;

        // Redibujamos la tabla HTML para reflejar los cambios en pantalla
        this.actualizarUI();
    }

    // Elimina y desvincula el nodo ubicado en el tope de la pila
    pop() {
        if (this.isEmpty()) {
            alert("No hay productos en la nota de venta para deshacer.");
            return null;
        }

        // Guardamos una referencia temporal del nodo que va a salir
        const nodoExtraido = this.tope;

        // Movemos el tope hacia el nodo que estaba inmediatamente abajo ('siguiente')
        this.tope = this.tope.siguiente;
        this.tamano--;

        // Redibujamos la tabla HTML sin el elemento eliminado
        this.actualizarUI();
        return nodoExtraido;
    }

    // Método auxiliar que verifica si la pila está completamente vacía
    isEmpty() {
        return this.tope === null;
    }

    // Método que recorre los nodos de la pila para mostrarlos en la interfaz
    actualizarUI() {
        const tbody = document.getElementById("cuerpoTablaVenta");
        const subtotalElem = document.getElementById("subtotal-val");
        const totalElem = document.getElementById("total-val");

        // Limpiamos el contenido anterior del cuerpo de la tabla
        tbody.innerHTML = "";
        // Puntero auxiliar empezando en el tope de la pila
        let actual = this.tope;
        // Acumulador para la suma total de los subtotales
        let subtotalAcumulado = 0;

        // Recorremos la pila desde el tope hacia abajo hasta encontrar un nodo null
        while (actual !== null) {
            // Sumamos el subtotal del nodo actual al acumulador
            subtotalAcumulado += actual.subtotal;

            // Creamos un elemento <tr> (fila de tabla) en la página
            const fila = document.createElement("tr");

            // Rellenamos la fila HTML con los datos extraídos del nodo actual
            fila.innerHTML = `
                <td style="font-family: Onest, sans-serif";>${actual.nombre}</td>
                <td style="font-family: Onest, sans-serif";>${actual.cantidad}</td>
                <td style="font-family: Onest, sans-serif";>$${actual.precio.toFixed(2)}</td>
                <td style="font-family: Onest, sans-serif";>$${actual.subtotal.toFixed(2)}</td>
            `;

            // Insertamos la fila dentro del cuerpo de la tabla
            tbody.appendChild(fila);

            // Avanzamos el puntero auxiliar al nodo de abajo
            actual = actual.siguiente;
        }

        // Calculamos el total agregando el 16% de IVA
        const totalConIVA = subtotalAcumulado * 1.16;

        subtotalElem.innerText = subtotalAcumulado.toFixed(2);
        totalElem.innerText = totalConIVA.toFixed(2);
    }

    //Creamos un nuevo metodo, este metodo lo que hara es trabajar directamente con los datos de la pila, pasandolos a una array para poder
    // posteriormente enviarlos a php
    obtenerArrayProductos(){
        const productos = []; //creamos un nuevo array llamado productos, aqui iremos guardando producto por producto
        let actual = this.tope; //creamos una variable auxiliar "actual" la cual indicará siempre al producto que está hasta arriba de la pila
        while (actual !== null) { //ciclo while con la condicion de que: mientras actual no apunte a nulo, haz lo siguiente.
        productos.push({ //productos es el array que creamos, entonces agregará siempre al final del array el codigo, nombre, etc. del producto el cual "actual" este señalando
            codigo: actual.codigo,
            nombre: actual.nombre,
            precio: actual.precio,
            cantidad: actual.cantidad,
            subtotal: actual.subtotal
            });
            actual = actual.siguiente; //Mueve el indicador al elemento que esta justo abajo de la pila, esto hace que se recorra producto por producto
        }
        return productos; //una vez que se termina la pila, retornamos la lista completa con todos los productos de la pila
    }

    // Vacía la pila al terminar la venta, "reseta" el punto de venta lista para atender el siguiente cliente
    limpiar() {
        this.tope = null;
        this.tamano = 0;
        this.actualizarUI();
    }
}

// Instancia global de la pila de productos en la nota de venta
const ventaPila = new PilaVenta();

// Función ejecutada al hacer clic en el botón "Registrar"
function agregarProducto() {
    const inputBusqueda = document.getElementById("productoInput");
    const inputPrecio = document.getElementById("precioInput");
    const inputCantidad = document.getElementById("cantidadInput");

    // Extraemos los valores de los inputs
    const criterio = inputBusqueda.value.trim();
    const precio = parseFloat(inputPrecio.value);
    const cantidad = parseInt(inputCantidad.value);

    // Buscamos el nodo coincidente en la lista enlazada usando la búsqueda secuencial
    const nodoEncontrado = catalogoLista.buscarSecuencial(criterio);

    // Validación: Si el texto introducido no coincide con ningún nodo del catálogo
    if (!nodoEncontrado) {
        alert("Por favor, busque y seleccione un producto válido del catálogo antes de registrar.");
        return;
    }

    if (isNaN(precio) || isNaN(cantidad) || cantidad <= 0 || precio <= 0) {
        alert("Ingrese datos válidos de precio y cantidad.");
        return;
    }

    // Insertamos en la pila de la nota de venta el nombre y codigo del nodo hallado en la BD
    ventaPila.push(nodoEncontrado.codigo, nodoEncontrado.nombre, precio, cantidad);

    // Reseteamos los campos del formulario para la siguiente venta
    inputBusqueda.value = "";
    inputPrecio.value = "";
    inputCantidad.value = "1";
    document.getElementById("mensaje-busqueda").innerText = ""; // Limpia la alerta
    inputBusqueda.focus(); // Coloca el cursor en el buscador automáticamente
}

// Función ejecutada al hacer clic en el botón "Deshacer"
function deshacerUltimo() {
    ventaPila.pop();
}

//declaramos la funcion, es la funcion que se ejecuta cuando el cajero da al boton de cobrar
function finalizarVenta() { 
    //si la pila esta vacia, muestra un mensaje al cajero de agregar por lo menos un producto
    if (ventaPila.isEmpty()) {
        alert("Agregue al menos un producto a la lista antes de cobrar.");
        return;
    }

    const totalConIVA = parseFloat(document.getElementById("total-val").innerText); //lee el texto del monto total con IVA que esta en la pantalla y lo convierte en decimal 
    const productos = ventaPila.obtenerArrayProductos(); //llama al metodo del array de los productos y los guarda en la variable productos

    // Empaquetamos los datos que enviaremos a PHP dentro del objeto datosVenta, el total a pagar y los productos que se vendieron
    const datosVenta = {
        total: totalConIVA,
        productos: productos
    };

    //Enviamos los datos mediante una petición fetch al archivo guardar_venta en PHP 
    fetch('../php/guardar_venta.php', {
        method: 'POST', //indicamos el metodo en lo que lo estamos pasando, post significa que estamos pasando datos nuevos, a diferencia de get que es solo para leer.
        headers: { 'Content-Type': 'application/json' }, //le avisa a php que le estamos pasando un JSON
        body: JSON.stringify(datosVenta) //JSON.stringify toma nuestro paquete y lo convierte a una cadena de texto lista para viajar por la red sin errores.
    })
    .then(respuesta => respuesta.json()) //Espera la respuesta de php, y a esa respuesta la convierte en JSON
    .then(data => { //recibe los datos ya procesados y los guarda en la varuable data

        //si la venta se guardo con exito en mysql, manda un aviso, y limpia la pantalla para la siguiente venta
        if (data.success) {
            alert(`¡Venta guardada con éxito! Nota #${data.numero_nota}`);
            ventaPila.limpiar(); // Limpia la pantalla para la siguiente venta
        } else {
            alert("Error al guardar: " + data.message); //si no se guardo exitosamente, muestra la causa
        }
    })

    //catch atrapa errores de red, e imprime en la consola el error. 
    .catch(error => {
        console.error("Error en la conexión:", error);
    });
}
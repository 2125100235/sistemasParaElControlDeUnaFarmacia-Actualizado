// 1. Definición del Nodo
class NodoVenta {
    constructor(nombre, precio, cantidad) {
        this.nombre = nombre;
        this.precio = parseFloat(precio);
        this.cantidad = parseInt(cantidad);
        this.subtotal = this.precio * this.cantidad;
        this.siguiente = null;
    }
}

// Estructura de la Pila basada en Nodos
class PilaVenta {
    constructor() {
        this.tope = null;
        this.tamano = 0;
    }

    // Operación Push: Inserta un nuevo nodo al tope
    push(nombre, precio, cantidad) {
        const nuevoNodo = new NodoVenta(nombre, precio, cantidad);
        nuevoNodo.siguiente = this.tope;
        this.tope = nuevoNodo;
        this.tamano++;
        this.actualizarUI();
    }

    // Operación Pop: Extrae y desvincula el nodo del tope
    pop() {
        if (this.isEmpty()) {
            alert("No hay productos en la nota de venta para deshacer.");
            return null;
        }
        const nodoExtraido = this.tope;
        this.tope = this.tope.siguiente; // El tope pasa a ser el nodo de abajo
        this.tamano--;
        this.actualizarUI();
        return nodoExtraido;
    }

    isEmpty() {
        return this.tope === null;
    }

    // Recorrido de punteros para renderizar en HTML y calcular totales
    actualizarUI() {
        const tbody = document.getElementById("cuerpoTablaVenta");
        const subtotalElem = document.getElementById("subtotal-val");
        const totalElem = document.getElementById("total-val");

        tbody.innerHTML = ""; // Limpieza del contenedor
        let actual = this.tope;
        let subtotalAcumulado = 0;

        // Recorrido secuencial del nodo superior al inferior
        while (actual !== null) {
            subtotalAcumulado += actual.subtotal;

            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>${actual.nombre}</td>
                <td>${actual.cantidad}</td>
                <td>$${actual.precio.toFixed(2)}</td>
                <td>$${actual.subtotal.toFixed(2)}</td>
            `;
            tbody.appendChild(fila);

            actual = actual.siguiente; // Avanza el puntero
        }

        const totalConIVA = subtotalAcumulado * 1.16;

        subtotalElem.innerText = subtotalAcumulado.toFixed(2);
        totalElem.innerText = totalConIVA.toFixed(2);
    }
}

// Instancia global de la Pila
const ventaPila = new PilaVenta();

// 3. Funciones asociadas a los botones HTML
function agregarProducto() {
    const inputNombre = document.getElementById("prod-input");
    const inputPrecio = document.getElementById("precio-input");
    const inputCantidad = document.getElementById("cant-input");

    const nombre = inputNombre.value.trim();
    const precio = parseFloat(inputPrecio.value);
    const cantidad = parseInt(inputCantidad.value);

    if (nombre === "" || isNaN(precio) || isNaN(cantidad) || cantidad <= 0 || precio <= 0) {
        alert("Ingrese datos válidos de producto, precio y cantidad.");
        return;
    }

    // Insertar en la Pila de Nodos
    ventaPila.push(nombre, precio, cantidad);

    // Limpiar campos del formulario
    inputNombre.value = "";
    inputPrecio.value = "";
    inputCantidad.value = "1";
    inputNombre.focus();
}

function deshacerUltimo() {
    ventaPila.pop();
}
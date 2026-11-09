// 1. Nodo: guarda la venta y su número de posición
class NodoVenta {
    constructor(venta, posicion) {
        this.venta = venta;        
        this.posicion = posicion;  // Guarda si es la venta 1, 2, 3...
        this.siguiente = null;    
        this.anterior = null;     
    }
}

// 2. Lista Doblemente Enlazada Circular
class ListaCircularVentas {
    constructor() {
        this.cabeza = null;
        this.actual = null;
        this.totalNodos = 0;
    }

    insertar(venta) {
        this.totalNodos++;
        // Le asignamos su número de posición directamente al nodo
        const nuevoNodo = new NodoVenta(venta, this.totalNodos);

        if (this.cabeza === null) {
            this.cabeza = nuevoNodo;
            this.cabeza.siguiente = this.cabeza;
            this.cabeza.anterior = this.cabeza;
            this.actual = this.cabeza;
        } else {
            const ultimo = this.cabeza.anterior;
            ultimo.siguiente = nuevoNodo;
            nuevoNodo.anterior = ultimo;
            nuevoNodo.siguiente = this.cabeza;
            this.cabeza.anterior = nuevoNodo;
        }
    }

    // Mover puntero al siguiente nodo
    avanzar() {
        if (this.actual) {
            this.actual = this.actual.siguiente;
        }
    }

    // Mover puntero al nodo anterior
    retroceder() {
        if (this.actual) {
            this.actual = this.actual.anterior;
        }
    }
}

// Instancia global
const listaVentas = new ListaCircularVentas();

document.addEventListener("DOMContentLoaded", () => {
    obtenerVentas();

    document.getElementById("btn-anterior").addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.retroceder();
            renderizarTarjeta();
        }
    });

    document.getElementById("btn-siguiente").addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.avanzar();
            renderizarTarjeta();
        }
    });
});

async function obtenerVentas() {
    try {
        const respuesta = await fetch("../php/api_ventas_detalle.php");
        const datos = await respuesta.json();

        if (datos.status === "success" && datos.data.length > 0) {
            datos.data.forEach(venta => listaVentas.insertar(venta));
            renderizarTarjeta();
        } else {
            mostrarMensajeVacio();
        }
    } catch (error) {
        console.error("Error al cargar ventas:", error);
        document.getElementById("venta-folio").textContent = "Error de conexión";
    }
}

function renderizarTarjeta() {
    const nodoActual = listaVentas.actual;
    if (!nodoActual) return;

    const venta = nodoActual.venta;

    document.getElementById("venta-folio").textContent = `Venta #${venta.id_venta}`;
    document.getElementById("venta-fecha").textContent = venta.fecha;
    document.getElementById("venta-cajero").textContent = venta.cajero;

    const tbody = document.getElementById("venta-productos-cuerpo");
    tbody.innerHTML = "";

    venta.productos.forEach(prod => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${escapeHTML(prod.producto)}</td>
            <td>${prod.cantidad}</td>
            <td>$${prod.precio_unitario.toFixed(2)}</td>
            <td>$${prod.subtotal.toFixed(2)}</td>
        `;
        tbody.appendChild(fila);
    });

    // Leemos la posición directamente del nodo sin fórmulas
    document.getElementById("indicador-posicion").textContent = 
        `Venta ${nodoActual.posicion} de ${listaVentas.totalNodos}`;
    document.getElementById("venta-total").textContent = `$${venta.total.toFixed(2)}`;
}

function mostrarMensajeVacio() {
    document.getElementById("venta-folio").textContent = "Sin Ventas";
    document.getElementById("venta-fecha").textContent = "--/--/----";
    document.getElementById("venta-cajero").textContent = "N/A";
    document.getElementById("venta-productos-cuerpo").innerHTML = `
        <tr><td colspan="4" style="text-align:center;">No hay ventas registradas.</td></tr>
    `;
    document.getElementById("indicador-posicion").textContent = "Venta 0 de 0";
    document.getElementById("venta-total").textContent = "$0.00";
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}
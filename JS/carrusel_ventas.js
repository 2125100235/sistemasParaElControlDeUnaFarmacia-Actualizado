// 1. Definición del Nodo
class NodoVenta {
    constructor(venta) {
        this.venta = venta;        // Guarda los datos del JSON de la venta
        this.siguiente = null;    // Puntero al siguiente nodo
        this.anterior = null;     // Puntero al nodo anterior
    }
}

// 2. Definición de la Lista Doblemente Enlazada Circular
class ListaCircularVentas {
    constructor() {
        this.cabeza = null;         // Apunta al primer nodo de la lista
        this.actual = null;         // Apunta al nodo que se está mostrando actualmente
        this.totalNodos = 0;        // Contador total de nodos
        this.indiceActual = 0;      // Posición numérica para el indicador gráfico
    }

    // Insertar un nuevo nodo al final de la lista circular
    insertar(venta) {
        const nuevoNodo = new NodoVenta(venta);

        if (this.cabeza === null) {
            // Caso 1: La lista está vacía. El nodo se apunta a sí mismo
            this.cabeza = nuevoNodo;
            this.cabeza.siguiente = this.cabeza;
            this.cabeza.anterior = this.cabeza;
            this.actual = this.cabeza;
            this.indiceActual = 1;
        } else {
            // Caso 2: Ya existen nodos. Insertamos al final y reajustamos punteros
            const ultimo = this.cabeza.anterior; // El anterior a la cabeza siempre es el último

            ultimo.siguiente = nuevoNodo;
            nuevoNodo.anterior = ultimo;
            nuevoNodo.siguiente = this.cabeza;
            this.cabeza.anterior = nuevoNodo;
        }

        this.totalNodos++;
    }

    // Avanzar al siguiente nodo (Puntero -> siguiente)
    avanzar() {
        if (!this.actual) return;
        this.actual = this.actual.siguiente;
        
        // Actualizar número de posición
        this.indiceActual = (this.indiceActual % this.totalNodos) + 1;
    }

    // Retroceder al nodo anterior (Puntero -> anterior)
    retroceder() {
        if (!this.actual) return;
        this.actual = this.actual.anterior;
        
        // Actualizar número de posición
        this.indiceActual = (this.indiceActual - 2 + this.totalNodos) % this.totalNodos + 1;
    }
}

// Instancia global de nuestra lista circular
const listaVentas = new ListaCircularVentas();

// Inicialización de eventos al cargar el documento
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

/**
 * Petición a la API y conversión del JSON en Nodos de la Lista
 */
async function obtenerVentas() {
    try {
        const respuesta = await fetch("../php/api_ventas_detalle.php");
        const datos = await respuesta.json();

        if (datos.status === "success" && datos.data.length > 0) {
            // Convertimos cada objeto en un nodo de nuestra Lista Enlazada
            datos.data.forEach(venta => {
                listaVentas.insertar(venta);
            });

            renderizarTarjeta();
        } else {
            mostrarMensajeVacio();
        }
    } catch (error) {
        console.error("Error al cargar ventas:", error);
        document.getElementById("venta-folio").textContent = "Error de conexión";
    }
}

/**
 * Muestra en pantalla el contenido del nodo apuntado por 'actual'
 */
function renderizarTarjeta() {
    const nodoActual = listaVentas.actual;
    if (!nodoActual) return;

    const venta = nodoActual.venta;

    // Encabezado
    document.getElementById("venta-folio").textContent = `Venta #${venta.id_venta}`;
    document.getElementById("venta-fecha").textContent = venta.fecha;
    document.getElementById("venta-cajero").textContent = venta.cajero;

    // Tabla de productos
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

    // Indicador y Total
    document.getElementById("indicador-posicion").textContent = 
        `Venta ${listaVentas.indiceActual} de ${listaVentas.totalNodos}`;
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
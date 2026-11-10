// 1. Clase NodoVenta: guarda la información de una sola venta y su posición
class NodoVenta {
    constructor(venta, posicion) {
        this.venta = venta;        // Objeto con id, fecha, cajero, total y productos
        this.posicion = posicion;  // Número de tarjeta (1, 2, 3...)
        this.siguiente = null;     // Enlace al siguiente nodo
        this.anterior = null;      // Enlace al nodo anterior
    }
}

// 2. Clase ListaCircularVentas: lista doblemente enlazada circular
class ListaCircularVentas {
    constructor() {
        this.cabeza = null;     // Primer nodo guardado
        this.actual = null;     // Nodo visible en el carrusel
        this.totalNodos = 0;    // Contador total de ventas
    }

    // Inserta un nodo y mantiene la estructura circular
    insertar(venta) {
        this.totalNodos++;
        const nuevoNodo = new NodoVenta(venta, this.totalNodos);

        if (this.cabeza === null) {
            this.cabeza = nuevoNodo;
            this.cabeza.siguiente = nuevoNodo;
            this.cabeza.anterior = nuevoNodo;
            this.actual = nuevoNodo;
        } else {
            const ultimo = this.cabeza.anterior;
            ultimo.siguiente = nuevoNodo;
            nuevoNodo.anterior = ultimo;
            nuevoNodo.siguiente = this.cabeza;
            this.cabeza.anterior = nuevoNodo;
        }
    }

    // Avanza a la siguiente venta en el carrusel
    avanzar() {
        if (this.actual) {
            this.actual = this.actual.siguiente;
        }
    }

    // Retrocede a la venta anterior en el carrusel
    retroceder() {
        if (this.actual) {
            this.actual = this.actual.anterior;
        }
    }

    // Limpia la lista antes de cargar un nuevo filtro
    limpiar() {
        this.cabeza = null;
        this.actual = null;
        this.totalNodos = 0;
    }
}

// Instancia global de la lista circular
const listaVentas = new ListaCircularVentas();

// Se ejecuta al cargar por completo la página web
document.addEventListener("DOMContentLoaded", () => {
    // Carga inicial automatica con las ventas de hoy
    obtenerVentas("hoy");

    // Flecha izquierda del carrusel
    document.getElementById("btn-anterior")?.addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.retroceder();
            renderizarTarjeta();
        }
    });

    // Flecha derecha del carrusel
    document.getElementById("btn-siguiente")?.addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.avanzar();
            renderizarTarjeta();
        }
    });

    // Menú flotante (Hamburguesa)
    const btnHamburguesa = document.getElementById("btn-hamburguesa");
    const dropdownMenu = document.getElementById("dropdown-ventas");

    if (btnHamburguesa && dropdownMenu) {
        // Abre y cierra el menú al hacer clic en ☰
        btnHamburguesa.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle("mostrar");
        });

        // Cierra el menú al hacer clic fuera de él
        document.addEventListener("click", (e) => {
            if (!dropdownMenu.contains(e.target) && e.target !== btnHamburguesa) {
                dropdownMenu.classList.remove("mostrar");
            }
        });

        // Escucha los botones rápidos (Hoy, Esta semana, Este mes)
        document.querySelectorAll(".opcion-filtro").forEach(boton => {
            boton.addEventListener("click", (e) => {
                const tipo = e.target.getAttribute("data-tipo");
                const titulos = {
                    'hoy': 'Ventas del día',
                    'semana_actual': 'Ventas de esta semana',
                    'mes_actual': 'Ventas de este mes'
                };

                actualizarVista(tipo, "", titulos[tipo] || "Ventas");
            });
        });

        // Escucha el selector de día específico
        document.getElementById("filtro-dia")?.addEventListener("change", (e) => {
            const fecha = e.target.value;
            if (fecha) {
                actualizarVista("dia", fecha, `Ventas del día (${formatearFecha(fecha)})`);
            }
        });

        // Escucha el selector de semana
        document.getElementById("filtro-semana")?.addEventListener("change", (e) => {
            const fecha = e.target.value;
            if (fecha) {
                actualizarVista("semana", fecha, `Ventas de la semana del ${formatearFecha(fecha)}`);
            }
        });

        // Escucha el selector de mes
        document.getElementById("filtro-mes")?.addEventListener("change", (e) => {
            const mesAnio = e.target.value;
            if (mesAnio) {
                actualizarVista("mes", mesAnio, `Ventas de ${formatearMes(mesAnio)}`);
            }
        });
    }
});

// Reinicia la estructura y solicita datos del nuevo filtro
function actualizarVista(tipo, fecha, titulo) {
    const tituloElem = document.getElementById("titulo-tabla-ventas");
    if (tituloElem) tituloElem.textContent = titulo;

    listaVentas.limpiar();
    obtenerVentas(tipo, fecha);
    document.getElementById("dropdown-ventas")?.classList.remove("mostrar");
}

// Pide las ventas a PHP pasándole tipo y fecha
async function obtenerVentas(tipo = "hoy", fecha = "") {
    try {
        const respuesta = await fetch(`../php/api_ventas_detalle.php?tipo=${tipo}&fecha=${fecha}`);
        const datos = await respuesta.json();

        if (datos.status === "success" && datos.data.length > 0) {
            // Guarda cada venta en la lista circular
            datos.data.forEach(venta => listaVentas.insertar(venta));
            
            // 1. Actualiza la TABLA SUPERIOR con TODAS las ventas obtenidas
            renderizarTablaSuperior(datos.data);

            // 2. Actualiza la TARJETA DEL CARRUSEL con la primera venta activa
            renderizarTarjeta();
        } else {
            // Si no hay resultados, limpia ambas vistas
            renderizarTablaSuperior([]);
            mostrarMensajeVacio();
        }
    } catch (error) {
        console.error("Error al cargar ventas:", error);
        document.getElementById("venta-folio").textContent = "Error de conexión";
    }
}

// Dibuja TODAS las ventas devueltas en la tabla general de arriba
function renderizarTablaSuperior(ventas) {
    const tbody = document.getElementById("tabla-ventas-cuerpo");
    if (!tbody) return;

    tbody.innerHTML = ""; // Borra las filas viejas

    // Si no existen ventas para el filtro seleccionado
    if (!ventas || ventas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: #777; padding: 15px;">
                    No se encontraron ventas para esta consulta.
                </td>
            </tr>
        `;
        return;
    }

    // Recorre cada venta y escribe una fila por cada producto registrado
    ventas.forEach(venta => {
        venta.productos.forEach(prod => {
            const fila = document.createElement("tr");
            fila.innerHTML = `
                <td>#${venta.id_venta}</td>
                <td>${escapeHTML(venta.cajero)}</td>
                <td>${escapeHTML(prod.producto)}</td>
                <td>${prod.cantidad}</td>
                <td>$${prod.precio_unitario.toFixed(2)}</td>
                <td>$${prod.subtotal.toFixed(2)}</td>
            `;
            tbody.appendChild(fila);
        });
    });
}

// Dibuja la venta activa dentro de la tarjeta grande del carrusel
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

    document.getElementById("indicador-posicion").textContent = 
        `Venta ${nodoActual.posicion} de ${listaVentas.totalNodos}`;
    document.getElementById("venta-total").textContent = `$${venta.total.toFixed(2)}`;
}

// Muestra estructura sin datos en el carrusel
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

// Convierte 'AAAA-MM-DD' a 'DD/MM/AAAA'
function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

// Convierte 'AAAA-MM' a 'NombreMes AAAA'
function formatearMes(mesAnioStr) {
    const [anio, mes] = mesAnioStr.split("-");
    const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    return `${meses[parseInt(mes, 10) - 1]} ${anio}`;
}

// Limpia caracteres especiales para evitar inyecciones XSS
function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}
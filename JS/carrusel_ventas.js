// 1. Clase NodoVenta: guarda la información de una venta
class NodoVenta {
    constructor(venta, posicion) {
        this.venta = venta;        // Guarda los datos de la venta
        this.posicion = posicion;  // Número de tarjeta (1, 2, 3...)
        this.siguiente = null;     // Apunta a la siguiente tarjeta
        this.anterior = null;      // Apunta a la tarjeta anterior
    }
}

// 2. Clase ListaCircularVentas: lista circular doble
class ListaCircularVentas {
    constructor() {
        this.cabeza = null;     // Primera tarjeta cargada
        this.actual = null;     // Tarjeta activa en el modal
        this.totalNodos = 0;    // Contador total de ventas
    }

    // Inserta una nueva venta al final de la lista
    insertar(venta) {
        this.totalNodos++; // Suma 1 al total de tarjetas
        const nuevoNodo = new NodoVenta(venta, this.totalNodos); // Crea el nodo

        if (this.cabeza === null) {
            this.cabeza = nuevoNodo;           // Asigna la cabeza
            this.cabeza.siguiente = nuevoNodo; // Conecta a sí mismo adelante
            this.cabeza.anterior = nuevoNodo;  // Conecta a sí mismo atrás
            this.actual = nuevoNodo;          // Marca como nodo activo
        } else {
            const ultimo = this.cabeza.anterior; // Encuentra la última tarjeta
            ultimo.siguiente = nuevoNodo;        // Apunta a la nueva tarjeta
            nuevoNodo.anterior = ultimo;         // La nueva apunta a la última
            nuevoNodo.siguiente = this.cabeza;   // La nueva apunta a la cabeza
            this.cabeza.anterior = nuevoNodo;    // La cabeza apunta a la nueva
        }
    }

    // Busca una venta por ID y la activa
    buscarPorId(idVenta) {
        if (!this.cabeza) return false; // Si está vacía sale
        
        let auxiliar = this.cabeza; // Inicia la búsqueda desde la cabeza
        do {
            if (auxiliar.venta.id_venta === idVenta) {
                this.actual = auxiliar; // Activa la venta encontrada
                return true; // Confirma el hallazgo
            }
            auxiliar = auxiliar.siguiente; // Avanza a la siguiente
        } while (auxiliar !== this.cabeza); // Se detiene al dar la vuelta

        return false; // Retorna falso si no la halló
    }

    // Avanza a la siguiente tarjeta
    avanzar() {
        if (this.actual) {
            this.actual = this.actual.siguiente; // Pasa a la tarjeta derecha
        }
    }

    // Retrocede a la tarjeta anterior
    retroceder() {
        if (this.actual) {
            this.actual = this.actual.anterior; // Pasa a la tarjeta izquierda
        }
    }

    // Limpia todas las tarjetas guardadas
    limpiar() {
        this.cabeza = null;     // Quita la cabeza
        this.actual = null;     // Quita el nodo activo
        this.totalNodos = 0;    // Reinicia el contador
    }
}

// Crea la instancia global de la lista circular
const listaVentas = new ListaCircularVentas();

// Se ejecuta al cargar la página completa
document.addEventListener("DOMContentLoaded", () => {
    // Carga las ventas iniciales sin abrir el modal
    obtenerVentas("hoy");

    // Click en la flecha izquierda del modal
    document.getElementById("btn-anterior")?.addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.retroceder(); // Retrocede un nodo
            renderizarTarjeta();     // Muestra la tarjeta en pantalla
        }
    });

    // Click en la flecha derecha del modal
    document.getElementById("btn-siguiente")?.addEventListener("click", () => {
        if (listaVentas.totalNodos > 0) {
            listaVentas.avanzar(); // Avanza un nodo
            renderizarTarjeta();   // Muestra la tarjeta en pantalla
        }
    });

    // Click en el botón de cerrar ✕
    document.getElementById("btn-cerrar-carrusel")?.addEventListener("click", () => {
        cerrarModalDetalle(); // Oculta el modal centrado
    });

    // Cerrar modal al dar clic en el fondo borroso (fuera de la tarjeta)
    const modalSeccion = document.querySelector(".carrusel-seccion");
    modalSeccion?.addEventListener("click", (e) => {
        // Si el clic fue directamente en el fondo borroso y no dentro de la tarjeta
        if (e.target === modalSeccion) {
            cerrarModalDetalle();
        }
    });

    // Elementos del menú flotante de hamburguesa
    const btnHamburguesa = document.getElementById("btn-hamburguesa");
    const dropdownMenu = document.getElementById("dropdown-ventas");

    if (btnHamburguesa && dropdownMenu) {
        // Abrir/cerrar menú al dar clic en ☰
        btnHamburguesa.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle("mostrar");
        });

        // Cerrar menú al dar clic fuera de él
        document.addEventListener("click", (e) => {
            if (!dropdownMenu.contains(e.target) && e.target !== btnHamburguesa) {
                dropdownMenu.classList.remove("mostrar");
            }
        });

        // Eventos para filtros rápidos
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

        // Evento para día específico
        document.getElementById("filtro-dia")?.addEventListener("change", (e) => {
            const fecha = e.target.value;
            if (fecha) {
                actualizarVista("dia", fecha, `Ventas del día (${formatearFecha(fecha)})`);
            }
        });

        // Evento para semana de una fecha
        document.getElementById("filtro-semana")?.addEventListener("change", (e) => {
            const fecha = e.target.value;
            if (fecha) {
                actualizarVista("semana", fecha, `Ventas de la semana del ${formatearFecha(fecha)}`);
            }
        });

        // Evento para mes completo
        document.getElementById("filtro-mes")?.addEventListener("change", (e) => {
            const mesAnio = e.target.value;
            if (mesAnio) {
                actualizarVista("mes", mesAnio, `Ventas de ${formatearMes(mesAnio)}`);
            }
        });
    }
});

// Función para ocultar el modal borroso
function cerrarModalDetalle() {
    const carruselSec = document.querySelector(".carrusel-seccion");
    if (carruselSec) {
        carruselSec.classList.add("oculto"); // Agrega la clase CSS que oculta todo el modal
    }
}

// Actualiza el filtro, limpia nodos y mantiene el modal oculto
function actualizarVista(tipo, fecha, titulo) {
    const tituloElem = document.getElementById("titulo-tabla-ventas");
    if (tituloElem) tituloElem.textContent = titulo;

    cerrarModalDetalle();        // Asegura que el modal siga oculto
    listaVentas.limpiar();       // Borra datos viejos
    obtenerVentas(tipo, fecha);  // Pide nuevos datos a PHP
    document.getElementById("dropdown-ventas")?.classList.remove("mostrar");
}

// Consulta las ventas a PHP sin mostrar el modal automáticamente
async function obtenerVentas(tipo = "hoy", fecha = "") {
    try {
        const respuesta = await fetch(`../php/api_ventas_detalle.php?tipo=${tipo}&fecha=${fecha}`);
        const datos = await respuesta.json();

        // Si hay ventas registradas en la respuesta
        if (datos.status === "success" && datos.data.length > 0) {
            datos.data.forEach(venta => listaVentas.insertar(venta)); // Llena la lista circular
            renderizarTablaSuperior(datos.data);                     // Dibuja la tabla principal
        } else {
            renderizarTablaSuperior([]); // Muestra aviso de tabla vacía
            mostrarMensajeVacio();       // Prepara el modal con vista limpia
        }
    } catch (error) {
        console.error("Error al cargar ventas:", error);
    }
}

// Dibuja la tabla superior y asigna el evento para abrir el modal al dar clic
function renderizarTablaSuperior(ventas) {
    const tbody = document.getElementById("tabla-ventas-cuerpo");
    if (!tbody) return;

    tbody.innerHTML = ""; // Limpia las filas viejas

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

    // Genera las filas de la tabla
    ventas.forEach(venta => {
        venta.productos.forEach(prod => {
            const fila = document.createElement("tr");
            fila.setAttribute("data-id-venta", venta.id_venta);
            fila.title = "Haz clic para ver el detalle de esta venta";
            
            fila.innerHTML = `
                <td>#${venta.id_venta}</td>
                <td>${escapeHTML(venta.cajero)}</td>
                <td>${escapeHTML(prod.producto)}</td>
                <td>${prod.cantidad}</td>
                <td>$${prod.precio_unitario.toFixed(2)}</td>
                <td>$${prod.subtotal.toFixed(2)}</td>
            `;

            // AL DAR CLIC EN CUALQUIER FILA DE LA TABLA:
            fila.addEventListener("click", () => {
                const idVenta = parseInt(fila.getAttribute("data-id-venta"));
                
                // Mueve la lista circular a la venta elegida
                if (listaVentas.buscarPorId(idVenta)) {
                    renderizarTarjeta(); // Dibuja la información en la tarjeta
                    
                    // MUESTRA EL MODAL CON FONDO BORROSO CENTRADO
                    const carruselSec = document.querySelector(".carrusel-seccion");
                    if (carruselSec) {
                        carruselSec.classList.remove("oculto"); // Activa la vista flotante
                    }
                }
            });

            tbody.appendChild(fila);
        });
    });
}

// Dibuja la información de la venta activa dentro del modal
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

// Limpia los textos si no hay datos
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

// Formatos auxiliares
function formatearFecha(fechaStr) {
    const partes = fechaStr.split("-");
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function formatearMes(mesAnioStr) {
    const [anio, mes] = mesAnioStr.split("-");
    const meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    return `${meses[parseInt(mes, 10) - 1]} ${anio}`;
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
    );
}
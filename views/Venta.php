<?php 
// Requiere de la conexión a la base de datos
require_once "../config/conexion.php"; 
// Realiza la consulta a la base de datos MySQL (excluye sin stock y caducados)
$consulta = "SELECT codigo, nombre, precio_venta, existencia, fecha_caducidad 
             FROM producto 
             WHERE estado = 1 
               AND existencia > 0 
               AND (fecha_caducidad > CURDATE() OR fecha_caducidad IS NULL)";
// Ejecutamos la consulta
$resultado = $conexion->query($consulta);
// Verifica si el resultado tiene un valor valido
$productos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

// Consulta de clientes registrados y activos en la base de datos
$consultaClientes = "SELECT codigo, nombre, telefono, rfc FROM cliente WHERE estado = 1 ORDER BY nombre ASC";
$resultadoClientes = $conexion->query($consultaClientes);
$clientes = $resultadoClientes ? $resultadoClientes->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta - FarmaVive</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <a href="../index.php" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo" width="60">
        </a>
        <div class="puntoVenta">
            <div>
                <label for="productoInput">Producto (Nombre o Código)</label>
                <input type="text" id="productoInput" placeholder="Ej. 1 o Paracetamol" oninput="buscarProductoCatalogo()">
            </div>
            <div>
                <label for="precioInput">Precio</label>
                <input type="number" step="0.01" id="precioInput" readonly placeholder="0.00">
            </div>
            <div>
                <label for="cantidadInput">Cantidad</label>
                <input type="number" id="cantidadInput" value="1" min="1">
            </div>
            <button id="registrarBtn" type="button" onclick="agregarProducto()">Registrar</button>
            <button id="deshacerBtn" type="button" onclick="deshacerUltimo()">Deshacer</button>
        </div>
        <div class="menuToggle">
            <button id="menuToggleBtn">☰</button>
        </div>
    </header>

    <main>
        <?php include "partials/menu.php"; ?>
        <section class="contenido">
            <div class="contenidoMensaje">
                <p id="mensaje-busqueda"></p>
            </div>

            <div class="recuadro recuadroVenta">
                <div class="tabla-venta-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaVenta">
                        </tbody>
                    </table>
                </div>

                <div class="footer-venta">
                    <div class="totales">
                        <p><span>Subtotal</span> <span>$<span id="subtotal-val">0.00</span></span></p>
                        <p><span>Total c/IVA</span> <span>$<span id="total-val">0.00</span></span></p>
                    </div>

                    <div class="campo-cliente">
                        <label for="clienteInput">Cliente</label>
                        <input type="text" id="clienteInput" value="Público en general" readonly onclick="abrirModalClienteVenta()">
                        <input type="hidden" id="clienteCodigoInput" value="">
                        <button type="button" id="btnAgregarClienteVenta" class="btn-agregar-cliente" onclick="abrirModalClienteVenta()">Agregar cliente</button>
                    </div>

                    <button id="cobrarBtn" type="button" onclick="finalizarVenta()">Registrar venta</button>
                </div>
            </div>
        </section>
    </main>

    <!-- MODAL PARA BUSCAR Y SELECCIONAR CLIENTE REGISTRADO -->
    <div id="modal-seleccionar-cliente" class="modal-overlay hidden">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" onclick="cerrarModalClienteVenta()">×</button>
            <h2>Seleccionar Cliente Registrado</h2>

            <div class="campo-form campo-full">
                <label for="buscarClienteVentaInput">Buscar cliente (nombre, código, teléfono o RFC):</label>
                <input type="text" id="buscarClienteVentaInput" placeholder="Escribe para filtrar clientes..." oninput="filtrarClientesModal()">
            </div>

            <div class="tabla-clientes-wrapper" style="max-height: 250px; overflow-y: auto; margin-top: 10px;">
                <table class="tabla-clientes-mockup">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>RFC</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes-venta-cuerpo">
                    </tbody>
                </table>
            </div>

            <div class="modal-acciones campo-full" style="margin-top: 15px;">
                <button type="button" class="btn-cancelar" onclick="seleccionarPublicoGeneral()">Público en general</button>
                <button type="button" class="btn-cancelar" onclick="cerrarModalClienteVenta()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Pasar las variables de BD a JavaScript -->
    <script>
        const productosDesdeBD = <?php echo json_encode($productos); ?>;
        const clientesDesdeBD = <?php echo json_encode($clientes); ?>;
    </script>
    <script src="../js/venta.js"></script>
</body>
</html>
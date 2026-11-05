<?php 
// Requiere de la conexión a la base de datos
require_once "../php/conexion.php"; 
// Realiza la consulta a la base de datos MySQL
$consulta = "SELECT codigo, nombre, precio_venta, existencia FROM producto WHERE estado = 1";
// Ejecutamos la consulta
$resultado = $conexion->query($consulta);
// Verifica si el resultado tiene un valor valido (es decir, que la consulta haya sido exitosa)
$productos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
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
    <?php include "../php/menu.php"; ?>
        <section class="contenido">
            <div class="contenidoMensaje">
                <p id="mensaje-busqueda" style="font-family: Asimovian; font-weight: lighter; color: #11698E; margin-bottom: 10px;"></p>
            </div>
            <div class="recuadro recuadroVenta">
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

                <div class="totales">
                    <p>Subtotal $<span id="subtotal-val">0.00</span></p>
                    <p>Total c/IVA $<span id="total-val">0.00</span></p>
                </div>
            </div>
        </section>
    </main>

    <!-- Pasar la variable $productos correcta a JavaScript -->
    <script>
        const productosDesdeBD = <?php echo json_encode($productos); ?>;
    </script>
    <script src="../js/venta.js"></script>
</body>
</html>
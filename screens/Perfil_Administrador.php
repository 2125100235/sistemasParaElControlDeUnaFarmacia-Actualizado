<?php 
// Conectamos con la base de datos
require_once "../php/conexion.php"; 

// Guardamos en la variable queryVentasDia la consulta sql.
$queryVentasDia = "
    SELECT 
        v.numero_nota, 
        CONCAT(e.nombre, ' ', e.apellido_paterno) AS atendio,
        p.nombre AS producto,
        dv.cantidad_salida AS piezas_vendidas,
        dv.precio_venta,
        dv.subtotal AS precio_total
    FROM venta v
    INNER JOIN empleado e ON v.id_empleado = e.id_empleado
    INNER JOIN detalle_venta dv ON v.numero_nota = dv.numero_nota
    INNER JOIN producto p ON dv.codigo_producto = p.codigo
    WHERE DATE(v.fecha) = CURDATE()
    ORDER BY v.numero_nota DESC
";

$resultadoVentas = $conexion->query($queryVentasDia);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil-Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../php/header.php" ?>
    <main>
    <?php include "../php/menu.php" ?>
    <section class="contenido">
        
        <!-- Tabla Venta del día -->
        <div class="recuadro ventaDia">
            <h1>Venta del día</h1>
            <table>
                <thead>
                    <tr>
                        <th>Nota</th>
                        <th>Atendió</th>
                        <th>Nombre del producto</th>
                        <th>Piezas Vendidas</th>
                        <th>Precio Unitario</th>
                        <th>Precio Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultadoVentas && $resultadoVentas->num_rows > 0): ?>
                        <?php while ($fila = $resultadoVentas->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $fila['numero_nota']; ?></td>
                                <td><?php echo htmlspecialchars($fila['atendio']); ?></td>
                                <td><?php echo htmlspecialchars($fila['producto']); ?></td>
                                <td><?php echo $fila['piezas_vendidas']; ?></td>
                                <td>$<?php echo number_format($fila['precio_venta'], 2); ?></td>
                                <td>$<?php echo number_format($fila['precio_total'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777; padding: 15px;">
                                No se han registrado ventas el día de hoy.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Carrusel Circular de Ventas -->
        <div class="recuadro carrusel-seccion">
            <h2>Detalle General de Ventas</h2>
            
            <div class="carrusel-ventas-container">
                <button id="btn-anterior" class="btn-carrusel" title="Venta anterior">◀</button>

                <!-- Tarjeta Grande de Detalle -->
                <div id="tarjeta-venta-grande" class="tarjeta-venta">
                    <div class="tarjeta-header">
                        <h3 id="venta-folio">Venta #--</h3>
                        <span id="venta-fecha" class="badge-fecha">--/--/---- --:--</span>
                    </div>

                    <div class="tarjeta-body">
                        <p class="cajero-info">
                            <strong>Atendido por:</strong> <span id="venta-cajero">--</span>
                        </p>

                        <h4>Productos comprados:</h4>
                        <div class="tabla-scroll">
                            <table class="tabla-productos-detalle">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cant.</th>
                                        <th>P. Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="venta-productos-cuerpo">
                                    <!-- Se llenará dinámicamente con JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tarjeta-footer">
                        <span id="indicador-posicion" class="badge-posicion">Venta 0 de 0</span>
                        <div class="total-box">
                            <span>Total:</span>
                            <strong id="venta-total">$0.00</strong>
                        </div>
                    </div>
                </div>

                <button id="btn-siguiente" class="btn-carrusel" title="Siguiente venta">▶</button>
            </div>
        </div>

    </section>
    </main>

    <script src="../js/carrusel_ventas.js"></script>
</body>
</html>
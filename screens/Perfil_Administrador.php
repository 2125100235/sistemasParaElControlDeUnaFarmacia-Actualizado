<?php 
//Conectamos con la base de datos
require_once "../php/conexion.php"; 

// Guardamos en la variable queryVentasDia la consulta sql.
//Guardamos numero de folio, concatenamos el nombre y apellido del cajero en una misma linea
//guardamos el nombre del producto, piezas vendidas, subtotal y total. 
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
//Usamos la tabla venta con apodo "v"
//Inner join lo que hace es juntar informacion de distintas tablas, para mostrar la informacion junta
//INNER JOIN empleado e, asigno el apodo "e"  a la tabla de empleado 
//ON v.id_empleado (donde el id de empleado en la tabla de venta) = e.id_empleado (sea el mismo en la tabla de empleado)

//Concecta cada ticket con su info correspondiente donde el numero de nota sea el mismo
//Toma el codigo de producto del ticket y busca el precio y nombre en la tabla de productos mediante su codigo

//WHERE DATE : Filtra para recuperar unicamente las ventas de la fecha del dia actual (hoy) CURRENT DAY
// ORDER BY: Ordena los resultados para que la venta mas reciente se pomga hasta arriba


// Ejecutamos la consulta en MySQL y la guardamos en la variable resultadoVentas
$resultadoVentas = $conexion->query($queryVentasDia);
?>
<!DOCTYPE html>
<html lang="en">
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
                    <?php if ($resultadoVentas && $resultadoVentas->num_rows > 0): ?> <!--Si la consulta fue exitosa y hay almenos una fila-->
                        <?php while ($fila = $resultadoVentas->fetch_assoc()): ?> <!--Extraemos los registros devueltos en uno en uno dentro de la variable fila-->
                            <tr>
                                <td>#<?php echo $fila['numero_nota']; ?></td> <!--Imprimimos el numero de nota despues del simbolo #-->
                                <td><?php echo htmlspecialchars($fila['atendio']); ?></td>
                                <td><?php echo htmlspecialchars($fila['producto']); ?></td> <!--Imprimimos el cajero y el producto, htmlspecialchars evita que textos con caracteres especiales rompan la estructura html-->
                                <td><?php echo $fila['piezas_vendidas']; ?></td> <!--Imprime la cantidad de piezas vendidas-->
                                <td>$<?php echo number_format($fila['precio_venta'], 2); ?></td>
                                <td>$<?php echo number_format($fila['precio_total'], 2); ?></td> <!--Imprime precio uitario y precio total, el number format 2, asegura que siempre muestre dos decimales "25.00"-->
                            </tr>
                        <?php endwhile; ?> <!--Termina el while-->
                    <?php else: ?> <!--Si no hay al menos una linea en la consulta, muestra un mensaje-->
                        <tr>
                            <td colspan="6" style="text-align: center; color: #777; padding: 15px;">
                                No se han registrado ventas el día de hoy.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    </main>
</body>
</html>
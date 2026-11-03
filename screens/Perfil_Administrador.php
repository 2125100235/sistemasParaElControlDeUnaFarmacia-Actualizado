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
                    <tr>
                        <td><input type="text" class="nota"></td>
                        <td><input type="text" class="atendio"></td>
                        <td><input type="text" class="nombre" ></td>
                        <td><input type="number" class="pzs" ></td>
                        <td><input type="text" class="precioUnitario" ></td>
                        <td><input type="text" class="precioTotal"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    </main>
</body>
</html>
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
        <div>
            <label for="prod-input">Producto</label>
            <input type="text" id="prod-input">
        </div>
        <div>
            <label for="precio-input">Precio</label>
            <input type="number" step="0.01" id="precio-input">
        </div>
        <div>
            <label for="cant-input">Cantidad</label>
            <input type="number" id="cant-input" value="1">
        </div>
        
        <button type="button" onclick="agregarProducto()">Registrar</button>
        <button type="button" onclick="deshacerUltimo()" style="background-color: #d9534f; color: white;">Deshacer</button>
    </header>

    <main>
    <?php include "../php/menu.php"; ?>
        <section class="contenido">
            <div class="recuadro">
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

    <!-- Script con la lógica de nodos de la Pila -->
    <script src="../js/pilaVenta.js"></script>
</body>
</html>
<?php require_once "../php/conexion.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Pantalla Inicio de Almacenista</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="crud">
        <a href="index.html" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo" width="60">
        </a>
        <div class="contenedorBusqueda">
            <input class="inputBusqueda" type="text">
        </div>
        <nav class="nav-links aCrud">
            <a href="">Eliminar</a>
            <a href="">Agregar</a>
            <a href="">Editar</a>
        </nav>
    </header>
    <main>
      <?php include "../php/menu.php"; ?>

      <section class="contenido productos">
        <?php 
        $consulta = "SELECT * FROM producto WHERE estado = 1";
        $resultado = $conexion->query($consulta);
        $productos = $resultado->fetch_all(MYSQLI_ASSOC);
        
        $imagenLocal = "../assets/placeholderFotoUsuario.png"; 
        
        if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <?php 

                $precio = $producto['precio_venta'] ?? 0.00;
            ?>

            <div class="producto">
                <img src="<?php echo $imagenLocal; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                <h2>Nombre: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
                <h2>Precio: $<?php echo number_format($precio, 2); ?></h2>
                <h2>Existencia: <?php echo htmlspecialchars($producto['existencia']); ?></h2>
            </div>

        <?php endforeach; ?>
        <?php else: ?>
            <p>No hay productos disponibles en este momento.</p>
        <?php endif; ?>
      </section>
    </main>
</body>
</html>
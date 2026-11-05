<?php
// Define la ruta base del proyecto dentro de htdocs
$base = "/sistemasParaElControlDeUnaFarmacia-Actualizado/";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia FarmaVive</title>
    <!-- Ruta absoluta para el archivo CSS -->
    <link rel="stylesheet" href="<?php echo $base; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo $base; ?>css/Control_De_Acceso.css">
</head>
<body>
<header>

    <a href="<?php echo $base; ?>index.php" class="logo">
        <img src="<?php echo $base; ?>assets/logo.svg" alt="FarmaVive Logo" width="60">
    </a>

    <nav class="nav-links">

        <a href="<?php echo $base; ?>index.php">Inicio</a>
        <a href="<?php echo $base; ?>screens/Sobre_Nosotros.php">Sobre nosotros</a>
        <a href="<?php echo $base; ?>screens/Contacto.php">Contacto</a>
    </nav>
        <div class="menuToggle">
        <button id="menuToggleBtn">☰</button>
    </div>
</header>
</body>
</html>
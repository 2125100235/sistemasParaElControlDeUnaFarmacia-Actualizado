<?php
require_once "./php/conexion.php";

// Verificar si existe un empleado en sesión
$haySesion = !empty($_SESSION['idEmpleado']);
$nombre    = $_SESSION['nombre'] ?? '';
$rol       = $_SESSION['rol'] ?? '';

// Determinar la URL del botón "Acceder" según el rol de la sesión activa
if ($haySesion) {
    switch (mb_strtolower($rol)) {
        case 'administrador':
        case 'admin':
            $urlAcceso = "./screens/Perfil_Administrador.php";
            break;

        case 'almacenista':
            $urlAcceso = "./screens/Productos_Pantalla_Almacenista.php";
            break;

        case 'cajero':
            $urlAcceso = "./screens/Venta.php";
            break;

        default:
            $urlAcceso = "./screens/Perfil_Administrador.php";
            break;
    }
} else {
    // Si no hay sesión, el botón lleva a la pantalla de login
    $urlAcceso = "./php/Control_De_Acceso.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmacia - FarmaVive</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<?php include "./php/header.php"; ?>

    <section id="contenido">
        
    </section> 

    <footer>
        <?php if (!$haySesion): ?>
            <div>
                <h1 id="bienvenida">Bienvenido(a) al sistema de control de Farmavive</h1>
            </div> 
            <div>
                <a href="<?php echo $urlAcceso; ?>" id="acceder">Acceder</a>
            </div>
        <?php else: ?>
            <div>
                <h1 id="bienvenida">Buen día, <?php echo htmlspecialchars($nombre); ?></h1>
            </div>
            <div>
                <a href="<?php echo $urlAcceso; ?>" id="acceder">Acceder</a>
            </div>
        <?php endif; ?>
    </footer>
</body>
</html>
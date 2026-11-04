<?php
// Iniciar sesión si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que exista un usuario logueado; si no, redirigir al login
if (!isset($_SESSION['idEmpleado']) && !isset($_SESSION['id_empleado'])) {
    header("Location: ../php/Control_De_Acceso.php");
    exit();
}

// Recuperar las variables de la sesión
$idEmpleado = $_SESSION['idEmpleado'] ?? $_SESSION['id_empleado'] ?? 'N/A';
$nombre = $_SESSION['nombre'] ?? '';
$apellidoPaterno = $_SESSION['apellidoPaterno'] ?? '';
$apellidoMaterno = $_SESSION['apellidoMaterno'] ?? '';
$nombreCompleto  = trim("$nombre $apellidoPaterno $apellidoMaterno");

$rol = $_SESSION['rol'] ?? 'Sin rol asignado';
$correo = $_SESSION['correo'] ?? 'No registrado';
$telefono = $_SESSION['telefono'] ?? 'Sin número';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - FarmaVive</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../php/header.php"; ?>

    <main>
<?php include "../php/menu.php"; ?>
        <div class="bannerUsuario">
            <div class="contenidoBannerUsuario">
                <img class="bannerFoto" src="../assets/placeholderFotoUsuario.png" alt="Foto de perfil del usuario">
            </div>
        </div>
        <section class="contenidoPerfil">
                <h1><?php echo htmlspecialchars($nombreCompleto); ?></h1>
                <p><b>ID:</b> #<?php echo htmlspecialchars($idEmpleado); ?></p>
                <p><b>Rol:</b> <?php echo htmlspecialchars(ucfirst($rol)); ?></p>
                <p><b>Correo Electrónico:</b> <?php echo htmlspecialchars($correo); ?></p>
                <p><b>Teléfono:</b> <?php echo htmlspecialchars($telefono); ?></p>
                <a href="../php/cerrar_sesion.php" id="acceder" style="text-align: center; text-decoration: none;">Cerrar Sesión</a>
        </section>
    </main>
</body>
</html>
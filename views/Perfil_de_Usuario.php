<?php
// Iniciar sesión si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que exista un usuario logueado; si no, redirigir al login
if (!isset($_SESSION['id_empleado'])) {
    header("Location: Control_De_Acceso.php");
    exit();
}

// Recuperar las variables de la sesión
$id_empleado     = $_SESSION['id_empleado'] ?? 'N/A';
$nombre          = $_SESSION['nombre'] ?? '';
$apellidoPaterno = $_SESSION['apellidoPaterno'] ?? '';
$apellidoMaterno = $_SESSION['apellidoMaterno'] ?? '';
$nombreCompleto  = trim("$nombre $apellidoPaterno $apellidoMaterno");

$rol      = $_SESSION['rol'] ?? 'Sin rol asignado';
$correo   = $_SESSION['correo'] ?? 'No registrado';
$telefono = $_SESSION['telefono'] ?? 'Sin número';
$foto     = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'fotoDefectoEmpleado.png';
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
<?php include "partials/header.php"; ?>

    <main>
<?php include "partials/menu.php"; ?>
        <div class="bannerUsuario">
            <div class="contenidoBannerUsuario">
                <img class="bannerFoto" 
                     src="../assets/<?php echo htmlspecialchars($foto); ?>" 
                     alt="Foto de perfil del usuario"
                     onerror="this.src='../assets/fotoDefectoEmpleado.png'">

                <form action="../controllers/AuthController.php?accion=actualizar_foto" method="POST" enctype="multipart/form-data" class="form-foto-perfil">
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/png, image/jpeg, image/webp, image/avif" hidden onchange="this.form.submit()">
                    <label for="foto_perfil" class="btn-cambiar-foto">Cambiar foto</label>
                </form>
            </div>
        </div>
        <section class="contenidoPerfil">
            <h1><?php echo htmlspecialchars($nombreCompleto); ?></h1>
            <p><b>ID:</b> #<?php echo htmlspecialchars($id_empleado); ?></p>
            <p><b>Rol:</b> <?php echo htmlspecialchars(ucfirst($rol)); ?></p>
            <p><b>Correo Electrónico:</b> <?php echo htmlspecialchars($correo); ?></p>
            <p><b>Teléfono:</b> <?php echo htmlspecialchars($telefono); ?></p>
            <a href="../controllers/AuthController.php?accion=cerrar" id="acceder" style="text-align: center; text-decoration: none;">Cerrar Sesión</a>
        </section>
    </main>
</body>
</html>
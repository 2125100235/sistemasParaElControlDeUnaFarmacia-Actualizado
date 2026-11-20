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

require_once __DIR__ . '/../config/conexion.php';

$id_empleado     = $_SESSION['id_empleado'];
$nombre          = $_SESSION['nombre'] ?? '';
$apellidoPaterno = $_SESSION['apellidoPaterno'] ?? '';
$apellidoMaterno = $_SESSION['apellidoMaterno'] ?? '';
$nombreCompleto  = trim("$nombre $apellidoPaterno $apellidoMaterno");
$rol             = $_SESSION['rol'] ?? 'Sin rol asignado';
$correo          = $_SESSION['correo'] ?? 'No registrado';
$telefono        = $_SESSION['telefono'] ?? 'Sin número';

// Procesar subida de nueva foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_subir_foto'])) {
    if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] === UPLOAD_ERR_OK) {
        $extension  = strtolower(pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (in_array($extension, $permitidas)) {
            $nombreArchivo = "perfil_" . $id_empleado . "_" . time() . "." . $extension;
            $rutaDestino   = __DIR__ . "/../assets/" . $nombreArchivo;

            if (move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $rutaDestino)) {
                $stmt = $conexion->prepare("UPDATE empleado SET foto = ? WHERE id_empleado = ?");
                $stmt->bind_param("si", $nombreArchivo, $id_empleado);

                if ($stmt->execute()) {
                    $_SESSION['foto'] = $nombreArchivo;
                }
                $stmt->close();
            }
        } else {
            echo "<script>alert('Formato no válido. Usa JPG, PNG, WEBP o AVIF.');</script>";
        }
    }
}

$fotoPerfil = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'fotoDefectoEmpleado.png';
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
                <img class="bannerFoto" src="../assets/<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil del usuario">
            </div>
        </div>
        <section class="contenidoPerfil">
            <h1><?php echo htmlspecialchars($nombreCompleto); ?></h1>
            <p><b>ID:</b> #<?php echo htmlspecialchars($id_empleado); ?></p>
            <p><b>Rol:</b> <?php echo htmlspecialchars(ucfirst($rol)); ?></p>
            <p><b>Correo Electrónico:</b> <?php echo htmlspecialchars($correo); ?></p>
            <p><b>Teléfono:</b> <?php echo htmlspecialchars($telefono); ?></p>

            <form action="" method="POST" enctype="multipart/form-data" class="form-foto-perfil">
                <label for="nueva_foto">Cambiar foto de perfil:</label>
                <input type="file" id="nueva_foto" name="nueva_foto" accept="image/png, image/jpeg, image/webp, image/avif" required>
                <button type="submit" name="btn_subir_foto">Guardar foto</button>
            </form>

            <a href="../controllers/AuthController.php?accion=cerrar" id="acceder">Cerrar Sesión</a>
        </section>
    </main>
</body>
</html>
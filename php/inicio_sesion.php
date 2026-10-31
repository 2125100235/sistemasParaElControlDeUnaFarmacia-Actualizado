<?php
session_start();

// Conexión a MySQL
$host = "localhost";
$user = "root";
$pass = "";
$db   = "farmacia";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los 3 parámetros de la vista
    $id         = trim($_POST['id']);
    $nombre     = trim($_POST['nombre']);
    $clave = trim($_POST['clave']);

    // Consultar el registro exacto por id_empleado y nombre
    $stmt = $conexion->prepare("SELECT * FROM empleado WHERE id_empleado = ? AND nombre = ?");
    $stmt->bind_param("is", $id, $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $empleado = $resultado->fetch_assoc();

        // Validar contraseña (compara texto plano 'admin123' o hash encriptado)
        $claveValida = ($clave === $empleado['clave']) || password_verify($clave, $empleado['clave']);

        if ($claveValida) {
            // Guardar datos en la sesión
            $_SESSION['id_empleado'] = $empleado['id_empleado'];
            $_SESSION['nombre']      = $empleado['nombre'];
            
            // Obtener el rol o puesto asignado
            $rol = $empleado['rol'] ?? $empleado['puesto'] ?? 'Administrador';
            $_SESSION['rol'] = $rol;

            // Redireccionar a la pantalla en /html/ según el rol/puesto
            switch (mb_strtolower($rol)) {
                case 'administrador':
                case 'admin':
                    header("Location: ../screens/Perfil_Administrador.php");
                    break;

                case 'almacenista':
                    header("Location: ../screens/Productos_Pantalla_Almacenista.html");
                    break;

                case 'cajero':
                    header("Location: ../screens/Venta.html");
                    break;

                default:
                    header("Location: ../screens/Perfil_Administrador.html");
                    break;
            }
            exit();

        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='Control_De_Acceso.php';</script>";
        }
    } else {
        echo "<script>alert('El ID o Nombre del empleado no coinciden con ningún registro activo.'); window.location.href='Control_De_Acceso.php';</script>";
    }

    $stmt->close();
}
$conexion->close();
?>
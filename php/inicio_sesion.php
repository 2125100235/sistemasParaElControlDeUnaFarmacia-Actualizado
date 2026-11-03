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
    $id     = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $clave  = trim($_POST['clave']);

    // Consultar el registro exacto por idEmpleado y nombre (compatibilidad camelCase y snake_case)
    $stmt = $conexion->prepare("SELECT * FROM empleado WHERE (idEmpleado = ? OR id_empleado = ?) AND nombre = ?");
    $stmt->bind_param("iis", $id, $id, $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $empleado = $resultado->fetch_assoc();

        // Validar contraseña (compara texto plano o hash encriptado)
        $claveValida = ($clave === $empleado['clave']) || password_verify($clave, $empleado['clave']);

        if ($claveValida) {
            // Guardar TODAS las variables del usuario en $_SESSION
            $_SESSION['idEmpleado']      = $empleado['idEmpleado'] ?? $empleado['id_empleado'];
            $_SESSION['nombre']          = $empleado['nombre'];
            $_SESSION['apellidoPaterno'] = $empleado['apellidoPaterno'] ?? $empleado['apellido_paterno'] ?? '';
            $_SESSION['apellidoMaterno'] = $empleado['apellidoMaterno'] ?? $empleado['apellido_materno'] ?? '';
            $_SESSION['correo']          = $empleado['correo'];
            $_SESSION['telefono']        = $empleado['telefono'] ?? 'Sin registro';
            
            $rol = $empleado['rol'] ?? $empleado['puesto'] ?? 'Administrador';
            $_SESSION['rol'] = $rol;

            // Redireccionar según el rol
            switch (mb_strtolower($rol)) {
                case 'administrador':
                case 'admin':
                    header("Location: ../screens/Perfil_Administrador.php");
                    break;

                case 'almacenista':
                    header("Location: ../screens/Productos_Pantalla_Almacenista.php");
                    break;

                case 'cajero':
                    header("Location: ../screens/Venta.php");
                    break;

                default:
                    header("Location: ../screens/Perfil_Administrador.php");
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
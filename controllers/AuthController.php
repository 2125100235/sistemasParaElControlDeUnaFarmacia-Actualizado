<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new UsuarioModel($conexion);
    }

    // 1. PROCESAR INICIO DE SESIÓN
    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/Control_De_Acceso.php");
            exit();
        }

        $id     = (int) trim($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $clave  = trim($_POST['clave'] ?? '');

        $empleado = $this->modelo->buscarEmpleadoPorIdYNombre($id, $nombre);

        if ($empleado) {
            // Validar contraseña (texto plano o hash encriptado)
            $claveValida = ($clave === $empleado['clave']) || password_verify($clave, $empleado['clave']);

            if ($claveValida) {
                $apPaterno = $empleado['apellido_paterno'] ?? $empleado['apellidoPaterno'] ?? '';
                $apMaterno = $empleado['apellido_materno'] ?? $empleado['apellidoMaterno'] ?? '';

                $_SESSION['id_empleado']      = $empleado['id_empleado'];
                $_SESSION['nombre']           = $empleado['nombre'];
                $_SESSION['apellido_paterno'] = $apPaterno;
                $_SESSION['apellido_materno'] = $apMaterno;
                // Compatibilidad con vistas que lean en camelCase
                $_SESSION['apellidoPaterno']  = $apPaterno;
                $_SESSION['apellidoMaterno']  = $apMaterno;
                $_SESSION['correo']           = $empleado['correo'] ?? '';
                $_SESSION['telefono']         = $empleado['telefono'] ?? 'Sin registro';

                $rol = $empleado['rol'] ?? $empleado['puesto'] ?? 'Administrador';
                $_SESSION['rol'] = $rol;

                // Redireccionar a la vista correspondiente según el rol
                switch (mb_strtolower($rol)) {
                    case 'administrador':
                    case 'admin':
                        header("Location: ../views/Perfil_Administrador.php");
                        break;

                    case 'almacenista':
                        header("Location: ../views/Productos_Pantalla_Almacenista.php");
                        break;

                    case 'cajero':
                        header("Location: ../views/Venta.php");
                        break;

                    default:
                        header("Location: ../views/Perfil_Administrador.php");
                        break;
                }
                exit();

            } else {
                echo "<script>alert('Contraseña incorrecta.'); window.location.href='../views/Control_De_Acceso.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('El ID o Nombre del empleado no coinciden con ningún registro activo.'); window.location.href='../views/Control_De_Acceso.php';</script>";
            exit();
        }
    }

    // 2. PROCESAR CIERRE DE SESIÓN
    public function cerrarSesion() {
        session_unset();
        session_destroy();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Cerrando sesión...</title>
        </head>
        <body>
            <script>
                alert('Ha finalizado la sesión con éxito');
                window.location.href = '../index.php';
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}

// Enrutador de acciones del controlador
$accion = $_REQUEST['accion'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'login' : '');
$auth = new AuthController($conexion);

switch ($accion) {
    case 'login':
        $auth->iniciarSesion();
        break;

    case 'cerrar':
        $auth->cerrarSesion();
        break;
}
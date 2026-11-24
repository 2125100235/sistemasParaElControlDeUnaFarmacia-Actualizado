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

    // 1. PROCESAR INICIO DE SESIÓN (SOLO NOMBRE Y CONTRASEÑA)
    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/Control_De_Acceso.php");
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $clave  = trim($_POST['clave'] ?? '');

        $empleado = $this->modelo->buscarEmpleadoPorNombre($nombre);

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
                $_SESSION['foto']             = !empty($empleado['foto']) ? $empleado['foto'] : 'fotoDefectoEmpleado.png';

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
            echo "<script>alert('El nombre del empleado no coincide con ningún registro activo.'); window.location.href='../views/Control_De_Acceso.php';</script>";
            exit();
        }
    }

    // 2. ACTUALIZAR FOTO DE PERFIL DEL EMPLEADO
    public function actualizarFoto() {
        if (!isset($_SESSION['id_empleado'])) {
            header("Location: ../views/Control_De_Acceso.php");
            exit();
        }

        if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Selecciona una imagen válida.'); window.location.href='../views/Perfil_de_Usuario.php';</script>";
            exit();
        }

        $extension  = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (!in_array($extension, $permitidas)) {
            echo "<script>alert('Formato no permitido. Usa JPG, PNG, WEBP o AVIF.'); window.location.href='../views/Perfil_de_Usuario.php';</script>";
            exit();
        }

        $id_empleado = (int) $_SESSION['id_empleado'];
        $nuevoNombre = "emp_" . $id_empleado . "_" . time() . "." . $extension;
        $rutaDestino = __DIR__ . '/../assets/' . $nuevoNombre;

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaDestino)) {
            if ($this->modelo->actualizarFoto($id_empleado, $nuevoNombre)) {
                $_SESSION['foto'] = $nuevoNombre;
                echo "<script>alert('Foto de perfil actualizada.'); window.location.href='../views/Perfil_de_Usuario.php';</script>";
                exit();
            }
        }

        echo "<script>alert('No se pudo actualizar la foto de perfil.'); window.location.href='../views/Perfil_de_Usuario.php';</script>";
        exit();
    }

    // 3. PROCESAR CIERRE DE SESIÓN
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

    case 'actualizar_foto':
        $auth->actualizarFoto();
        break;

    case 'cerrar':
        $auth->cerrarSesion();
        break;
}
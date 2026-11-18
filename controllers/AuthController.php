<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AuthController {

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

    // AQUÍ AGREGAREMOS DESPUÉS EL MÉTODO iniciarSesion() DE inicio_sesion.php
}

$accion = $_REQUEST['accion'] ?? '';

if ($accion === 'cerrar') {
    $auth = new AuthController();
    $auth->cerrarSesion();
}
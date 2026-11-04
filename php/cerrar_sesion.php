<?php
session_start();
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
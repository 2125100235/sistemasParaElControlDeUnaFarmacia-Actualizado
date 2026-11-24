<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Acceso</title>
    <link rel="stylesheet" href="../css/Control_De_Acceso.css">
</head>
<body>
<?php include "partials/header.php"; ?>

<section id="section_form">
    <form action="../controllers/AuthController.php?accion=login" method="POST">
        <h1>Acceda al sistema</h1>
        <div>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Carlos">
        </div>
        <br>
        <div>
            <label for="clave">Contraseña</label><br>
            <input type="password" id="clave" name="clave" required placeholder="••••••••">
        </div>
        <br>
        <button type="submit" id="acceso">Iniciar sesión</button>
    </form>
</section>
</body>
</html>
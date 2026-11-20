<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "partials/header.php" ?>
<main class="mainContacto">
    <form class="contacto" action="" method="POST">
        <h1>Contacto</h1>

        <div class="campo-contacto">
            <label for="id_empleado">ID de empleado</label>
            <input type="text" id="id_empleado" name="id_empleado" class="input-id-corto">
        </div>

        <div class="campo-contacto">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo">
        </div>

        <div class="campo-contacto">
            <label for="razon">Razón</label>
            <textarea id="razon" name="razon" rows="4"></textarea>
        </div>

        <button type="submit" id="acceso">
            <span id="btn_contacto">Contactar</span>
        </button>
    </form>
</main>

</body>
</html>
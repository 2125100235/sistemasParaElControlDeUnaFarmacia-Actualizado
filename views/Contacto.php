<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "partials/header.php" ?>
<main class="mainContacto">
    <form id="form-contacto" class="contacto" action="../controllers/ContactoController.php" method="POST">
        <h1>Contacto</h1>

        <div class="campo-contacto">
            <label for="id_empleado">ID de empleado</label>
            <input type="text" id="id_empleado" name="id_empleado" class="input-id-corto" required>
        </div>

        <div class="campo-contacto">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" required>
        </div>

        <div class="campo-contacto">
            <label for="razon">Razón</label>
            <textarea id="razon" name="razon" rows="4" required></textarea>
        </div>

        <p id="mensaje-contacto" style="font-family: Onest, sans-serif; font-size: 0.9rem; text-align: center; margin: 0;"></p>

        <button type="submit" id="acceso">
            <span id="btn_contacto">Contactar</span>
        </button>
    </form>
</main>

<script src="../js/contacto.js"></script>
</body>
</html>
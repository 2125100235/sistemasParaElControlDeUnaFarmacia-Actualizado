<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../php/header.php" ?>
<main class="mainContacto">
    <section class="contacto recuadro">
      <form>
        <h1>Contacto</h1>
        <div>
            <label for="Correo">Correo</label><br>
            <input type="text" id="Correo" name="Correo" placeholder="usuario.correo@gmail.com" >
        </div>
        <br>
        <div>
            <label for="Telefono">Teléfono</label><br>
            <input type="text" id="Telefono" name="Telefono" placeholder="33 1177 6655" >
        </div>
        <br>
        <div>
            <label for="Direccion">Dirección</label><br>
            <input type="text" id="Direccion" name="Direccion" placeholder="Av.Casa #321" >
        </div>
        <br>
        <a href="#" id="acceso">Contactar</a>
      </form>
    </section>
</main>

</body>
</html>
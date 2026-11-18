<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte tecnico</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include "../php/header.php" ?>
    <main>
    <?php include "../php/menu.php" ?>
            <section class="contenido">
                <div class="infoSobreNosotros recuadro">
                <h1><b>Control De Acceso</b></h1>
                <p>El acceso al sistema se realiza exclusivamente mediante el ID de empleado y clave asignada. Queda estrictamente prohibido compartir credenciales entre colaboradores. Todo usuario debe cerrar su sesión al ausentarse de la estación de trabajo para evitar transacciones no autorizadas con su identidad.</p>
                </div>
                <div class="infoSobreNosotros recuadro">
                <h1><b>Restricción por roles</b></h1>
               <p>Administrador: Control total de usuarios, asignación de roles y configuraciones globales. Almacenista: Acceso exclusivo a la gestión de inventario, recepción de abastecimientos y directorio de proveedores. Cajero: Acceso limitado a la interfaz de Punto de Venta, registro de clientes y arqueos de caja.</p>
                </div>
                 <div class="infoSobreNosotros recuadro">
                <h1><b>Atención al cliente y almacén</b></h1>
               <p>Los productos caducados o dañados no se eliminan físicamente de la base de datos; se deshabilita su visibilidad cambiando su atributo a inactivo (estado = 0). Toda línea agregada a una nota de venta debe provenir de una coincidencia válida obtenida mediante la búsqueda en el catálogo. No se permiten registros sin clave de producto válida.</p>
                </div>
            </section>
    </main>
</body>
</html>
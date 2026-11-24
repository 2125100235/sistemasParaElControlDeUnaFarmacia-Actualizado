<?php 
// Requerimos la conexión principal a la base de datos MySQL
require_once "../config/conexion.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Proveedores - FarmaVive</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Encabezado superior: Logo + Buscador + Eliminar / Agregar / Editar -->
    <header class="crud">
        <a href="../index.php" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo, volver al inicio" width="60">
        </a>
        
        <div class="contenedorBusqueda">
            <input class="inputBusqueda" id="input-buscar" type="text" placeholder="Buscar proveedor por ID, nombre, teléfono o correo...">
        </div>
        
        <nav class="nav-links aCrud">
            <button type="button" id="btn-header-eliminar" class="btn-header-crud">Eliminar</button>
            <button type="button" id="btn-header-agregar" class="btn-header-crud">Agregar</button>
            <button type="button" id="btn-header-editar" class="btn-header-crud">Editar</button>
        </nav>
    </header>

    <main>
        <!-- Barra lateral del menú existente -->
        <?php include "partials/menu.php"; ?>

        <!-- Contenido principal con el recuadro blanco y las 5 columnas en cajitas -->
        <section class="contenido">
            <div class="recuadro recuadro-clientes">
                <div class="tabla-clientes-wrapper">
                    <table class="tabla-clientes-mockup">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-proveedores-cuerpo">
                            <!-- JavaScript inyectará aquí las filas con celdas estilo caja -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- VENTANA MODAL EMERGENTE PARA CREAR Y EDITAR PROVEEDORES -->
    <div id="modal-proveedor" class="modal-overlay hidden">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" id="btn-cerrar-modal">×</button>
            
            <h2 id="modal-titulo">Agregar Proveedor</h2>
            
            <form id="form-proveedor" class="form-grid-producto">
                <input type="hidden" id="proveedor-id" name="id_proveedor" value="">

                <!-- Nombre del Proveedor o Laboratorio (Ocupa 2 columnas) -->
                <div class="campo-form campo-full">
                    <label for="nombre">Nombre del Proveedor *:</label>
                    <input type="text" id="nombre" name="nombre" maxlength="150" required placeholder="Ej. Distribuidora Farmacéutica del Bajío">
                </div>

                <!-- Teléfono -->
                <div class="campo-form">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" minlength="10" maxlength="10" pattern="[0-9]{10}"  placeholder="10 dígitos (ej. 3336124578)" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                </div>

                <!-- Correo -->
                <div class="campo-form">
                    <label for="correo">Correo Electrónico:</label>
                   <input type="email" id="correo" name="correo" maxlength="100" pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$" placeholder="contacto@proveedor.com">
                </div>

                <!-- Dirección (Ocupa 2 columnas) -->
                <div class="campo-form campo-full">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="255" placeholder="Ej. Calz. Independencia Norte #850, Guadalajara">
                </div>

                <!-- Botones de Acción -->
                <div class="modal-acciones campo-full">
                    <button type="button" class="btn-cancelar" id="btn-cancelar-modal">Cancelar</button>
                    <button type="submit" class="btn-guardar" id="btn-guardar-proveedor">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../JS/proveedores.js"></script>
</body>
</html>
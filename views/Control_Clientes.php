<?php 
// Requerimos la conexión principal a la base de datos MySQL
require_once "../config/conexion.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Clientes - FarmaVive</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Encabezado superior igual al mockup: Logo + Buscador + Eliminar / Agregar / Editar -->
    <header class="crud">
        <a href="../index.php" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo, volver al inicio" width="60">
        </a>
        
        <div class="contenedorBusqueda">
            <input class="inputBusqueda" id="input-buscar" type="text" placeholder="Buscar cliente por nombre, código, teléfono o RFC...">
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
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>RFC</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-clientes-cuerpo">
                            <!-- JavaScript inyectará aquí las filas con celdas estilo caja -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- VENTANA MODAL EMERGENTE PARA CREAR Y EDITAR CLIENTES -->
    <div id="modal-cliente" class="modal-overlay hidden">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" id="btn-cerrar-modal">×</button>
            
            <h2 id="modal-titulo">Agregar Cliente</h2>
            
            <form id="form-cliente" class="form-grid-producto">
                <input type="hidden" id="cliente-codigo" name="codigo" value="">

                <!-- Nombre completo (Ocupa 2 columnas) -->
                <div class="campo-form campo-full">
                    <label for="nombre">Nombre del Cliente *:</label>
                   <input type="text" id="nombre" name="nombre" minlength="2" maxlength="120" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');">

                <!-- Teléfono -->
                <div class="campo-form">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" minlength="10" maxlength="10" pattern="[0-9]{10}" placeholder="10 dígitos" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                </div>

               <!-- RFC: mayúsculas automáticas, 12 o 13 caracteres -->
                <div class="campo-form">
                    <label for="rfc">RFC:</label>
                    <input type="text" id="rfc" name="rfc" minlength="12" maxlength="13" pattern="^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$" placeholder="Ej. XAXX010101000" oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '').slice(0, 13);">
                </div>

                <!-- Dirección (Ocupa 2 columnas) -->
                <div class="campo-form campo-full">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="255" placeholder="Ej. Av. Vallarta #1234, Col. Americana">
                </div>

                <!-- Botones de Acción -->
                <div class="modal-acciones campo-full">
                    <button type="button" class="btn-cancelar" id="btn-cancelar-modal">Cancelar</button>
                    <button type="submit" class="btn-guardar" id="btn-guardar-cliente">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../JS/clientes.js"></script>
</body>
</html>
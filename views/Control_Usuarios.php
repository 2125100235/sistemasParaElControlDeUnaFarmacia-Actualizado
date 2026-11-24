<?php 
// Requerimos la conexión principal a la base de datos MySQL
require_once "../config/conexion.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Usuarios - FarmaVive</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Encabezado superior: Logo + Buscador + Eliminar / Agregar / Editar -->
    <header class="crud">
        <a href="../index.php" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo, volver al inicio" width="60">
        </a>
        
        <div class="contenedorBusqueda">
            <input class="inputBusqueda" id="input-buscar" type="text" placeholder="Buscar usuario por ID, nombre, correo, teléfono o rol...">
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
                                <th>Nombre completo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-usuarios-cuerpo">
                            <!-- JavaScript inyectará aquí las filas con celdas estilo caja -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- VENTANA MODAL EMERGENTE PARA CREAR Y EDITAR USUARIOS -->
    <div id="modal-usuario" class="modal-overlay hidden">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" id="btn-cerrar-modal">×</button>
            
            <h2 id="modal-titulo">Agregar Usuario</h2>
            
            <form id="form-usuario" class="form-grid-producto">
                <input type="hidden" id="usuario-id" name="id_empleado" value="">

                <!-- Nombre -->
                <div class="campo-form">
                    <label for="nombre">Nombre(s) *:</label>
                  <input type="text" id="nombre" name="nombre" minlength="2" maxlength="50" required oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');">
                </div>

                <!-- Apellido Paterno -->
                <div class="campo-form">
                    <label for="apellido_paterno">Apellido Paterno *:</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" maxlength="50" required placeholder="Ej. Vargas" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');">
                </div>

                <!-- Apellido Materno -->
                <div class="campo-form">
                    <label for="apellido_materno">Apellido Materno:</label>
                   <input type="text" id="apellido_materno" name="apellido_materno" maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g, '');">
                </div>

                <!-- Teléfono -->
                <div class="campo-form">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" minlength="10" maxlength="10" pattern="[0-9]{10}" placeholder="10 dígitos (ej. 3312345678)" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                </div>

                <!-- Correo electrónico -->
                <div class="campo-form">
                    <label for="correo">Correo Electrónico *:</label>
                    <input type="email" id="correo" name="correo" maxlength="100" required pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$" placeholder="usuario@farmavive.com">
                </div>

                <!-- Rol (ENUM de la BD) -->
                <div class="campo-form">
                    <label for="rol">Rol *:</label>
                    <select id="rol" name="rol" required>
                        <option value="Cajero">Cajero</option>
                        <option value="Almacenista">Almacenista</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>

                <!--Contraseña: mínimo 6 caracteres -->
                <div class="campo-form campo-full">
                    <label for="clave" id="label-clave">Contraseña *:</label>
                    <input type="password" id="clave" name="clave" minlength="6" maxlength="255" required placeholder="Ingresa una contraseña">
                </div>

                <!-- Botones de Acción -->
                <div class="modal-acciones campo-full">
                    <button type="button" class="btn-cancelar" id="btn-cancelar-modal">Cancelar</button>
                    <button type="submit" class="btn-guardar" id="btn-guardar-usuario">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../JS/usuarios.js"></script>
</body>
</html>
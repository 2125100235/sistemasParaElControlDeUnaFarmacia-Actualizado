<?php 
// Requerimos la conexión principal a la base de datos MySQL
require_once "../php/conexion.php"; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Define la codificación de caracteres en UTF-8 -->
    <meta charset="UTF-8">
    <!-- Ajuste para correcta visualización en dispositivos móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Pantalla Inicio de Almacenista</title>
    <!-- Enlace a la hoja de estilos CSS general -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Encabezado superior con la barra de búsqueda y el botón para agregar -->
    <header class="crud">
        <!-- Logo principal que redirige al menú del sistema -->
        <a href="../index.php" class="logo">
            <img src="../assets/logo.svg" alt="FarmaVive Logo, volver al inicio" width="60">
        </a>
        
        <!-- Contenedor del buscador (JavaScript detectará lo que escribas aquí en tiempo real) -->
        <div class="contenedorBusqueda">
            <input class="inputBusqueda" id="input-buscar" type="text" placeholder="Buscar producto por nombre o código...">
        </div>
        
        <!-- Menú superior con el botón que despliega el formulario flotante para Agregar -->
        <nav class="nav-links aCrud">
            <button type="button" id="btn-abrir-agregar" class="btn-crud btn-agregar">+ Agregar Producto</button>
        </nav>
    </header>

    <main>
      <!-- Incluye la barra lateral del menú existente -->
      <?php include "../php/menu.php"; ?>

      <!-- Área vacía donde JavaScript inyectará las tarjetas de productos desde la BD -->
      <section class="contenido productos" id="contenedor-productos">
          <!-- JavaScript colocará aquí las tarjetas dinámicas sin recargar la página -->
      </section>
    </main>

    <!-- VENTANA MODAL EMERGENTE PARA CREAR Y EDITAR PRODUCTOS (INVISIBLE CON LA CLASE hidden) -->
    <div id="modal-producto" class="modal-overlay hidden">
    <div class="modal-contenido">
        
        <!-- Botón X para cerrar -->
        <button type="button" class="modal-cerrar" id="btn-cerrar-modal">×</button>
        
        <!-- Título -->
        <h2 id="modal-titulo">Agregar Producto</h2>
        
        <!-- Formulario ordenado en cuadrícula de 2 columnas -->
        <form id="form-producto" class="form-grid-producto">
            
            <input type="hidden" id="producto-codigo" name="codigo" value="">

            <!-- Nombre (Ocupa 2 columnas) -->
            <div class="campo-form campo-full">
                <label for="nombre">Nombre del Producto *:</label>
                <input type="text" id="nombre" name="nombre" required placeholder="Ej. Paracetamol 500mg">
            </div>

            <!-- Tipo de Medicamento -->
            <div class="campo-form">
                <label for="tipo_medicamento">Tipo de Medicamento *:</label>
                <select id="tipo_medicamento" name="tipo_medicamento">
                    <option value="Genérico">Genérico</option>
                    <option value="Patente">Patente</option>
                    <option value="Controlado">Controlado</option>
                    <option value="OTC / Libre Venta" selected>OTC / Libre Venta</option>
                    <option value="Material Cuidado Personal">Material Cuidado Personal</option>
                </select>
            </div>

            <!-- Laboratorio -->
            <div class="campo-form">
                <label for="laboratorio">Laboratorio *:</label>
                <input type="text" id="laboratorio" name="laboratorio" required placeholder="Ej. Laboratorios Pisa">
            </div>

            <!-- Lote -->
            <div class="campo-form">
                <label for="lote">Lote:</label>
                <input type="text" id="lote" name="lote" placeholder="Ej. L-001">
            </div>

            <!-- Fecha de Caducidad -->
            <div class="campo-form">
                <label for="fecha_caducidad">Fecha de Caducidad *:</label>
                <input type="date" id="fecha_caducidad" name="fecha_caducidad" required>
            </div>

            <!-- Precio de Venta (Bloqueado a >= 0) -->
            <div class="campo-form">
                <label for="precio_venta">Precio de Venta ($) *:</label>
                <input type="number" step="0.01" min="0" id="precio_venta" name="precio_venta" required placeholder="0.00" oninput="if(this.value < 0) this.value = 0;">
            </div>

            <!-- Cantidad en Existencia (Bloqueado a >= 0) -->
            <div class="campo-form">
                <label for="existencia">Cantidad en Existencia *:</label>
                <input type="number" min="0" id="existencia" name="existencia" required placeholder="0" oninput="if(this.value < 0) this.value = 0;">
            </div>

            <!-- Imagen (Ocupa 2 columnas) -->
            <div class="campo-form campo-full">
                <label for="imagen">Nombre o archivo de Imagen:</label>
                <input type="text" id="imagen" name="imagen" placeholder="placeholderFotoUsuario.png">
            </div>

            <!-- Botones de Acción (Ocupan 2 columnas) -->
            <div class="modal-acciones campo-full">
                <button type="button" class="btn-cancelar" id="btn-cancelar-modal">Cancelar</button>
                <button type="submit" class="btn-guardar" id="btn-guardar-producto">Guardar</button>
            </div>

        </form>
    </div>
</div>

    <!-- Enlace al controlador JavaScript dentro de tu carpeta JS (respetando las mayúsculas) -->
    <script src="../JS/productos.js"></script>
</body>
</html>
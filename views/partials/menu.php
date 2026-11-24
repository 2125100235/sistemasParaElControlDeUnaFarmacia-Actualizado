<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
   <?php
// Iniciar la sesión si aún no está iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el rol, nombre y foto de la sesión
$rol    = isset($_SESSION['rol']) ? mb_strtolower($_SESSION['rol']) : '';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$foto   = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'fotoDefectoEmpleado.png';
?>

<aside class="menu">
    <?php if ($rol === 'administrador' || $rol === 'admin'): ?>
        <a href="../views/Control_Usuarios.php"><h1>Control de usuarios</h1></a>

        <a href="../views/Perfil_Administrador.php"><h1>Ventas y facturación</h1></a>

        <a href="../views/Politicas_Sistema.php"><h1>Políticas del sistema</h1></a>

        <a href="#"><h1>Contactos</h1></a>
        <ul>
            <li><a href="../views/Soporte_tecnico.php"><h2>Soporte Técnico </h2></a></li>
            <li><a href="../views/Directorio_Proveedores.php"><h2>Directorio de proveedores</h2></a></li>
        </ul>

    <?php elseif ($rol === 'almacenista'): ?>
        <a href="../views/Productos_Pantalla_Almacenista.php"><h1>Productos e Inventario</h1></a>
        
        <a href="../views/Directorio_Proveedores.php"><h1>Proveedores</h1></a>

        <a href="#"><h1>Protocolos</h1></a>
        <ul>
            <li><a href="../views/Protocolos_Medicamentos_Refrigerados.php"><h2>Protocolo de medicamentos refrigerados</h2></a></li>
            <li><a href="../views/Normativa_Almacenamiento.php"><h2>Normativa de almacenamiento</h2></a></li>
        </ul>

        <a href="../views/Soporte_tecnico.php"><h1>Contactar a soporte técnico</h1></a>

    <?php elseif ($rol === 'cajero'): ?>
        <a href="../views/Venta.php"><h1>Punto de Venta</h1></a>
        <a href="../views/Control_Clientes.php"><h1>Control de Clientes</h1></a>
        <a href="#"><h1>Protocolos</h1></a>
        <ul>
            <li><a href="../views/Atencion_Cliente.php">Guía de atención al cliente</a></li>
            <li><a href="../views/Politicas_de_cobro_y_garantias.php">Políticas de cobro y garantías</a></li>
        </ul>
        <a href="../views/Soporte_tecnico.php"><h1>Contactar a soporte técnico</h1></a>

    <?php else: ?>
        <!-- Mensaje de respaldo si no hay sesión activa -->
        <p style="padding: 15px; color: red;">Sesión no detectada. Por favor inicie sesión.</p>
    <?php endif; ?>

<!-- Ficha del usuario dinámico -->
<div class="usuario"> 
        <img src="../assets/<?php echo htmlspecialchars($foto); ?>" 
             alt="Foto de perfil del usuario" 
             width="40" 
             onerror="this.src='../assets/fotoDefectoEmpleado.png'">
        <div class="info-usuario">
            <a href="../views/Perfil_de_Usuario.php">
                <h1><?php echo htmlspecialchars($nombre); ?></h1>
            </a>
            <h2><?php echo htmlspecialchars(ucfirst($rol)); ?></h2>
        </div>
        <a href="../controllers/AuthController.php?accion=cerrar" class="btn-cerrar-sesion-menu" title="Cerrar sesión">
            Cerrar sesión
        </a>
    </div>
</aside>
<script src="../js/menuToggle.js"></script>
</body>
</html>
<?php
// Iniciar la sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener datos de la sesión
$rol        = isset($_SESSION['rol']) ? mb_strtolower($_SESSION['rol']) : '';
$nombre     = $_SESSION['nombre'] ?? 'Usuario';
$fotoPerfil = !empty($_SESSION['foto']) ? $_SESSION['foto'] : 'fotoDefectoEmpleado.png';
?>

<aside class="menu">
    <?php if ($rol === 'administrador' || $rol === 'admin'): ?>
        <a href="../views/Control_Usuarios.php"><h1>Control de usuarios</h1></a>
        <a href="../views/Perfil_Administrador.php"><h1>Ventas y facturación</h1></a>
        <a href="../views/Politicas_Sistema.php"><h1>Políticas del sistema</h1></a>
        <a href="#"><h1>Contactos</h1></a>
        <ul>
            <li><a href="../views/Soporte_tecnico.php">Soporte Técnico</a></li>
            <li><a href="../views/Directorio_Proveedores.php">Directorio de proveedores</a></li>
        </ul>

    <?php elseif ($rol === 'almacenista'): ?>
        <a href="../views/Productos_Pantalla_Almacenista.php"><h1>Productos e Inventario</h1></a>
        <a href="../views/Directorio_Proveedores.php"><h1>Proveedores</h1></a>
        <a href="#"><h1>Protocolos</h1></a>
        <ul>
            <li><a href="../views/Protocolos_Medicamentos_Refrigerados.php">Protocolo de medicamentos refrigerados</a></li>
            <li><a href="../views/Normativa_Almacenamiento.php">Normativa de almacenamiento</a></li>
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
        <p style="padding: 15px; color: var(--color-quinario);">Sesión no detectada. Por favor inicie sesión.</p>
    <?php endif; ?>

    <!-- Ficha del usuario dinámico -->
    <div class="usuario">
        <a href="../views/Perfil_de_Usuario.php" class="usuario-link-foto">
            <img src="../assets/<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto de perfil del usuario">
        </a>
        <div class="info-usuario">
            <a href="../views/Perfil_de_Usuario.php">
                <h1><?php echo htmlspecialchars($nombre); ?></h1>
            </a>
            <h2><?php echo htmlspecialchars(ucfirst($rol)); ?></h2>
        </div>
    </div>
</aside>
<script src="../js/menuToggle.js"></script>
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
// Iniciar la sesión si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el rol de la sesión y convertidor a minúsculas para comparar fácil
$rol = isset($_SESSION['rol']) ? mb_strtolower($_SESSION['rol']) : '';
$nombre = $_SESSION['nombre'] ?? 'Usuario';
?>

<aside class="menu">
    <?php if ($rol === 'administrador' || $rol === 'admin'): ?>
        <a href="#"><h1>Control de usuarios</h1></a>

        <a href="../screens/Perfil_administrador.php"><h1>Ventas y facturación</h1></a>

        <a href="../screens/Politicas_Sistema.php"><h1>Políticas del sistema</h1></a>

        <a href="#"><h1>Contactos</h1></a>
        <ul>
            <li><a href="../screens/Soporte_tecnico.php"><h2>Soporte Técnico </h2></a></li>
            <li><a href="#"><h2>Directorio de proveedores</h2></a></li>
        </ul>

    <?php elseif ($rol === 'almacenista'): ?>
        <a href="../screens/Productos_Pantalla_Almacenista.php"><h1>Productos e Inventario</h1></a>
        
        <a href="#"><h1>Proveedores</h1></a>

        <a href="#"><h1>Protocolos</h1></a>
        <ul>
            <li><a href="../screens/Protocolos_Medicamentos_Refrigerados.php"><h2>Protocolo de medicamentos refrigerados</h2></a></li>
            <li><a href="../screens/Normativa_Almacenamiento.php"><h2>Normativa de almacenamiento</h2></a></li>
        </ul>

        <a href="../screens/Soporte_tecnico.php"><h1>Contactar a soporte técnico</h1></a>

    <?php elseif ($rol === 'cajero'): ?>
        <a href="../screens/Venta.php"><h1>Punto de Venta</h1></a>
        <a href="#"><h1>Control de Clientes</h1></a>
        <a href="#"><h1>Protocolos</h1></a>
        <ul>
            <li><a href="../screens/Atencion_Cliente.php">Guía de atención al cliente</a></li>
            <li><a href="../screens/Politicas_de_cobro_y_garantias.php">Políticas de cobro y garantías</a></li>
        </ul>
        <a href="../screens/Soporte_tecnico.php"><h1>Contactar a soporte técnico</h1></a>

    <?php else: ?>
        <!-- Mensaje de respaldo si no hay sesión activa -->
        <p style="padding: 15px; color: red;">Sesión no detectada. Por favor inicie sesión.</p>
    <?php endif; ?>

    <!-- Ficha del usuario dinámico -->
    <div class="usuario"> 
        <div class="info-usuario">
            <img src="../assets/placeholderFotoUsuario.png" alt="Foto de perfil del usuario" width="40">
            <a href="Perfil_de_Usuario.php">
                <h1><?php echo htmlspecialchars($nombre); ?></h1>
            </a>
        </div>
        <h2><?php echo htmlspecialchars(ucfirst($rol)); ?></h2>
    </div>
</aside>
<script src="../js/menuToggle.js"></script>
</body>
</html>
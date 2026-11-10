<?php
// Inicia la sesión si aún no está activa en el servidor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Le avisa al navegador que la respuesta será un JSON en texto UTF-8
header('Content-Type: application/json; charset=utf-8');

// Comprueba que el usuario tenga una sesión válida de empleado
if (!isset($_SESSION['id_empleado'])) {
    // Si no inició sesión, devuelve un mensaje de error en formato JSON
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado: Sesión no iniciada'
    ]);
    exit(); // Detiene la ejecución del código
}

// Incluye el archivo que conecta con la base de datos MySQL
require_once "conexion.php";

// Lee el tipo de filtro enviado por la URL (si no hay, usa 'hoy')
$tipo  = isset($_GET['tipo']) ? $_GET['tipo'] : 'hoy';
// Lee la fecha enviada por la URL (si no hay, usa la fecha de hoy)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Evalúa qué opción de filtro seleccionó el usuario
switch ($tipo) {
    case 'dia':
        // Filtra las ventas de un día específico elegido
        $filtroSql = "WHERE DATE(v.fecha) = ?";
        $parametro = $fecha;
        break;

    case 'semana':
        // Filtra las ventas de la semana completa del día elegido
        $filtroSql = "WHERE YEARWEEK(v.fecha, 1) = YEARWEEK(?, 1)";
        $parametro = $fecha;
        break;

    case 'mes':
        // Filtra las ventas del mes y año elegidos (ejemplo: '2026-11')
        $filtroSql = "WHERE DATE_FORMAT(v.fecha, '%Y-%m') = ?";
        $parametro = $fecha;
        break;

    case 'semana_actual':
        // Filtra las ventas de la semana en curso (hoy)
        $filtroSql = "WHERE YEARWEEK(v.fecha, 1) = YEARWEEK(CURDATE(), 1)";
        $parametro = null;
        break;

    case 'mes_actual':
        // Filtra las ventas del mes y año actuales (hoy)
        $filtroSql = "WHERE MONTH(v.fecha) = MONTH(CURDATE()) AND YEAR(v.fecha) = YEAR(CURDATE())";
        $parametro = null;
        break;

    case 'hoy':
    default:
        // Filtra únicamente las ventas del día de hoy
        $filtroSql = "WHERE DATE(v.fecha) = CURDATE()";
        $parametro = null;
        break;
}

// Instrucción SQL para traer el folio de venta, fecha y nombre del cajero
$sqlVentas = "SELECT v.numero_nota AS id_venta, v.fecha, CONCAT(e.nombre, ' ', e.apellido_paterno) AS cajero 
              FROM venta v
              INNER JOIN empleado e ON v.id_empleado = e.id_empleado
              $filtroSql
              ORDER BY v.fecha DESC";

// Prepara la consulta para evitar inyecciones SQL
$stmtVentas = $conexion->prepare($sqlVentas);

// Si se envió un parámetro de fecha, lo asigna a la consulta de forma segura
if ($parametro !== null) {
    $stmtVentas->bind_param("s", $parametro);
}

// Ejecuta la consulta de ventas en la base de datos
$stmtVentas->execute();
// Obtiene el resultado con las ventas encontradas
$resVentas = $stmtVentas->get_result();

// Arreglo vacío donde guardaremos todas las ventas procesadas
$ventas = [];

// Comprueba si la consulta devolvió al menos una venta
if ($resVentas && $resVentas->num_rows > 0) {
    // Recorre cada venta encontrada una por una
    while ($venta = $resVentas->fetch_assoc()) {
        $id_venta = $venta['id_venta']; // Guarda el número de nota actual
        
        // Consulta SQL para traer los productos de la venta actual
        $sqlDetalle = "SELECT p.nombre AS producto, dv.cantidad_salida AS cantidad, dv.precio_venta AS precio_unitario, dv.subtotal
                       FROM detalle_venta dv
                       INNER JOIN producto p ON dv.codigo_producto = p.codigo
                       WHERE dv.numero_nota = ?";
                       
        // Prepara la subconsulta de productos
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        // Sustituye el signo '?' por la ID de la venta actual
        $stmtDetalle->bind_param("i", $id_venta);
        // Ejecuta la búsqueda de productos
        $stmtDetalle->execute();
        // Guarda los productos encontrados
        $resDetalle = $stmtDetalle->get_result();
        
        $productos = []; // Arreglo para meter los productos de esta nota
        $totalVenta = 0; // Variable para ir sumando el costo total de la venta

        // Recorre los productos de la venta actual
        while ($prod = $resDetalle->fetch_assoc()) {
            $subtotal = (float)$prod['subtotal']; // Convierte el subtotal a número decimal
            $totalVenta += $subtotal;             // Suma el subtotal al total general

            // Guarda los datos de cada producto
            $productos[] = [
                'producto'        => $prod['producto'],
                'cantidad'        => (int)$prod['cantidad'],
                'precio_unitario' => (float)$prod['precio_unitario'],
                'subtotal'        => $subtotal
            ];
        }
        // Cierra la subconsulta de productos
        $stmtDetalle->close();

        // Guarda la estructura completa de la venta en el arreglo final
        $ventas[] = [
            'id_venta'  => (int)$venta['id_venta'],
            'fecha'     => date("d/m/Y h:i A", strtotime($venta['fecha'])), // Da formato bonito a la fecha
            'cajero'    => $venta['cajero'],
            'total'     => $totalVenta, // Asigna el total calculado
            'productos' => $productos   // Asigna la lista de productos
        ];
    }
}

// Cierra la consulta principal de ventas
$stmtVentas->close();
// Cierra la conexión a la base de datos MySQL
$conexion->close();

// Devuelve el arreglo final de ventas en formato JSON ordenado
echo json_encode([
    'status' => 'success',
    'total_ventas' => count($ventas),
    'data' => $ventas
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); //reanuda la sesión del servidor
}
header('Content-Type: application/json; charset=utf-8'); //Envia un encabezado http indicando que la respuesta que espera son datos en un JSON

// Valida que haya una sesion activa de un empleado autorizado
if (!isset($_SESSION['id_empleado'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado: Sesión no iniciada'
    ]);
    exit();
}

//importa el archivo de conexion a MYSQL para poder hacer uso de la variable "conexion"
require_once "conexion.php";

// Obtiene todas las ventas registradas junto con el nombre del cajero de la mas reciente a la mas antigua ORDER BY v.fecha DESC
$sqlVentas = "SELECT v.numero_nota AS id_venta, v.fecha, CONCAT(e.nombre, ' ', e.apellido_paterno) AS cajero 
              FROM venta v
              INNER JOIN empleado e ON v.id_empleado = e.id_empleado
              ORDER BY v.fecha DESC";

//almacena el resultado general de las ventas en la variable resVentas
$resVentas = $conexion->query($sqlVentas);

//Crea un arreglo vacio llamado ventas, este nos servira como contenedor final
$ventas = [];

//si hubo al menos un registro, comienza un bucle while que ira recorriendo registro por registro de la base de datos gracias a fetch_assoc
if ($resVentas && $resVentas->num_rows > 0) {
    while ($venta = $resVentas->fetch_assoc()) {
        $id_venta = $venta['id_venta']; //extrae la id de la venta actual en esta nueva variable
        
        //Es una cadena de texto para extraer, son las instrucciones sql que utilizaremos mas adelante
        $sqlDetalle = "SELECT p.nombre AS producto, dv.cantidad_salida AS cantidad, dv.precio_venta AS precio_unitario, dv.subtotal
                       FROM detalle_venta dv
                       INNER JOIN producto p ON dv.codigo_producto = p.codigo
                       WHERE dv.numero_nota = ?";
                       
        $stmtDetalle = $conexion->prepare($sqlDetalle); //manda las instrucciones a mysql
        $stmtDetalle->bind_param("i", $id_venta); //Cambia el ? por el id de la venta actual
        $stmtDetalle->execute(); //busca los productos de esta venta en la base de datos 
        $resDetalle = $stmtDetalle->get_result(); //obtenemos los resultados de la consulta. (la lista de productos)
        
        //creamos el arreglo productos, donde se ira guardando cada producto de la venta actual, convirtiendolo a entero o flotante de manera segura.
        $productos = [];
        $totalVenta = 0;
        while ($prod = $resDetalle->fetch_assoc()) {
            $subtotal = (float)$prod['subtotal'];
            $totalVenta += $subtotal;

            $productos[] = [
                'producto'        => $prod['producto'],
                'cantidad'        => (int)$prod['cantidad'],
                'precio_unitario' => (float)$prod['precio_unitario'],
                'subtotal'        => $subtotal
            ];
        }
        $stmtDetalle->close(); //cierra la subconsulta al terminar

        //Guardamos las ventas completas en su arreglo ventas, cuando nos referimos a la variable producto, quiere decir que guardamos el arreglo completo con todos los productos listos. 
        $ventas[] = [
            'id_venta'  => (int)$venta['id_venta'],
            'fecha'     => date("d/m/Y h:i A", strtotime($venta['fecha'])),
            'cajero'    => $venta['cajero'],
            'total'     => $totalVenta,
            'productos' => $productos
        ];
    }
}

$conexion->close(); //Cierra la conexion con base de datos

// Convierte la estructura completa en JSON, respetando acentos y con formato limpio y legible
echo json_encode([
    'status' => 'success',
    'total_ventas' => count($ventas),
    'data' => $ventas
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
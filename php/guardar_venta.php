<?php
header('Content-Type: application/json'); //le comunicamos al navegador que la respuesta que esperamos es un JSON
require_once 'conexion.php'; //conectamos con l abase de datos con la variable conexion

// 1. Leer los datos JSON que nos envió JavaScript mediante fetch
$input = file_get_contents('php://input'); //abre el canal de recepcion para capturar los datos con la funcion fetch
$data = json_decode($input, true); //traduce los datos JSON a un arreglo nativo en php para poder leer variables como data

// Validar que hayamos recibido productos, en caso de que sea cierto, mandamos un mensaje de error
if (!$data || empty($data['productos'])) {
    echo json_encode(['success' => false, 'message' => 'No hay productos para guardar.']);
    exit;
}

// Tomamos el ID del cajero, si la sesion esta cerrada, por defecto tomamos el 3 que es la primera cajera registrada
$id_empleado = $_SESSION['id_empleado'] ?? 3;
//  Funcion recursiva para calcular la suma de los subtotales en el servidor
function calcularSubtotalRecursivoPHP(array $productos, int $indice = 0): float {
    if ($indice >= count($productos)) {// verifica si ya no quiedan mas elementos en el arreglo, si es asi, retorna 0.0 para que la suma no se vea afectada
        return 0.0;
    }
    return (float)$productos[$indice]['subtotal'] + calcularSubtotalRecursivoPHP($productos, $indice + 1);// Toma el subtotal del producto actual y le suma el resultado de la llamada recursiva para el siguiente producto
}

// --- CÁLCULO RECURSIVO DEL TOTAL CON IVA ---
$subtotalBase = calcularSubtotalRecursivoPHP($data['productos']);// Llama a la función recursiva para calcular el subtotal de todos los productos en el arreglo $data['productos']
$total_pagar  = $subtotalBase * 1.16;// Calcula el total a pagar aplicando el 16% de IVA sobre el subtotal base

// En esta linea inicia el modo transaccion, es decir, lo guarda de manera temporal hasta que se le confirme
$conexion->begin_transaction();

try {
    // Prepara una tipo plantilla en mysql, primero con el encabezado de la venta. Los ?? so espacios en blanco que se llenan mas adelante, pero esto evita posibles ataques a sql
    $stmtVenta = $conexion->prepare("INSERT INTO venta (total_pagar, id_empleado) VALUES (?, ?)");

    //bind_param, une nuestras variables a la base de datos pero como referencias, para evitar hackeos
    //Entonces en esta parte pasamos el total a pagar como numero decimal (d) y el id como entero (i) 
    $stmtVenta->bind_param("di", $total_pagar, $id_empleado);

    //Ejecuta la consulta, creando el renglon correspondiente en la tabla.
    $stmtVenta->execute();
    
    // Guardar el número de nota recién generado automáticamente por MySQL (AUTO_INCREMENT)
    $numero_nota = $conexion->insert_id;

    // Prepara la plantilla sql para insertar cada medicamento en la tabla.
    $stmtDetalle = $conexion->prepare("INSERT INTO detalle_venta (numero_nota, codigo_producto, cantidad_salida, precio_venta, subtotal) VALUES (?, ?, ?, ?, ?)");

    //Prepara plantilla sql para descontar existencias, validando que la existencia sea mayor o igual a lo que se pide
    $stmtStock = $conexion->prepare("UPDATE producto SET existencia = existencia - ? WHERE codigo = ? AND existencia >= ?");

    //Recorrer los productos que venían en la pila, y extrae cada valor
    foreach ($data['productos'] as $prod) {
        $codigo   = $prod['codigo'];
        $cantidad = $prod['cantidad'];
        $precio   = $prod['precio'];
        $subtotal = $prod['subtotal'];

        // rellena la plantilla de Detalle con los valores (entero x3) (decimal x2) y ejecuta la orden para registra cada articulo en la tabla de venta
        $stmtDetalle->bind_param("iiidd", $numero_nota, $codigo, $cantidad, $precio, $subtotal);
        $stmtDetalle->execute();

        // rellena la plantilla de Stocl con tres numeros enteros y resta el stock
        $stmtStock->bind_param("iii", $cantidad, $codigo, $cantidad);
        $stmtStock->execute();

        // Si no se modificó ninguna fila, significa que el stock no alcanzaba
        if ($stmtStock->affected_rows === 0) {
            throw new Exception("Inventario insuficiente para el producto con código: " . $codigo);
        }
    }

    // Confirma que todas las operaciones se realizaron con éxito
    $conexion->commit();
    echo json_encode(['success' => true, 'numero_nota' => $numero_nota]);

} catch (Exception $e) {
    // Si algo falló, deshace absolutamente todos los cambios hechos en las tablas
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
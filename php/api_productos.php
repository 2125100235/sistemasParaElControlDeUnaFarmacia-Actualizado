<?php
// Establece el encabezado para responder únicamente en formato JSON con UTF-8
header('Content-Type: application/json; charset=utf-8');

// Incluye el archivo de conexión a la base de datos (inicia $conexion y session_start)
require_once "conexion.php";

// Captura la acción enviada desde JavaScript por GET o POST (por defecto 'leer')
$accion = $_REQUEST['accion'] ?? 'leer';

// Estructura base de la respuesta JSON que se enviará al cliente
$respuesta = [
    'exito' => false,     // Indica si la operación concluyó con éxito
    'mensaje' => '',       // Mensaje descriptivo para el usuario o sistema
    'datos' => []          // Arreglo para devolver los datos de los productos
];

// Evalúa la acción enviada desde el frontend
switch ($accion) {

    // CASO 1: LEER Y BUSCAR PRODUCTOS (READ)
    case 'leer':
        // Obtiene y limpia la cadena de texto enviada desde la barra de búsqueda
        $buscar = trim($_GET['buscar'] ?? '');

        if (!empty($buscar)) {
            // Prepara la consulta para buscar coincidencias por nombre o por código del producto
            $stmt = $conexion->prepare("SELECT * FROM producto WHERE estado = 1 AND (nombre LIKE ? OR codigo LIKE ?) ORDER BY codigo DESC");
            // Agrega los comodines de porcentaje para la búsqueda parcial
            $termino = "%{$buscar}%";
            // Vincula los dos parámetros string ("ss") a la consulta preparada
            $stmt->bind_param("ss", $termino, $termino);
            // Ejecuta la consulta parametrizada
            $stmt->execute();
            // Obtiene el resultado de la ejecución
            $resultado = $stmt->get_result();
            // Transforma los datos en un arreglo asociativo de PHP
            $productos = $resultado->fetch_all(MYSQLI_ASSOC);
            // Cierra la sentencia
            $stmt->close();
        } else {
            // Si el buscador está vacío, consulta todos los productos activos (estado = 1)
            $resultado = $conexion->query("SELECT * FROM producto WHERE estado = 1 ORDER BY codigo DESC");
            // Convierte las filas recibidas en un arreglo asociativo
            $productos = $resultado->fetch_all(MYSQLI_ASSOC);
        }

        // Marca la respuesta como exitosa y asigna la lista de productos
        $respuesta['exito'] = true;
        $respuesta['datos'] = $productos;
        break;

    // CASO 2: CREAR UN NUEVO PRODUCTO (CREATE)
    case 'crear':
        // Captura y limpia los campos enviados desde el formulario modal por POST
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo_medicamento'] ?? 'OTC / Libre Venta';
        $laboratorio = trim($_POST['laboratorio'] ?? '');
        $lote = trim($_POST['lote'] ?? '');
        $caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;
        $existencia = intval($_POST['existencia'] ?? 0);
        $precio = floatval($_POST['precio_venta'] ?? 0.00);
        $id_proveedor = !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null;
        $imagen = !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'placeholderFotoUsuario.png';

        // Valida que el nombre, el laboratorio, la caducidad y los números sean correctos
        if (!empty($nombre) && !empty($laboratorio) && !empty($caducidad) && $precio >= 0 && $existencia >= 0) {
            // Prepara la instrucción SQL INSERT con 9 parámetros
            $stmt = $conexion->prepare("INSERT INTO producto (nombre, tipo_medicamento, laboratorio, lote, fecha_caducidad, existencia, precio_venta, id_proveedor, estado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
            // Vincula las variables a los tipos: s=string, i=int, d=double/decimal
            $stmt->bind_param("sssssidis", $nombre, $tipo, $laboratorio, $lote, $caducidad, $existencia, $precio, $id_proveedor, $imagen);

            // Ejecuta la sentencia e informa el resultado
            if ($stmt->execute()) {
                $respuesta['exito'] = true;
                $respuesta['mensaje'] = 'Producto agregado correctamente.';
            } else {
                $respuesta['mensaje'] = 'Error al insertar en la base de datos: ' . $conexion->error;
            }
            // Cierra la consulta
            $stmt->close();
        } else {
            $respuesta['mensaje'] = 'Completa todos los campos obligatorios (Nombre, Laboratorio, Caducidad, Precio y Existencia).';
        }
        break;

   
    // CASO 3: OBTENER UN SOLO PRODUCTO POR SU CÓDIGO (READ ONE)
    
    case 'obtener_uno':
        // Obtiene el código del producto solicitado vía GET
        $codigo = intval($_GET['id'] ?? 0);

        if ($codigo > 0) {
            // Prepara la consulta para extraer únicamente el producto especificado
            $stmt = $conexion->prepare("SELECT * FROM producto WHERE codigo = ? AND estado = 1");
            // Vincula la clave primaria entera ("i")
            $stmt->bind_param("i", $codigo);
            // Ejecuta la consulta
            $stmt->execute();
            // Obtiene la fila resultante en forma de arreglo asociativo
            $producto = $stmt->get_result()->fetch_assoc();
            // Cierra la sentencia
            $stmt->close();

            // Verifica si el producto existía en la base de datos
            if ($producto) {
                $respuesta['exito'] = true;
                $respuesta['datos'] = $producto;
            } else {
                $respuesta['mensaje'] = 'El producto no existe o está inactivo.';
            }
        } else {
            $respuesta['mensaje'] = 'Código de producto no válido.';
        }
        break;

    // --------------------------------------------------------------------------
    // CASO 4: ACTUALIZAR PRODUCTO (UPDATE)
    // --------------------------------------------------------------------------
    case 'actualizar':
        // Captura la clave primaria (código del producto)
        $codigo = intval($_POST['codigo'] ?? 0);
        // Sanitiza el resto de campos actualizados
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo_medicamento'] ?? 'OTC / Libre Venta';
        $laboratorio = trim($_POST['laboratorio'] ?? '');
        $lote = trim($_POST['lote'] ?? '');
        $caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;
        $existencia = intval($_POST['existencia'] ?? 0);
        $precio = floatval($_POST['precio_venta'] ?? 0.00);
        $id_proveedor = !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null;
        $imagen = !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'placeholderFotoUsuario.png';

        // Valida que exista una clave primaria válida y datos indispensables
        if ($codigo > 0 && !empty($nombre) && !empty($laboratorio) && !empty($caducidad)) {
            // Prepara la consulta UPDATE
            $stmt = $conexion->prepare("UPDATE producto SET nombre = ?, tipo_medicamento = ?, laboratorio = ?, lote = ?, fecha_caducidad = ?, existencia = ?, precio_venta = ?, id_proveedor = ?, imagen = ? WHERE codigo = ? AND estado = 1");
            // Vincula las variables a los tipos de datos (s=string, i=int, d=double)
            $stmt->bind_param("sssssidisi", $nombre, $tipo, $laboratorio, $lote, $caducidad, $existencia, $precio, $id_proveedor, $imagen, $codigo);

            // Ejecuta la sentencia UPDATE
            if ($stmt->execute()) {
                $respuesta['exito'] = true;
                $respuesta['mensaje'] = 'Producto actualizado correctamente.';
            } else {
                $respuesta['mensaje'] = 'Error al actualizar el registro: ' . $conexion->error;
            }
            // Cierra la sentencia
            $stmt->close();
        } else {
            $respuesta['mensaje'] = 'Datos insuficientes para realizar la actualización.';
        }
        break;

  
    // CASO 5: ELIMINAR PRODUCTO - BORRADO LÓGICO (DELETE)
    
    case 'eliminar':
        // Obtiene el código del producto a dar de baja
        $codigo = intval($_POST['codigo'] ?? 0);

        if ($codigo > 0) {
            // Aplica borrado lógico cambiando estado a 0 (desactivado)
            $stmt = $conexion->prepare("UPDATE producto SET estado = 0 WHERE codigo = ?");
            // Vincula la clave primaria
            $stmt->bind_param("i", $codigo);

            // Ejecuta la desactivación del registro
            if ($stmt->execute()) {
                $respuesta['exito'] = true;
                $respuesta['mensaje'] = 'Producto eliminado con éxito.';
            } else {
                $respuesta['mensaje'] = 'No se pudo eliminar el producto.';
            }
            // Cierra la consulta
            $stmt->close();
        } else {
            $respuesta['mensaje'] = 'Identificador de producto no válido.';
        }
        break;
}

// Emite la respuesta estructurada en formato JSON hacia JavaScript
echo json_encode($respuesta);
// Finaliza la ejecución del script PHP
exit();
?>
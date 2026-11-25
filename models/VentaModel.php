<?php
class VentaModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // 1. OBTENER VENTAS Y DETALLES FILTRADOS (Para el Carrusel / Lista Circular)
    public function obtenerVentasDetalle($tipo = 'hoy', $fecha = null) {
        if ($fecha === null) {
            $fecha = date('Y-m-d');
        }

        switch ($tipo) {
            case 'dia':
                $filtroSql = "WHERE DATE(v.fecha) = ?";
                $parametro = $fecha;
                break;

            case 'semana':
                $filtroSql = "WHERE YEARWEEK(v.fecha, 1) = YEARWEEK(?, 1)";
                $parametro = $fecha;
                break;

            case 'mes':
                $filtroSql = "WHERE DATE_FORMAT(v.fecha, '%Y-%m') = ?";
                $parametro = $fecha;
                break;

            case 'semana_actual':
                $filtroSql = "WHERE YEARWEEK(v.fecha, 1) = YEARWEEK(CURDATE(), 1)";
                $parametro = null;
                break;

            case 'mes_actual':
                $filtroSql = "WHERE MONTH(v.fecha) = MONTH(CURDATE()) AND YEAR(v.fecha) = YEAR(CURDATE())";
                $parametro = null;
                break;

            case 'hoy':
            default:
                $filtroSql = "WHERE DATE(v.fecha) = CURDATE()";
                $parametro = null;
                break;
        }

        $sqlVentas = "SELECT v.numero_nota AS id_venta, v.fecha, 
                             CONCAT(e.nombre, ' ', e.apellido_paterno) AS cajero,
                             COALESCE(c.nombre, 'Público en general') AS cliente
                      FROM venta v
                      INNER JOIN empleado e ON v.id_empleado = e.id_empleado
                      LEFT JOIN cliente c ON v.codigo_cliente = c.codigo
                      $filtroSql
                      ORDER BY v.fecha DESC";

        $stmtVentas = $this->db->prepare($sqlVentas);

        if ($parametro !== null) {
            $stmtVentas->bind_param("s", $parametro);
        }

        $stmtVentas->execute();
        $resVentas = $stmtVentas->get_result();

        $ventas = [];

        if ($resVentas && $resVentas->num_rows > 0) {
            $sqlDetalle = "SELECT p.nombre AS producto, dv.cantidad_salida AS cantidad, dv.precio_venta AS precio_unitario, dv.subtotal
                           FROM detalle_venta dv
                           INNER JOIN producto p ON dv.codigo_producto = p.codigo
                           WHERE dv.numero_nota = ?";
            $stmtDetalle = $this->db->prepare($sqlDetalle);

            while ($venta = $resVentas->fetch_assoc()) {
                $id_venta = (int)$venta['id_venta'];

                $stmtDetalle->bind_param("i", $id_venta);
                $stmtDetalle->execute();
                $resDetalle = $stmtDetalle->get_result();

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

                $ventas[] = [
                    'id_venta'  => $id_venta,
                    'fecha'     => date("d/m/Y h:i A", strtotime($venta['fecha'])),
                    'cajero'    => $venta['cajero'],
                    'cliente'   => $venta['cliente'],
                    'total'     => $totalVenta,
                    'productos' => $productos
                ];
            }

            $stmtDetalle->close();
        }

        $stmtVentas->close();
        return $ventas;
    }

    // 2. REGISTRAR VENTA Y DESCONTAR INVENTARIO CON TRANSACCIÓN (Para Punto de Venta)
    public function registrarVenta($id_empleado, $total_pagar, array $productos, $codigo_cliente = null) {
        $this->db->begin_transaction();

        try {
            // Insertar encabezado de la venta (codigo_cliente NULL = Público en general)
            $stmtVenta = $this->db->prepare("INSERT INTO venta (total_pagar, codigo_cliente, id_empleado) VALUES (?, ?, ?)");
            $stmtVenta->bind_param("dii", $total_pagar, $codigo_cliente, $id_empleado);
            $stmtVenta->execute();

            $numero_nota = $this->db->insert_id;
            $stmtVenta->close();

            // Preparar consulta para validar nombre, stock y caducidad
            $stmtInfoProd = $this->db->prepare(
                "SELECT nombre, existencia, fecha_caducidad FROM producto WHERE codigo = ? AND estado = 1"
            );

            // Preparar sentencias de detalle y actualización de stock
            $stmtDetalle = $this->db->prepare(
                "INSERT INTO detalle_venta (numero_nota, codigo_producto, cantidad_salida, precio_venta, subtotal) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmtStock = $this->db->prepare(
                "UPDATE producto SET existencia = existencia - ? WHERE codigo = ? AND existencia >= ?"
            );

            $hoy = date('Y-m-d');

            foreach ($productos as $prod) {
                $codigo   = (int)$prod['codigo'];
                $cantidad = (int)$prod['cantidad'];
                $precio   = (float)$prod['precio'];
                $subtotal = (float)$prod['subtotal'];

                $stmtInfoProd->bind_param("i", $codigo);
                $stmtInfoProd->execute();
                $infoProducto = $stmtInfoProd->get_result()->fetch_assoc();
                $nombreProducto = $infoProducto ? $infoProducto['nombre'] : ($prod['nombre'] ?? 'Desconocido');

                if (!$infoProducto) {
                    $stmtInfoProd->close();
                    $stmtDetalle->close();
                    $stmtStock->close();
                    throw new Exception("El producto \"" . $nombreProducto . "\" no está disponible.");
                }

                if (!empty($infoProducto['fecha_caducidad']) && $infoProducto['fecha_caducidad'] <= $hoy) {
                    $stmtInfoProd->close();
                    $stmtDetalle->close();
                    $stmtStock->close();
                    throw new Exception("El producto \"" . $nombreProducto . "\" tiene la caducidad vencida (" . $infoProducto['fecha_caducidad'] . ").");
                }

                $stmtDetalle->bind_param("iiidd", $numero_nota, $codigo, $cantidad, $precio, $subtotal);
                $stmtDetalle->execute();

                $stmtStock->bind_param("iii", $cantidad, $codigo, $cantidad);
                $stmtStock->execute();

                if ($stmtStock->affected_rows === 0) {
                    $disponible = (int)$infoProducto['existencia'];
                    $stmtInfoProd->close();
                    $stmtDetalle->close();
                    $stmtStock->close();
                    throw new Exception("Stock insuficiente para el producto \"" . $nombreProducto . "\" (Disponible: " . $disponible . ").");
                }
            }

            $stmtInfoProd->close();
            $stmtDetalle->close();
            $stmtStock->close();

            // Confirma todos los cambios en la base de datos
            $this->db->commit();
            return $numero_nota;

        } catch (Exception $e) {
            // Revierte cualquier cambio si ocurrió un error
            $this->db->rollback();
            throw $e;
        }
    }
}
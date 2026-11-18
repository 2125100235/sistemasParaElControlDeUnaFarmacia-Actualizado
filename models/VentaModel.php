<?php
class VentaModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Obtiene las ventas filtradas por fecha junto con sus productos (para el Carrusel / Lista Circular)
    public function obtenerVentasDetalle($tipo = 'hoy', $fecha = null) {
        if ($fecha === null) {
            $fecha = date('Y-m-d');
        }

        // Evalúa qué opción de filtro seleccionó el usuario
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

        // Consulta principal: folio de venta, fecha y nombre del cajero
        $sqlVentas = "SELECT v.numero_nota AS id_venta, v.fecha, CONCAT(e.nombre, ' ', e.apellido_paterno) AS cajero 
                      FROM venta v
                      INNER JOIN empleado e ON v.id_empleado = e.id_empleado
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
            // Prepara una sola vez la subconsulta de productos para reutilizarla en el ciclo
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
                    'total'     => $totalVenta,
                    'productos' => $productos
                ];
            }

            $stmtDetalle->close();
        }

        $stmtVentas->close();
        return $ventas;
    }

    // AQUÍ AGREGAREMOS DESPUÉS EL MÉTODO registrarVenta() DE guardar_venta.php
}


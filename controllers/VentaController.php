<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/VentaModel.php';

class VentaController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new VentaModel($conexion);
    }

    // Función recursiva para calcular la suma de los subtotales en el servidor
    private function calcularSubtotalRecursivo(array $productos, int $indice = 0): float {
        if ($indice >= count($productos)) {
            return 0.0;
        }
        return (float)$productos[$indice]['subtotal'] + $this->calcularSubtotalRecursivo($productos, $indice + 1);
    }

    public function procesarPeticion() {
        // Si llega por POST sin parámetro 'accion', asume 'guardar'; si llega por GET, asume 'listar'
        $accion = $_REQUEST['accion'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'guardar' : 'listar');

        switch ($accion) {
            case 'listar':
                if (!isset($_SESSION['id_empleado'])) {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Acceso denegado: Sesión no iniciada'
                    ]);
                    exit();
                }

                $tipo  = $_GET['tipo'] ?? 'hoy';
                $fecha = $_GET['fecha'] ?? date('Y-m-d');

                $ventas = $this->modelo->obtenerVentasDetalle($tipo, $fecha);

                echo json_encode([
                    'status'       => 'success',
                    'total_ventas' => count($ventas),
                    'data'         => $ventas
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;

            case 'guardar':
                $input = file_get_contents('php://input');
                $data  = json_decode($input, true);

                if (!$data || empty($data['productos'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No hay productos para guardar.'
                    ]);
                    exit();
                }

                $id_empleado  = $_SESSION['id_empleado'] ?? 3;
                $subtotalBase = $this->calcularSubtotalRecursivo($data['productos']);
                $total_pagar  = $subtotalBase * 1.16;

                try {
                    $numero_nota = $this->modelo->registrarVenta($id_empleado, $total_pagar, $data['productos']);
                    echo json_encode([
                        'success'     => true,
                        'numero_nota' => $numero_nota
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                break;
        }

        exit();
    }
}

$controlador = new VentaController($conexion);
$controlador->procesarPeticion();
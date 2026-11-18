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

    public function procesarPeticion() {
        // Valida que el usuario tenga sesión activa
        if (!isset($_SESSION['id_empleado'])) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Acceso denegado: Sesión no iniciada'
            ]);
            exit();
        }

        // Por defecto ejecuta 'listar' si viene por GET
        $accion = $_REQUEST['accion'] ?? 'listar';

        switch ($accion) {
            case 'listar':
                $tipo  = $_GET['tipo'] ?? 'hoy';
                $fecha = $_GET['fecha'] ?? date('Y-m-d');

                $ventas = $this->modelo->obtenerVentasDetalle($tipo, $fecha);

                echo json_encode([
                    'status'       => 'success',
                    'total_ventas' => count($ventas),
                    'data'         => $ventas
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;

            // AQUÍ AGREGAREMOS DESPUÉS EL case 'guardar': DE guardar_venta.php
        }

        exit();
    }
}

$controlador = new VentaController($conexion);
$controlador->procesarPeticion();
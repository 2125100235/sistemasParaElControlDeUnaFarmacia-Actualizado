<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/ClienteModel.php';

class ClienteController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ClienteModel($conexion);
    }

    public function procesarPeticion() {
        $accion = $_REQUEST['accion'] ?? 'leer';
        $respuesta = [
            'exito' => false,
            'mensaje' => '',
            'datos' => []
        ];

        $regexLetras = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u';
        $regexRFC = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/u';

        switch ($accion) {
            case 'leer':
                $buscar = trim($_GET['buscar'] ?? '');
                $respuesta['exito'] = true;
                $respuesta['datos'] = $this->modelo->listar($buscar);
                break;

            case 'crear':
                $datos = [
                    'nombre'    => trim($_POST['nombre'] ?? ''),
                    'telefono'  => !empty(trim($_POST['telefono'] ?? '')) ? trim($_POST['telefono']) : null,
                    'rfc'       => !empty(trim($_POST['rfc'] ?? '')) ? mb_strtoupper(trim($_POST['rfc'])) : null,
                    'direccion' => !empty(trim($_POST['direccion'] ?? '')) ? trim($_POST['direccion']) : null
                ];

                if (empty($datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre del cliente es obligatorio.';
                } elseif (mb_strlen($datos['nombre']) < 2 || !preg_match($regexLetras, $datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre del cliente debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif ($datos['telefono'] !== null && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif ($datos['rfc'] !== null && !preg_match($regexRFC, $datos['rfc'])) {
                    $respuesta['mensaje'] = 'El formato del RFC no es válido (debe tener 12 o 13 caracteres, ej. XAXX010101000).';
                } else {
                    if ($this->modelo->crear($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Cliente agregado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al insertar el cliente en la base de datos.';
                    }
                }
                break;

            case 'obtener_uno':
                $codigo = intval($_GET['id'] ?? 0);

                if ($codigo > 0) {
                    $cliente = $this->modelo->obtenerPorCodigo($codigo);
                    if ($cliente) {
                        $respuesta['exito'] = true;
                        $respuesta['datos'] = $cliente;
                    } else {
                        $respuesta['mensaje'] = 'El cliente no existe o está inactivo.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Código de cliente no válido.';
                }
                break;

            case 'actualizar':
                $datos = [
                    'codigo'    => intval($_POST['codigo'] ?? 0),
                    'nombre'    => trim($_POST['nombre'] ?? ''),
                    'telefono'  => !empty(trim($_POST['telefono'] ?? '')) ? trim($_POST['telefono']) : null,
                    'rfc'       => !empty(trim($_POST['rfc'] ?? '')) ? mb_strtoupper(trim($_POST['rfc'])) : null,
                    'direccion' => !empty(trim($_POST['direccion'] ?? '')) ? trim($_POST['direccion']) : null
                ];

                if ($datos['codigo'] <= 0 || empty($datos['nombre'])) {
                    $respuesta['mensaje'] = 'Datos insuficientes para actualizar el cliente.';
                } elseif (mb_strlen($datos['nombre']) < 2 || !preg_match($regexLetras, $datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre del cliente debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif ($datos['telefono'] !== null && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif ($datos['rfc'] !== null && !preg_match($regexRFC, $datos['rfc'])) {
                    $respuesta['mensaje'] = 'El formato del RFC no es válido (debe tener 12 o 13 caracteres, ej. XAXX010101000).';
                } else {
                    if ($this->modelo->actualizar($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Cliente actualizado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al actualizar el registro del cliente.';
                    }
                }
                break;

            case 'eliminar':
                $codigo = intval($_POST['codigo'] ?? 0);

                if ($codigo > 0) {
                    if ($this->modelo->eliminar($codigo)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Cliente eliminado con éxito.';
                    } else {
                        $respuesta['mensaje'] = 'No se pudo eliminar el cliente.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Identificador de cliente no válido.';
                }
                break;
        }

        echo json_encode($respuesta);
        exit();
    }
}

$controlador = new ClienteController($conexion);
$controlador->procesarPeticion();
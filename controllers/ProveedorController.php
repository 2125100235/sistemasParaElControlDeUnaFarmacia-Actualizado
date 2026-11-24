<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/ProveedorModel.php';

class ProveedorController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ProveedorModel($conexion);
    }

    public function procesarPeticion() {
        $accion = $_REQUEST['accion'] ?? 'leer';
        $respuesta = [
            'exito' => false,
            'mensaje' => '',
            'datos' => []
        ];

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
                    'correo'    => !empty(trim($_POST['correo'] ?? '')) ? trim($_POST['correo']) : null,
                    'direccion' => !empty(trim($_POST['direccion'] ?? '')) ? trim($_POST['direccion']) : null
                ];

                if (empty($datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre del proveedor es obligatorio.';
                } elseif (mb_strlen($datos['nombre']) < 2) {
                    $respuesta['mensaje'] = 'El nombre del proveedor debe tener al menos 2 caracteres.';
                } elseif ($datos['telefono'] !== null && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif ($datos['correo'] !== null && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                    $respuesta['mensaje'] = 'El formato del correo electrónico no es válido.';
                } else {
                    if ($this->modelo->crear($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Proveedor agregado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al insertar el proveedor en la base de datos.';
                    }
                }
                break;

            case 'obtener_uno':
                $id = intval($_GET['id'] ?? 0);

                if ($id > 0) {
                    $proveedor = $this->modelo->obtenerPorId($id);
                    if ($proveedor) {
                        $respuesta['exito'] = true;
                        $respuesta['datos'] = $proveedor;
                    } else {
                        $respuesta['mensaje'] = 'El proveedor no existe o está inactivo.';
                    }
                } else {
                    $respuesta['mensaje'] = 'ID de proveedor no válido.';
                }
                break;

            case 'actualizar':
                $datos = [
                    'id_proveedor' => intval($_POST['id_proveedor'] ?? 0),
                    'nombre'       => trim($_POST['nombre'] ?? ''),
                    'telefono'     => !empty(trim($_POST['telefono'] ?? '')) ? trim($_POST['telefono']) : null,
                    'correo'       => !empty(trim($_POST['correo'] ?? '')) ? trim($_POST['correo']) : null,
                    'direccion'    => !empty(trim($_POST['direccion'] ?? '')) ? trim($_POST['direccion']) : null
                ];

                if ($datos['id_proveedor'] <= 0 || empty($datos['nombre'])) {
                    $respuesta['mensaje'] = 'Datos insuficientes para actualizar el proveedor.';
                } elseif (mb_strlen($datos['nombre']) < 2) {
                    $respuesta['mensaje'] = 'El nombre del proveedor debe tener al menos 2 caracteres.';
                } elseif ($datos['telefono'] !== null && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif ($datos['correo'] !== null && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                    $respuesta['mensaje'] = 'El formato del correo electrónico no es válido.';
                } else {
                    if ($this->modelo->actualizar($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Proveedor actualizado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al actualizar el registro del proveedor.';
                    }
                }
                break;

            case 'eliminar':
                $id = intval($_POST['id_proveedor'] ?? 0);

                if ($id > 0) {
                    if ($this->modelo->eliminar($id)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Proveedor eliminado con éxito.';
                    } else {
                        $respuesta['mensaje'] = 'No se pudo eliminar el proveedor.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Identificador de proveedor no válido.';
                }
                break;
        }

        echo json_encode($respuesta);
        exit();
    }
}

$controlador = new ProveedorController($conexion);
$controlador->procesarPeticion();
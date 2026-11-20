<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ProductoModel($conexion);
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
                    'nombre'       => trim($_POST['nombre'] ?? ''),
                    'tipo'         => $_POST['tipo_medicamento'] ?? 'OTC / Libre Venta',
                    'laboratorio'  => trim($_POST['laboratorio'] ?? ''),
                    'lote'         => trim($_POST['lote'] ?? ''),
                    'caducidad'    => !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null,
                    'existencia'   => intval($_POST['existencia'] ?? 0),
                    'precio'       => floatval($_POST['precio_venta'] ?? 0.00),
                    'id_proveedor' => !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null,
                    'imagen'       => !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'placeholderFotoUsuario.png'
                ];

                if (!empty($datos['nombre']) && !empty($datos['laboratorio']) && !empty($datos['caducidad']) && $datos['precio'] >= 0 && $datos['existencia'] >= 0) {
                    if ($this->modelo->crear($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Producto agregado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al insertar en la base de datos.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Completa todos los campos obligatorios (Nombre, Laboratorio, Caducidad, Precio y Existencia).';
                }
                break;

            case 'obtener_uno':
                $codigo = intval($_GET['id'] ?? 0);

                if ($codigo > 0) {
                    $producto = $this->modelo->obtenerPorCodigo($codigo);
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

            case 'actualizar':
                $datos = [
                    'codigo'       => intval($_POST['codigo'] ?? 0),
                    'nombre'       => trim($_POST['nombre'] ?? ''),
                    'tipo'         => $_POST['tipo_medicamento'] ?? 'OTC / Libre Venta',
                    'laboratorio'  => trim($_POST['laboratorio'] ?? ''),
                    'lote'         => trim($_POST['lote'] ?? ''),
                    'caducidad'    => !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null,
                    'existencia'   => intval($_POST['existencia'] ?? 0),
                    'precio'       => floatval($_POST['precio_venta'] ?? 0.00),
                    'id_proveedor' => !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null,
                    'imagen'       => !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'placeholderFotoUsuario.png'
                ];

                if ($datos['codigo'] > 0 && !empty($datos['nombre']) && !empty($datos['laboratorio']) && !empty($datos['caducidad'])) {
                    if ($this->modelo->actualizar($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Producto actualizado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al actualizar el registro.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Datos insuficientes para realizar la actualización.';
                }
                break;

            case 'eliminar':
                $codigo = intval($_POST['codigo'] ?? 0);

                if ($codigo > 0) {
                    if ($this->modelo->eliminar($codigo)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Producto eliminado con éxito.';
                    } else {
                        $respuesta['mensaje'] = 'No se pudo eliminar el producto.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Identificador de producto no válido.';
                }
                break;
        }

        echo json_encode($respuesta);
        exit();
    }
}

// Instancia y ejecuta el controlador usando la variable $conexion de config/conexion.php
$controlador = new ProductoController($conexion);
$controlador->procesarPeticion();
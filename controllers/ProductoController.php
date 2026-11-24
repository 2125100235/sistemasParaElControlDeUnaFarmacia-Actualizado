<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ProductoModel($conexion);
    }

    // Valida y guarda la imagen en ../assets/ si se seleccionó un archivo nuevo
    private function procesarImagen(string $imagenActual): array {
        if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['exito' => true, 'nombre' => $imagenActual];
        }

        if ($_FILES['archivo_imagen']['error'] !== UPLOAD_ERR_OK) {
            return ['exito' => false, 'mensaje' => 'Error al recibir la imagen en el servidor.'];
        }

        $extension  = strtolower(pathinfo($_FILES['archivo_imagen']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (!in_array($extension, $permitidas)) {
            return ['exito' => false, 'mensaje' => 'Formato no válido. Usa JPG, PNG, WEBP o AVIF.'];
        }

        $nuevoNombre = "prod_" . time() . "_" . mt_rand(100, 999) . "." . $extension;
        $rutaDestino = __DIR__ . '/../assets/' . $nuevoNombre;

        if (move_uploaded_file($_FILES['archivo_imagen']['tmp_name'], $rutaDestino)) {
            return ['exito' => true, 'nombre' => $nuevoNombre];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo guardar la imagen en la carpeta assets.'];
    }

    public function procesarPeticion() {
        $accion = $_REQUEST['accion'] ?? 'leer';
        $respuesta = [
            'exito' => false,
            'mensaje' => '',
            'datos' => []
        ];

        $tiposPermitidos = ['Genérico', 'Patente', 'Controlado', 'OTC / Libre Venta', 'Material Cuidado Personal'];

        switch ($accion) {
            case 'leer':
                $buscar = trim($_GET['buscar'] ?? '');
                $respuesta['exito'] = true;
                $respuesta['datos'] = $this->modelo->listar($buscar);
                break;

            case 'crear':
                $imagenBase = !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'imagenMedicinaDefecto.jpg';
                $resultadoImagen = $this->procesarImagen($imagenBase);

                if (!$resultadoImagen['exito']) {
                    $respuesta['mensaje'] = $resultadoImagen['mensaje'];
                    break;
                }

                $datos = [
                    'nombre'       => trim($_POST['nombre'] ?? ''),
                    'tipo'         => $_POST['tipo_medicamento'] ?? 'OTC / Libre Venta',
                    'laboratorio'  => trim($_POST['laboratorio'] ?? ''),
                    'lote'         => trim($_POST['lote'] ?? ''),
                    'caducidad'    => !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null,
                    'existencia'   => intval($_POST['existencia'] ?? 0),
                    'precio'       => floatval($_POST['precio_venta'] ?? 0.00),
                    'id_proveedor' => !empty($_POST['id_proveedor']) ? intval($_POST['id_proveedor']) : null,
                    'imagen'       => $resultadoImagen['nombre']
                ];

                $fechaObj = DateTime::createFromFormat('Y-m-d', $datos['caducidad'] ?? '');
                $fechaValida = $fechaObj && $fechaObj->format('Y-m-d') === $datos['caducidad'];

                if (empty($datos['nombre']) || empty($datos['laboratorio']) || empty($datos['caducidad'])) {
                    $respuesta['mensaje'] = 'Completa todos los campos obligatorios (Nombre, Laboratorio, Caducidad, Precio y Existencia).';
                } elseif (mb_strlen($datos['nombre']) < 2) {
                    $respuesta['mensaje'] = 'El nombre del producto debe tener al menos 2 caracteres.';
                } elseif (!in_array($datos['tipo'], $tiposPermitidos, true)) {
                    $respuesta['mensaje'] = 'El tipo de medicamento seleccionado no es válido.';
                } elseif (mb_strlen($datos['laboratorio']) < 2) {
                    $respuesta['mensaje'] = 'El laboratorio debe tener al menos 2 caracteres.';
                } elseif (!empty($datos['lote']) && !preg_match('/^[A-Za-z0-9\-_]{1,50}$/', $datos['lote'])) {
                    $respuesta['mensaje'] = 'El lote solo puede contener letras, números, guiones y máximo 50 caracteres.';
                } elseif (!$fechaValida) {
                    $respuesta['mensaje'] = 'La fecha de caducidad no tiene un formato válido.';
                } elseif ($datos['precio'] <= 0) {
                    $respuesta['mensaje'] = 'El precio de venta debe ser mayor a $0.00.';
                } elseif ($datos['existencia'] < 0) {
                    $respuesta['mensaje'] = 'La existencia no puede ser negativa.';
                } else {
                    if ($this->modelo->crear($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Producto agregado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al insertar en la base de datos.';
                    }
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
                $imagenBase = !empty($_POST['imagen']) ? trim($_POST['imagen']) : 'imagenMedicinaDefecto.jpg';
                $resultadoImagen = $this->procesarImagen($imagenBase);

                if (!$resultadoImagen['exito']) {
                    $respuesta['mensaje'] = $resultadoImagen['mensaje'];
                    break;
                }

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
                    'imagen'       => $resultadoImagen['nombre']
                ];

                $fechaObj = DateTime::createFromFormat('Y-m-d', $datos['caducidad'] ?? '');
                $fechaValida = $fechaObj && $fechaObj->format('Y-m-d') === $datos['caducidad'];

                if ($datos['codigo'] <= 0 || empty($datos['nombre']) || empty($datos['laboratorio']) || empty($datos['caducidad'])) {
                    $respuesta['mensaje'] = 'Datos insuficientes para realizar la actualización.';
                } elseif (mb_strlen($datos['nombre']) < 2) {
                    $respuesta['mensaje'] = 'El nombre del producto debe tener al menos 2 caracteres.';
                } elseif (!in_array($datos['tipo'], $tiposPermitidos, true)) {
                    $respuesta['mensaje'] = 'El tipo de medicamento seleccionado no es válido.';
                } elseif (mb_strlen($datos['laboratorio']) < 2) {
                    $respuesta['mensaje'] = 'El laboratorio debe tener al menos 2 caracteres.';
                } elseif (!empty($datos['lote']) && !preg_match('/^[A-Za-z0-9\-_]{1,50}$/', $datos['lote'])) {
                    $respuesta['mensaje'] = 'El lote solo puede contener letras, números, guiones y máximo 50 caracteres.';
                } elseif (!$fechaValida) {
                    $respuesta['mensaje'] = 'La fecha de caducidad no tiene un formato válido.';
                } elseif ($datos['precio'] <= 0) {
                    $respuesta['mensaje'] = 'El precio de venta debe ser mayor a $0.00.';
                } elseif ($datos['existencia'] < 0) {
                    $respuesta['mensaje'] = 'La existencia no puede ser negativa.';
                } else {
                    if ($this->modelo->actualizar($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Producto actualizado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al actualizar el registro.';
                    }
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
<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new UsuarioModel($conexion);
    }

    public function procesarPeticion() {
        $accion = $_REQUEST['accion'] ?? 'leer';
        $respuesta = [
            'exito' => false,
            'mensaje' => '',
            'datos' => []
        ];

        $rolesPermitidos = ['Administrador', 'Cajero', 'Almacenista'];
        $regexLetras = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u';

        switch ($accion) {
            case 'leer':
                $buscar = trim($_GET['buscar'] ?? '');
                $respuesta['exito'] = true;
                $respuesta['datos'] = $this->modelo->listar($buscar);
                break;

            case 'crear':
                $datos = [
                    'nombre'           => trim($_POST['nombre'] ?? ''),
                    'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                    'apellido_materno' => !empty(trim($_POST['apellido_materno'] ?? '')) ? trim($_POST['apellido_materno']) : null,
                    'correo'           => trim($_POST['correo'] ?? ''),
                    'telefono'         => !empty(trim($_POST['telefono'] ?? '')) ? trim($_POST['telefono']) : null,
                    'rol'              => $_POST['rol'] ?? '',
                    'clave'            => trim($_POST['clave'] ?? '')
                ];

                if (empty($datos['nombre']) || empty($datos['apellido_paterno']) || empty($datos['correo']) || empty($datos['clave'])) {
                    $respuesta['mensaje'] = 'Completa los campos obligatorios (Nombre, Apellido Paterno, Correo y Contraseña).';
                } elseif (mb_strlen($datos['nombre']) < 2 || !preg_match($regexLetras, $datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre es obligatorio, debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif (mb_strlen($datos['apellido_paterno']) < 2 || !preg_match($regexLetras, $datos['apellido_paterno'])) {
                    $respuesta['mensaje'] = 'El apellido paterno es obligatorio, debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif (!empty($datos['apellido_materno']) && !preg_match($regexLetras, $datos['apellido_materno'])) {
                    $respuesta['mensaje'] = 'El apellido materno solo puede contener letras.';
                } elseif (!empty($datos['telefono']) && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                    $respuesta['mensaje'] = 'El formato del correo electrónico no es válido.';
                } elseif (!in_array($datos['rol'], $rolesPermitidos, true)) {
                    $respuesta['mensaje'] = 'Selecciona un rol válido.';
                } elseif (mb_strlen($datos['clave']) < 6) {
                    $respuesta['mensaje'] = 'La contraseña debe tener al menos 6 caracteres.';
                } elseif ($this->modelo->existeCorreo($datos['correo'])) {
                    $respuesta['mensaje'] = 'Ese correo electrónico ya está registrado por otro usuario.';
                } else {
                    
                   

                    if ($this->modelo->crear($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Usuario agregado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al insertar el usuario en la base de datos.';
                    }
                }
                break;

            case 'obtener_uno':
                $id = intval($_GET['id'] ?? 0);

                if ($id > 0) {
                    $usuario = $this->modelo->obtenerPorId($id);
                    if ($usuario) {
                        $respuesta['exito'] = true;
                        $respuesta['datos'] = $usuario;
                    } else {
                        $respuesta['mensaje'] = 'El usuario no existe o está inactivo.';
                    }
                } else {
                    $respuesta['mensaje'] = 'ID de usuario no válido.';
                }
                break;

            case 'actualizar':
                $datos = [
                    'id_empleado'      => intval($_POST['id_empleado'] ?? 0),
                    'nombre'           => trim($_POST['nombre'] ?? ''),
                    'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
                    'apellido_materno' => !empty(trim($_POST['apellido_materno'] ?? '')) ? trim($_POST['apellido_materno']) : null,
                    'correo'           => trim($_POST['correo'] ?? ''),
                    'telefono'         => !empty(trim($_POST['telefono'] ?? '')) ? trim($_POST['telefono']) : null,
                    'rol'              => $_POST['rol'] ?? '',
                    'clave'            => trim($_POST['clave'] ?? '')
                ];

                if ($datos['id_empleado'] <= 0 || empty($datos['nombre']) || empty($datos['apellido_paterno']) || empty($datos['correo'])) {
                    $respuesta['mensaje'] = 'Completa los campos obligatorios para actualizar el usuario.';
                } elseif (mb_strlen($datos['nombre']) < 2 || !preg_match($regexLetras, $datos['nombre'])) {
                    $respuesta['mensaje'] = 'El nombre es obligatorio, debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif (mb_strlen($datos['apellido_paterno']) < 2 || !preg_match($regexLetras, $datos['apellido_paterno'])) {
                    $respuesta['mensaje'] = 'El apellido paterno es obligatorio, debe tener al menos 2 caracteres y solo puede contener letras.';
                } elseif (!empty($datos['apellido_materno']) && !preg_match($regexLetras, $datos['apellido_materno'])) {
                    $respuesta['mensaje'] = 'El apellido materno solo puede contener letras.';
                } elseif (!empty($datos['telefono']) && !preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
                    $respuesta['mensaje'] = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
                } elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                    $respuesta['mensaje'] = 'El formato del correo electrónico no es válido.';
                } elseif (!in_array($datos['rol'], $rolesPermitidos, true)) {
                    $respuesta['mensaje'] = 'Selecciona un rol válido.';
                } elseif (!empty($datos['clave']) && mb_strlen($datos['clave']) < 6) {
                    $respuesta['mensaje'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
                } elseif ($this->modelo->existeCorreo($datos['correo'], $datos['id_empleado'])) {
                    $respuesta['mensaje'] = 'Ese correo electrónico ya pertenece a otro usuario.';
                } else {
                    // Si tu login usa password_verify() y se ingresó nueva clave, descomenta:
                    // if (!empty($datos['clave'])) { $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT); }

                    if ($this->modelo->actualizar($datos)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Usuario actualizado correctamente.';
                    } else {
                        $respuesta['mensaje'] = 'Error al actualizar el registro del usuario.';
                    }
                }
                break;

            case 'eliminar':
                $id = intval($_POST['id_empleado'] ?? 0);

                if ($id > 0) {
                    $usuario = $this->modelo->obtenerPorId($id);

                    if (!$usuario) {
                        $respuesta['mensaje'] = 'El usuario no existe o ya fue eliminado.';
                    } elseif ($usuario['rol'] === 'Administrador') {
                        $respuesta['mensaje'] = 'No se puede eliminar a un usuario con el rol de Administrador.';
                    } elseif ($this->modelo->eliminar($id)) {
                        $respuesta['exito'] = true;
                        $respuesta['mensaje'] = 'Usuario eliminado con éxito.';
                    } else {
                        $respuesta['mensaje'] = 'No se pudo eliminar el usuario.';
                    }
                } else {
                    $respuesta['mensaje'] = 'Identificador de usuario no válido.';
                }
                break;
        }

        echo json_encode($respuesta);
        exit();
    }
}

$controlador = new UsuarioController($conexion);
$controlador->procesarPeticion();
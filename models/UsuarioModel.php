<?php
class UsuarioModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Busca un empleado por su ID y nombre exacto (método existente)
    public function buscarEmpleadoPorIdYNombre(int $id, string $nombre) {
        $stmt = $this->db->prepare("SELECT * FROM empleado WHERE id_empleado = ? AND nombre = ?");
        $stmt->bind_param("is", $id, $nombre);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $empleado = null;
        if ($resultado && $resultado->num_rows === 1) {
            $empleado = $resultado->fetch_assoc();
        }

        $stmt->close();
        return $empleado;
    }

    // CASO 1: LEER Y BUSCAR USUARIOS ACTIVOS (READ)
    public function listar($buscar = '') {
        $sqlBase = "SELECT 
                        id_empleado,
                        nombre,
                        apellido_paterno,
                        apellido_materno,
                        CONCAT_WS(' ', nombre, apellido_paterno, NULLIF(apellido_materno, '')) AS nombre_completo,
                        correo,
                        telefono,
                        rol
                    FROM empleado
                    WHERE estado = 1";

        if (!empty($buscar)) {
            $stmt = $this->db->prepare(
                $sqlBase . " AND (
                    id_empleado LIKE ? 
                    OR CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) LIKE ? 
                    OR correo LIKE ? 
                    OR telefono LIKE ? 
                    OR rol LIKE ?
                ) ORDER BY id_empleado DESC"
            );
            $termino = "%{$buscar}%";
            $stmt->bind_param("sssss", $termino, $termino, $termino, $termino, $termino);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $usuarios;
        } else {
            $resultado = $this->db->query($sqlBase . " ORDER BY id_empleado DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // VERIFICAR SI EL CORREO YA EXISTE (EVITA ERROR POR RESTRICCIÓN UNIQUE)
    public function existeCorreo($correo, $excluirId = 0) {
        if ($excluirId > 0) {
            $stmt = $this->db->prepare("SELECT id_empleado FROM empleado WHERE correo = ? AND id_empleado != ? LIMIT 1");
            $stmt->bind_param("si", $correo, $excluirId);
        } else {
            $stmt = $this->db->prepare("SELECT id_empleado FROM empleado WHERE correo = ? LIMIT 1");
            $stmt->bind_param("s", $correo);
        }
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    // CASO 2: CREAR UN NUEVO USUARIO (CREATE)
    public function crear($datos) {
        $stmt = $this->db->prepare(
            "INSERT INTO empleado (nombre, apellido_paterno, apellido_materno, clave, correo, telefono, rol, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );
        $stmt->bind_param(
            "sssssss",
            $datos['nombre'],
            $datos['apellido_paterno'],
            $datos['apellido_materno'],
            $datos['clave'],
            $datos['correo'],
            $datos['telefono'],
            $datos['rol']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 3: OBTENER UN SOLO USUARIO POR SU ID (READ ONE)
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare(
            "SELECT id_empleado, nombre, apellido_paterno, apellido_materno, correo, telefono, rol 
             FROM empleado 
             WHERE id_empleado = ? AND estado = 1"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    // CASO 4: ACTUALIZAR USUARIO (UPDATE)
    public function actualizar($datos) {
        if (!empty($datos['clave'])) {
            $stmt = $this->db->prepare(
                "UPDATE empleado 
                 SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, clave = ?, correo = ?, telefono = ?, rol = ? 
                 WHERE id_empleado = ? AND estado = 1"
            );
            $stmt->bind_param(
                "sssssssi",
                $datos['nombre'],
                $datos['apellido_paterno'],
                $datos['apellido_materno'],
                $datos['clave'],
                $datos['correo'],
                $datos['telefono'],
                $datos['rol'],
                $datos['id_empleado']
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE empleado 
                 SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, correo = ?, telefono = ?, rol = ? 
                 WHERE id_empleado = ? AND estado = 1"
            );
            $stmt->bind_param(
                "ssssssi",
                $datos['nombre'],
                $datos['apellido_paterno'],
                $datos['apellido_materno'],
                $datos['correo'],
                $datos['telefono'],
                $datos['rol'],
                $datos['id_empleado']
            );
        }

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 5: ELIMINAR USUARIO - BORRADO LÓGICO (DELETE)
    public function eliminar($id) {
        $stmt = $this->db->prepare("UPDATE empleado SET estado = 0 WHERE id_empleado = ?");
        $stmt->bind_param("i", $id);
        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }
}
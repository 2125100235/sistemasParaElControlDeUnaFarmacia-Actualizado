<?php
class ProveedorModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // CASO 1: LEER Y BUSCAR PROVEEDORES ACTIVOS (READ)
    public function listar($buscar = '') {
        if (!empty($buscar)) {
            $stmt = $this->db->prepare(
                "SELECT * FROM proveedor 
                 WHERE estado = 1 AND (
                     id_proveedor LIKE ? 
                     OR nombre LIKE ? 
                     OR telefono LIKE ? 
                     OR correo LIKE ?
                 ) 
                 ORDER BY id_proveedor DESC"
            );
            $termino = "%{$buscar}%";
            $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $proveedores = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $proveedores;
        } else {
            $resultado = $this->db->query("SELECT * FROM proveedor WHERE estado = 1 ORDER BY id_proveedor DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // CASO 2: CREAR UN NUEVO PROVEEDOR (CREATE)
    public function crear($datos) {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedor (nombre, telefono, correo, direccion, estado) 
             VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->bind_param(
            "ssss",
            $datos['nombre'],
            $datos['telefono'],
            $datos['correo'],
            $datos['direccion']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 3: OBTENER UN SOLO PROVEEDOR POR SU ID (READ ONE)
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM proveedor WHERE id_proveedor = ? AND estado = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $proveedor = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $proveedor;
    }

    // CASO 4: ACTUALIZAR PROVEEDOR (UPDATE)
    public function actualizar($datos) {
        $stmt = $this->db->prepare(
            "UPDATE proveedor 
             SET nombre = ?, telefono = ?, correo = ?, direccion = ? 
             WHERE id_proveedor = ? AND estado = 1"
        );
        $stmt->bind_param(
            "ssssi",
            $datos['nombre'],
            $datos['telefono'],
            $datos['correo'],
            $datos['direccion'],
            $datos['id_proveedor']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 5: ELIMINAR PROVEEDOR - BORRADO LÓGICO (DELETE)
    public function eliminar($id) {
        $stmt = $this->db->prepare("UPDATE proveedor SET estado = 0 WHERE id_proveedor = ?");
        $stmt->bind_param("i", $id);
        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }
}
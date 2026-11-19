<?php
class ClienteModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // CASO 1: LEER Y BUSCAR CLIENTES ACTIVOS (READ)
    public function listar($buscar = '') {
        if (!empty($buscar)) {
            $stmt = $this->db->prepare(
                "SELECT * FROM cliente 
                 WHERE estado = 1 AND (nombre LIKE ? OR codigo LIKE ? OR rfc LIKE ? OR telefono LIKE ?) 
                 ORDER BY codigo DESC"
            );
            $termino = "%{$buscar}%";
            $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $clientes = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $clientes;
        } else {
            $resultado = $this->db->query("SELECT * FROM cliente WHERE estado = 1 ORDER BY codigo DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // CASO 2: CREAR UN NUEVO CLIENTE (CREATE)
    public function crear($datos) {
        $stmt = $this->db->prepare(
            "INSERT INTO cliente (nombre, telefono, rfc, direccion, estado) 
             VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->bind_param(
            "ssss",
            $datos['nombre'],
            $datos['telefono'],
            $datos['rfc'],
            $datos['direccion']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 3: OBTENER UN SOLO CLIENTE POR SU CÓDIGO (READ ONE)
    public function obtenerPorCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM cliente WHERE codigo = ? AND estado = 1");
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $cliente;
    }

    // CASO 4: ACTUALIZAR CLIENTE (UPDATE)
    public function actualizar($datos) {
        $stmt = $this->db->prepare(
            "UPDATE cliente 
             SET nombre = ?, telefono = ?, rfc = ?, direccion = ? 
             WHERE codigo = ? AND estado = 1"
        );
        $stmt->bind_param(
            "ssssi",
            $datos['nombre'],
            $datos['telefono'],
            $datos['rfc'],
            $datos['direccion'],
            $datos['codigo']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 5: ELIMINAR CLIENTE - BORRADO LÓGICO (DELETE)
    public function eliminar($codigo) {
        $stmt = $this->db->prepare("UPDATE cliente SET estado = 0 WHERE codigo = ?");
        $stmt->bind_param("i", $codigo);
        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }
}
<?php
class ProductoModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // CASO 1: LEER Y BUSCAR PRODUCTOS (READ)
    public function listar($buscar = '') {
        if (!empty($buscar)) {
            $stmt = $this->db->prepare(
                "SELECT * FROM producto WHERE estado = 1 AND (nombre LIKE ? OR codigo LIKE ?) ORDER BY codigo DESC"
            );
            $termino = "%{$buscar}%";
            $stmt->bind_param("ss", $termino, $termino);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $productos = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $productos;
        } else {
            $resultado = $this->db->query("SELECT * FROM producto WHERE estado = 1 ORDER BY codigo DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }
    }

    // CASO 2: CREAR UN NUEVO PRODUCTO (CREATE)
    public function crear($datos) {
        $stmt = $this->db->prepare(
            "INSERT INTO producto (nombre, tipo_medicamento, laboratorio, lote, fecha_caducidad, existencia, precio_venta, id_proveedor, estado, imagen) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)"
        );
        $stmt->bind_param(
            "sssssidis",
            $datos['nombre'],
            $datos['tipo'],
            $datos['laboratorio'],
            $datos['lote'],
            $datos['caducidad'],
            $datos['existencia'],
            $datos['precio'],
            $datos['id_proveedor'],
            $datos['imagen']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 3: OBTENER UN SOLO PRODUCTO POR SU CÓDIGO (READ ONE)
    public function obtenerPorCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM producto WHERE codigo = ? AND estado = 1");
        $stmt->bind_param("i", $codigo);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $producto;
    }

    // CASO 4: ACTUALIZAR PRODUCTO (UPDATE)
    public function actualizar($datos) {
        $stmt = $this->db->prepare(
            "UPDATE producto 
             SET nombre = ?, tipo_medicamento = ?, laboratorio = ?, lote = ?, fecha_caducidad = ?, existencia = ?, precio_venta = ?, id_proveedor = ?, imagen = ? 
             WHERE codigo = ? AND estado = 1"
        );
        $stmt->bind_param(
            "sssssidisi",
            $datos['nombre'],
            $datos['tipo'],
            $datos['laboratorio'],
            $datos['lote'],
            $datos['caducidad'],
            $datos['existencia'],
            $datos['precio'],
            $datos['id_proveedor'],
            $datos['imagen'],
            $datos['codigo']
        );

        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }

    // CASO 5: ELIMINAR PRODUCTO - BORRADO LÓGICO (DELETE)
    public function eliminar($codigo) {
        $stmt = $this->db->prepare("UPDATE producto SET estado = 0 WHERE codigo = ?");
        $stmt->bind_param("i", $codigo);
        $ejecutado = $stmt->execute();
        $stmt->close();
        return $ejecutado;
    }
}
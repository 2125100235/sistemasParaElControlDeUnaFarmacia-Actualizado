<?php
class UsuarioModel {
    private $db;

    // Recibe la conexión mysqli desde el controlador
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Busca un empleado por su ID y nombre exacto
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
}
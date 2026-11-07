<?php
//Activa la memoria global del servidor, es lo que hace que recuerde que usario esta con la sesion iniciada mientras navega en distintas pantallas
session_start();

//importamos el archivo de conexion a la base de datos para poder usar la varianle conexion
require_once "conexion.php";

//Verifica que la info haya llegado mediante datos de un form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los 3 parámetros de la vista
    $id     = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $clave  = trim($_POST['clave']);

    // Consultar el registro exacto por id_empleado y nombre. El id como entero y el empleado como string
    $stmt = $conexion->prepare("SELECT * FROM empleado WHERE (id_empleado = ?) AND nombre = ?");
    $stmt->bind_param("is", $id, $nombre); 
    $stmt->execute();
    $resultado = $stmt->get_result();

    //Revisa si mysql encontro exactamente una coincidencia, si es asi, extrae los datos en un array
    if ($resultado->num_rows === 1) {
        $empleado = $resultado->fetch_assoc();

        // Validar contraseña (compara texto plano o hash encriptado)
        $claveValida = ($clave === $empleado['clave']) || password_verify($clave, $empleado['clave']);

        if ($claveValida) {
            // Guardar las variables en $_SESSION bajo nombres estandarizados (guion bajo). El operador ?? asigna un valor alternativo por si una columna esta vacia
            $_SESSION['id_empleado']      = $empleado['id_empleado'];
            $_SESSION['nombre']           = $empleado['nombre'];
            $_SESSION['apellido_paterno'] = $empleado['apellido_paterno'] ?? $empleado['apellidoPaterno'] ?? '';
            $_SESSION['apellido_materno'] = $empleado['apellido_materno'] ?? $empleado['apellidoMaterno'] ?? '';
            $_SESSION['correo']           = $empleado['correo'] ?? '';
            $_SESSION['telefono']         = $empleado['telefono'] ?? 'Sin registro';
            
            //Determina el rol o puesto del trabajador y lo guarda en SESSION[rol]
            $rol = $empleado['rol'] ?? $empleado['puesto'] ?? 'Administrador';
            $_SESSION['rol'] = $rol;

            // Redireccionar según el rol
            switch (mb_strtolower($rol)) {
                case 'administrador':
                case 'admin':
                    header("Location: ../screens/Perfil_Administrador.php");
                    break;

                case 'almacenista':
                    header("Location: ../screens/Productos_Pantalla_Almacenista.php");
                    break;

                case 'cajero':
                    header("Location: ../screens/Venta.php");
                    break;

                default:
                    header("Location: ../screens/Perfil_Administrador.php");
                    break;
            }
            exit();

        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='../screens/Control_De_Acceso.php';</script>";
        }
    } else {
        echo "<script>alert('El ID o Nombre del empleado no coinciden con ningún registro activo.'); window.location.href='../screens/Control_De_Acceso.php';</script>";
    }

    $stmt->close();
}
$conexion->close();
?>
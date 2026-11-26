<?php
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$id_empleado = trim($_POST['id_empleado'] ?? '');
$correo      = trim($_POST['correo'] ?? '');
$razon       = trim($_POST['razon'] ?? '');

if ($id_empleado === '' || $correo === '' || $razon === '') {
    echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Ingresa un correo electrónico válido.']);
    exit;
}

// Configura aquí la cuenta Gmail que enviará el correo
$correoRemitente    = '2125100243@soy.utj.edu.mx';
$passwordAplicacion = 'abcdefghijklmnop'; // 16 letras sin espacios
$correoDestino      = '2125100235@soy.utj.edu.mx';

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP de Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $correoRemitente;
    $mail->Password   = $passwordAplicacion;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Remitente, destinatario y dirección de respuesta
    $mail->setFrom($correoRemitente, 'FarmaVive Contacto');
    $mail->addAddress($correoDestino);
    $mail->addReplyTo($correo, "Empleado #$id_empleado");

    // Contenido del correo
    $mail->isHTML(false);
    $mail->Subject = "Contacto FarmaVive - Empleado #$id_empleado";
    $mail->Body    = "Nuevo mensaje desde el formulario de contacto de FarmaVive:\n\n"
                   . "ID de empleado: $id_empleado\n"
                   . "Correo de contacto: $correo\n\n"
                   . "Razón / Mensaje:\n$razon\n";

    $mail->send();

    echo json_encode([
        'exito'   => true,
        'mensaje' => 'Tu mensaje ha sido enviado correctamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'exito'   => false,
        'mensaje' => 'Error al enviar el correo: ' . $mail->ErrorInfo
    ]);
}
<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include(__DIR__ . "/../config/conexion.php");

// Crear instancia de la base de datos y obtener conexión
$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Correo requerido"]);
    exit;
}

// Verificar usuario por email en tabla fundaciones y obtener id de usuario
$stmt = $pdo->prepare("
    SELECT u.id 
    FROM usuarios u
    JOIN fundaciones f ON u.fundacion_id = f.id
    WHERE f.email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "No existe usuario con este correo"]);
    exit;
}

// Generar código y guardar en tabla usuarios
$code = random_int(100000, 999999);
$stmt = $pdo->prepare("
    UPDATE usuarios 
    SET reset_code = ?, reset_expire = DATE_ADD(NOW(), INTERVAL 10 MINUTE) 
    WHERE id = ?
");
$stmt->execute([$code, $user['id']]);

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);

try {
    // Configura SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Cambia si usas otro
    $mail->SMTPAuth = true;
    $mail->Username = 'onclubv.x@gmail.com';
    $mail->Password = 'kdvt gttb lqct wrej'; // Usa app password o env vars por seguridad
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('onclubv.x@gmail.com', 'OnClub');
    $mail->addAddress($email);
    $mail->Subject = "Código de recuperación";
    $mail->Body = "Tu código de verificación es: $code";

    $mail->send();

    echo json_encode(["status" => "success", "message" => "Código enviado a tu correo"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al enviar correo: {$mail->ErrorInfo}"]);
}
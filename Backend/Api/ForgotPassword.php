<?php
header("Content-Type: application/json");
include(__DIR__ . '/../config/conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../../vendor/autoload.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data["email"])) {
    echo json_encode(["status" => "error", "message" => "Correo requerido"]);
    exit;
}

$email = $data["email"];

// Buscar usuario
$stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "No existe usuario con ese correo"]);
    exit;
}

// Generar token único
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token en tabla de resets
$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expira) VALUES (?, ?, ?)");
$stmt->execute([$user["id"], $token, $expira]);

// Enlace de reset
$link = "http://tusitio.com/OnClub/Frontend/reset-password.php?token=" . $token;

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);

try {
    // Config SMTP (ejemplo con Gmail)
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "onclubv.x@gmail.com";  
    $mail->Password   = "kdvt gttb lqct wrej";      
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;

    $mail->setFrom("no-reply@onclub.com", "OnClub");
    $mail->addAddress($email, $user["nombre"]);

    $mail->isHTML(true);
    $mail->Subject = "Restablecer tu contraseña - OnClub";
    $mail->Body    = "
        <h2>Hola {$user['nombre']},</h2>
        <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace:</p>
        <p><a href='$link' style='background:#4F46E5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;'>Restablecer Contraseña</a></p>
        <p>Este enlace expira en 1 hora.</p>
    ";

    $mail->send();
    echo json_encode(["status" => "success", "message" => "Hemos enviado un correo con instrucciones"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error al enviar correo: {$mail->ErrorInfo}"]);
}

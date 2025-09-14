<?php
header("Content-Type: application/json; charset=utf-8");

// Configuración de errores (solo para depuración, luego desactívalo en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.log');

// Conexión BD y configuración de correo
require_once __DIR__ . '/../config/conexion.php';
$mailConfig = require __DIR__ . '/../config/config.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . "/../../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../../PHPMailer/src/SMTP.php";
require_once __DIR__ . "/../../PHPMailer/src/Exception.php";

try {
    // Leer JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "No se recibieron datos válidos"]);
        exit;
    }

    // Conexión
    $database = new Database();
    $conn = $database->getConnection();

    // Datos
    $nombre      = $data["organizationName"] ?? "";
    $email       = $data["email"] ?? "";
    $telefono    = $data["phone"] ?? "";
    $ubicacion   = $data["location"] ?? "";
    $web         = $data["website"] ?? "";
    $descripcion = $data["description"] ?? "";

    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Correo inválido"]);
        exit;
    }

    // Validar duplicados
    $stmt = $conn->prepare("SELECT id FROM fundaciones WHERE email = ? OR nombre = ?");
    $stmt->execute([$email, $nombre]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "La fundación ya está registrada"]);
        exit;
    }

    // Insertar fundación
    $stmt = $conn->prepare("INSERT INTO fundaciones (nombre, email, telefono, ubicacion, sitio_web, descripcion) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $email, $telefono, $ubicacion, $web, $descripcion]);
    $fundacionId = $conn->lastInsertId();

    // Crear usuarios
    $baseUsuario  = strtolower(preg_replace('/\s+/', '', $nombre));
    $usuarioAdmin = $baseUsuario . ".admin";
    $usuarioRh    = $baseUsuario . ".rh";

    // Passwords temporales
    $passAdminPlano = bin2hex(random_bytes(4));
    $passRhPlano    = bin2hex(random_bytes(4));

    $passAdminHash = password_hash($passAdminPlano, PASSWORD_BCRYPT);
    $passRhHash    = password_hash($passRhPlano, PASSWORD_BCRYPT);

    // Insert usuarios (agrego también el campo nombre)
    $stmt = $conn->prepare("INSERT INTO usuarios (fundacion_id, usuario, password, rol_id, nombre) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$fundacionId, $usuarioAdmin, $passAdminHash, 1, $nombre]);
    $stmt->execute([$fundacionId, $usuarioRh, $passRhHash, 2, $nombre]);

    // Enviar correo
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $mailConfig["host"];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig["username"];
    $mail->Password   = $mailConfig["password"];
    $mail->SMTPSecure = $mailConfig["encryption"];
    $mail->Port       = $mailConfig["port"];

    $mail->setFrom($mailConfig["from_email"], $mailConfig["from_name"]);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Registro en OnClub";
    $mail->Body = "
    <h2>Hola <b>$nombre</b>,</h2>
    <p>Tu registro fue exitoso en OnClub 🎉</p>
    <p>Datos de acceso:</p>
    <ul>
        <li><b>Usuario Admin:</b> $usuarioAdmin</li>
        <li><b>Contraseña temporal:</b> $passAdminPlano</li>
    </ul>
    <ul>
        <li><b>Usuario RH:</b> $usuarioRh</li>
        <li><b>Contraseña temporal:</b> $passRhPlano</li>
    </ul>
    <p>¡Bienvenido a la comunidad!</p>
";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Registro completado y correo enviado",
        "usuarios" => [
            ["usuario" => $usuarioAdmin, "password" => $passAdminPlano, "rol" => "ADMIN"],
            ["usuario" => $usuarioRh, "password" => $passRhPlano, "rol" => "RH"]
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en el registro: " . $e->getMessage()]);
}

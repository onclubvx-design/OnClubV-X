<?php
header("Content-Type: application/json");
include(__DIR__ . '/../config/conexion.php');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data["token"]) || empty($data["password"])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$token = $data["token"];
$password = password_hash($data["password"], PASSWORD_BCRYPT);

// Validar token
$stmt = $pdo->prepare("SELECT user_id, expira FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    echo json_encode(["status" => "error", "message" => "Token inválido"]);
    exit;
}

if (strtotime($reset["expira"]) < time()) {
    echo json_encode(["status" => "error", "message" => "El token ha expirado"]);
    exit;
}

// Actualizar contraseña del usuario
$stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
$stmt->execute([$password, $reset["user_id"]]);

// Borrar token para que no se reutilice
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
$stmt->execute([$token]);

echo json_encode(["status" => "success", "message" => "Contraseña actualizada correctamente"]);

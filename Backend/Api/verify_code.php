<?php
header("Content-Type: application/json");

include(__DIR__ . "/../config/conexion.php"); 

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");
$code = trim($data["code"] ?? "");

if (empty($email) || empty($code)) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

// Buscar el código en la tabla de usuarios, pero usando el email de fundaciones
$sql = "SELECT u.id 
        FROM usuarios u
        INNER JOIN fundaciones f ON u.fundacion_id = f.id
        WHERE f.email = ? AND u.reset_code = ? AND u.reset_expire > NOW()";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $code]);
$user = $stmt->fetch();

if ($user) {
    echo json_encode(["status" => "success", "message" => "Código válido"]);
} else {
    echo json_encode(["status" => "error", "message" => "Código inválido o expirado"]);
}

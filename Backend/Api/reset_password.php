<?php
header("Content-Type: application/json");

include(__DIR__ . "/../config/conexion.php");

$db = new Database();
$pdo = $db->getConnection();

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data["email"] ?? "");
$newPassword = trim($data["password"] ?? "");

if (empty($email) || empty($newPassword)) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

// Hashear la nueva contraseña
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

// Buscar ID del usuario relacionado con el email de fundación
$sql = "SELECT u.id 
        FROM usuarios u
        INNER JOIN fundaciones f ON u.fundacion_id = f.id
        WHERE f.email = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

// Actualizar contraseña y limpiar código
$update = $pdo->prepare("UPDATE usuarios SET password = ?, reset_code = NULL, reset_expire = NULL WHERE id = ?");
$ok = $update->execute([$hash, $user['id']]);

if ($ok) {
    echo json_encode(["status" => "success", "message" => "Contraseña actualizada con éxito"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al actualizar contraseña"]);
}
?>
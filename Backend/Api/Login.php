<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../config/conexion.php';

try {
    // Crear conexión con la base de datos
    $conexion = new Conexion();
    $conn = $conexion->conectar();

    // Leer datos JSON o POST
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data) {
        // Si no vino JSON, probamos con POST normal
        $data = $_POST;
    }

    if (!isset($data["usuario"], $data["password"])) {
        echo json_encode(["status" => "error", "message" => "Faltan datos"]);
        exit;
    }

    $usuario = trim($data["usuario"]);
    $password = trim($data["password"]);

    // Buscar usuario en la BD
    $stmt = $conn->prepare("SELECT id, fundacion_id, usuario, password, rol_id, nombre, intentos, bloqueado 
                            FROM usuarios 
                            WHERE usuario = :usuario");
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar si está bloqueado
    if ($user['bloqueado']) {
        echo json_encode([
            "status" => "error",
            "message" => "Tu cuenta fue bloqueada por múltiples intentos fallidos. Podrás volver a ingresar en 15 minutos."
        ]);
        exit;
    }

    // Validar contraseña
    if (!password_verify($password, $user["password"])) {
        $nuevosIntentos = $user["intentos"] + 1;
        $bloquear = $nuevosIntentos >= 3 ? 1 : 0;

        $stmtUpdate = $conn->prepare("UPDATE usuarios SET intentos = :intentos, bloqueado = :bloqueado WHERE id = :id");
        $stmtUpdate->execute([
            ":intentos" => $nuevosIntentos,
            ":bloqueado" => $bloquear,
            ":id" => $user["id"]
        ]);

        $msg = $bloquear 
            ? "Usuario bloqueado por múltiples intentos fallidos"
            : "Contraseña incorrecta. Intentos: $nuevosIntentos/3";

        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }

    // Resetear intentos fallidos
    $stmtReset = $conn->prepare("UPDATE usuarios SET intentos = 0 WHERE id = :id");
    $stmtReset->execute([":id" => $user["id"]]);

    // Guardar sesión
    $_SESSION["usuario_id"] = $user["id"];
    $_SESSION["nombre"] = $user["nombre"];
    $_SESSION["rol_id"] = $user["rol_id"];

    // Redirección según rol
    $redirect = "home.php"; // por defecto
    if ($user['rol_id'] == 1 || str_ends_with($user['usuario'], '.admin')) {
        $_SESSION["user_type"] = "admin";
        $redirect = "Admin.php";
    } elseif ($user['rol_id'] == 2 || str_ends_with($user['usuario'], '.rh')) {
        $_SESSION["user_type"] = "rh";
        $redirect = "Rh.php";
    }

    echo json_encode([
        "status" => "success",
        "message" => "Inicio de sesión exitoso",
        "usuario" => [
            "id" => $user["id"],
            "nombre" => $user["nombre"],
            "rol_id" => $user["rol_id"],
            "usuario" => $user["usuario"],
        ],
        "redirect" => $redirect
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error en el servidor: " . $e->getMessage()
    ]);
}

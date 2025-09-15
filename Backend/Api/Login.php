<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../config/conexion.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Leer datos JSON o POST
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (!$data) $data = $_POST;

    if (!isset($data["usuario"], $data["password"])) {
        echo json_encode(["status" => "error", "message" => "Faltan datos"]);
        exit;
    }

    $usuario = trim($data["usuario"]);
    $password = trim($data["password"]);

    // Buscar usuario
    $stmt = $conn->prepare("SELECT id, fundacion_id, usuario, password, rol_id, nombre, intentos, bloqueo_timestamp 
                            FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tiempo de bloqueo en segundos (2 minutos)
    $lockTime = 2 * 60;
    $ahora = time();

    if (!is_null($user['bloqueo_timestamp'])) {
        $tiempoBloqueo = (int)$user['bloqueo_timestamp'];
        $diferencia = $ahora - $tiempoBloqueo;

        if ($diferencia < $lockTime) {
            $segundosRestantes = $lockTime - $diferencia;
            $minutos = floor($segundosRestantes / 60);
            $segundos = $segundosRestantes % 60;
            $tiempoFormateado = sprintf("%d:%02d", $minutos, $segundos);

            echo json_encode([
                "status" => "error",
                "message" => "Tu cuenta está bloqueada. Intenta nuevamente en $tiempoFormateado minutos."
            ]);
            exit;
        } else {
            // Desbloquear usuario
            $stmtUnlock = $conn->prepare("UPDATE usuarios SET bloqueo_timestamp = NULL, intentos = 0 WHERE id = :id");
            $stmtUnlock->execute([":id" => $user["id"]]);
            $user['intentos'] = 0;
        }
    }

    // Validar contraseña
    if (!password_verify($password, $user["password"])) {
        $nuevosIntentos = $user["intentos"] + 1;

        if ($nuevosIntentos >= 3) {
            // Bloquear y guardar timestamp
            $stmtUpdate = $conn->prepare("UPDATE usuarios SET intentos = :intentos, bloqueo_timestamp = :bloqueo WHERE id = :id");
            $stmtUpdate->execute([
                ":intentos" => $nuevosIntentos,
                ":bloqueo" => $ahora,
                ":id" => $user["id"]
            ]);

            echo json_encode([
                "status" => "error",
                "message" => "Usuario bloqueado por múltiples intentos fallidos. Intenta de nuevo en 2 minutos."
            ]);
            exit;
        } else {
            // Solo actualizar intentos
            $stmtUpdate = $conn->prepare("UPDATE usuarios SET intentos = :intentos WHERE id = :id");
            $stmtUpdate->execute([
                ":intentos" => $nuevosIntentos,
                ":id" => $user["id"]
            ]);

            echo json_encode([
                "status" => "error",
                "message" => "Contraseña incorrecta. Intentos: $nuevosIntentos/3"
            ]);
            exit;
        }
    }

    // Login exitoso, resetear estado
    $stmtReset = $conn->prepare("UPDATE usuarios SET intentos = 0, bloqueo_timestamp = NULL WHERE id = :id");
    $stmtReset->execute([":id" => $user["id"]]);

    // Iniciar sesión
    $_SESSION["usuario_id"] = $user["id"];
    $_SESSION["nombre"] = $user["nombre"];
    $_SESSION["rol_id"] = $user["rol_id"];

    // Redirección por rol
    $redirect = "home.php";
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
?>

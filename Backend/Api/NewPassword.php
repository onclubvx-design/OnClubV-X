<?php
include(__DIR__ . "/config/conexion.php");

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    // Validar token
    $stmt = $conn->prepare("SELECT id, reset_expira FROM usuarios WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && strtotime($user["reset_expira"]) > time()) {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

            // Actualizar password y limpiar token
            $stmt = $conn->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
            $stmt->bind_param("si", $password, $user["id"]);
            $stmt->execute();

            echo "<p>✅ Contraseña actualizada correctamente. <a href='login.php'>Inicia sesión</a></p>";
            exit;
        }
    } else {
        die("❌ Token inválido o expirado.");
    }
} else {
    die("❌ Token no proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña</title>
    <link rel="stylesheet" href="../css/Registro.css">
</head>
<body>
    <div class="modal-container" style="max-width:400px; margin:50px auto;">
        <h2>Nueva contraseña</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Nueva contraseña" required>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
</body>
</html>

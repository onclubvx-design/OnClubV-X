<?php
$token = $_GET['token'] ?? '';
if (!$token) {
    die("Token inválido.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - OnClub</title>
    <link rel="stylesheet" href="../css/Registro.css">
</head>
<body>
    <div class="modal-container" style="max-width:400px;margin:50px auto;padding:20px;border:1px solid #ccc;border-radius:12px;">
        <h2>Restablecer Contraseña</h2>
        <form id="resetForm">
            <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirmar Contraseña</label>
                <input type="password" id="confirmPassword" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
        <div id="resetMessage"></div>
    </div>

<script>
document.getElementById("resetForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const token = document.getElementById("token").value;

    if (password !== confirmPassword) {
        alert("Las contraseñas no coinciden");
        return;
    }

    try {
        const res = await fetch("/OnClub/Backend/Api/ResetPassword.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ token, password })
        });
        const data = await res.json();
        document.getElementById("resetMessage").textContent = data.message;
    } catch (err) {
        console.error(err);
        alert("Error al restablecer contraseña.");
    }
});
</script>
</body>
</html>

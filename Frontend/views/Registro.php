<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OnClub - Registro & Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Registro.css">
</head>
<body>
    <!-- Pantalla de bienvenida -->
    <div class="welcome-screen" id="welcomeScreen">
        <div class="welcome-card">
            <h1>Bienvenido a OnClub</h1>
            <p class="inspirational">"Donde la pasión por el golf se encuentra con tu propósito."</p>
            <button id="openModalBtn" class="btn-open-modal">Acceder</button>
        </div>
    </div>

    <!-- Modal con pestañas -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-container">
            <button class="close-btn" onclick="closeModal()">&times;</button>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" id="registerTab" data-target="registerPane">Registro</button>
                <button class="tab-btn" id="loginTab" data-target="loginPane">Iniciar Sesión</button>
                <button class="tab-btn" id="forgotPasswordTab" data-target="forgotPasswordPane">Recuperar</button>
            </div>

            <!-- Contenido Tabs -->
            <div class="tab-content">
                <!-- Registro -->
                <div class="tab-pane active" id="registerPane">
                    <div class="modal-header">
                        <h2>Regístrate en OnClub</h2>
                        <p>Únete a nuestra comunidad y déjanos ayudarte a crecer.</p>
                    </div>

                    <form id="registrationForm">
                        <div class="form-group">
                            <label for="organizationName">Nombre de la Organización <span class="required">*</span></label>
                            <input type="text" id="organizationName" name="organizationName" placeholder="Ingresa el nombre de tu organización" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico <span class="required">*</span></label>
                            <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono <span class="required">*</span></label>
                            <input type="text" id="phone" name="phone" placeholder="+57 300 123 4567" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Ubicación <span class="required">*</span></label>
                            <input type="text" id="location" name="location" placeholder="Ciudad, País" required>
                        </div>
                        <div class="form-group">
                            <label for="website">Sitio Web (Opcional)</label>
                            <input type="url" id="website" name="website" placeholder="https://tu-sitio-web.com">
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción Breve (Opcional)</label>
                            <textarea id="description" name="description" placeholder="Cuéntanos sobre tu organización..."></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">Enviar Registro</button>
                        </div>
                    </form>
                </div>

                <!-- Login -->
                <div class="tab-pane" id="loginPane">
                    <div class="modal-header">
                        <h2>Iniciar Sesión</h2>
                        <p>Accede con tu usuario y contraseña.</p>
                    </div>

                    <form class="login-form" id="loginForm">
                        <div class="form-group">
                            <input type="text" id="loginUsuario" name="usuario" placeholder="Usuario" required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="loginPassword" name="password" placeholder="Contraseña" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Acceder</button>

                        <div class="forgot-password">
                            <a href="#" id="forgotPasswordLink">¿Olvidaste tu contraseña?</a>
                        </div>
                    </form>
                </div>

                <!-- Recuperar Contraseña -->
                <div class="tab-pane" id="forgotPasswordPane">
                    <div class="modal-header">
                        <h2>Recuperar Contraseña</h2>
                        <p>Ingresa tu correo para enviarte un enlace de recuperación.</p>
                    </div>

                    <form id="forgotPasswordForm" class="recover-form">
                        <div class="form-group">
                            <input type="email" id="forgotEmail" name="email" placeholder="ejemplo@correo.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar enlace</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta flotante -->
    <div id="successAlert" class="success-alert"></div>

    <!-- Script principal -->
    <script src="../js/Registro.js"></script>
</body>
</html>

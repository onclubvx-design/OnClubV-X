<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>OnClub - Registro & Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Registro.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <!-- Modal principal -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-container">
            <button class="close-btn" id="closeModalBtn">&times;</button>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" id="registerTab" data-target="registerPane">Registro</button>
                <button class="tab-btn" id="loginTab" data-target="loginPane">Iniciar Sesión</button>
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
                            <input type="text" id="organizationName" name="organizationName" required autocomplete="organization">
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="location">Ubicación <span class="required">*</span></label>
                            <input type="text" id="location" name="location" required autocomplete="address-level2">
                        </div>
                        <div class="form-group">
                            <label for="website">Sitio Web (Opcional)</label>
                            <input type="url" id="website" name="website" autocomplete="url">
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción Breve (Opcional)</label>
                            <textarea id="description" name="description" autocomplete="off"></textarea>
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
                    <form id="loginForm">
                        <div class="form-group">
                            <input type="text" id="loginUsuario" name="usuario" placeholder="Usuario" required autocomplete="username">
                        </div>
                        <div class="form-group password-wrapper">
                            <input type="password" id="loginPassword" name="password" placeholder="Contraseña" required autocomplete="current-password">
                            <button type="button" id="toggleLoginPassword" class="toggle-password">👁️</button>
                        </div>
                        <button type="submit" class="btn btn-primary">Acceder</button>
                        <p class="forgot-link">
                            <a href="#" class="forgot-password-link" id="openForgotPassword">¿Olvidaste tu contraseña?</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de recuperación de contraseña -->
    <div class="password-recovery-modal" id="passwordRecoveryModal">
        <div class="password-recovery-container">
            <button class="close-btn" id="closeRecoveryBtn">&times;</button>
            
            <!-- Paso 1: Ingresar correo -->
            <div class="recovery-step active" id="stepEmail">
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-circle">1</div>
                        <div class="step-text">Correo</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">2</div>
                        <div class="step-text">Código</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">3</div>
                        <div class="step-text">Contraseña</div>
                    </div>
                </div>
                
                <div class="modal-header">
                    <h2>Recuperar Contraseña</h2>
                    <p>Ingresa tu correo electrónico para recibir un código de verificación.</p>
                </div>
                
                <form id="emailForm">
                    <div class="form-group">
                        <label for="recoveryEmail">Correo Electrónico</label>
                        <input type="email" id="recoveryEmail" name="email" required autocomplete="email">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Código</button>
                </form>
                
                <div class="recovery-actions">
                    <button class="btn-link" id="backToLoginFromEmail">Volver al Login</button>
                </div>
            </div>
            
            <!-- Paso 2: Ingresar código -->
            <div class="recovery-step" id="stepCode">
                <div class="step-indicator">
                    <div class="step completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div>
                        <div class="step-text">Correo</div>
                    </div>
                    <div class="step active">
                        <div class="step-circle">2</div>
                        <div class="step-text">Código</div>
                    </div>
                    <div class="step">
                        <div class="step-circle">3</div>
                        <div class="step-text">Contraseña</div>
                    </div>
                </div>
                
                <div class="modal-header">
                    <h2>Verificar Código</h2>
                    <p>Hemos enviado un código de 6 dígitos a <span id="emailDisplay" class="font-weight-bold">usuario@ejemplo.com</span></p>
                </div>
                
                <form id="codeForm">
                    <div class="form-group">
                        <label for="verificationCode">Código de Verificación</label>
                        <input type="text" id="verificationCode" name="code" maxlength="6" required placeholder="000000" autocomplete="one-time-code">
                        <div class="countdown" id="countdown">05:00</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Verificar Código</button>
                </form>
                
                <div class="recovery-actions">
                    <button class="btn-link" id="resendCode">Reenviar código</button>
                    <button class="btn-link" id="backToEmail">Cambiar correo</button>
                </div>
            </div>
            
           <!-- Paso 3: Nueva contraseña -->
            <div class="recovery-step" id="stepNewPassword">
                <div class="step-indicator">
                    <div class="step completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div>
                        <div class="step-text">Correo</div>
                    </div>
                    <div class="step completed">
                        <div class="step-circle"><i class="fas fa-check"></i></div>
                        <div class="step-text">Código</div>
                    </div>
                    <div class="step active">
                        <div class="step-circle">3</div>
                        <div class="step-text">Contraseña</div>
                    </div>
                </div>

                <div class="modal-header">
                    <h2>Nueva Contraseña</h2>
                    <p>Crea una nueva contraseña para tu cuenta.</p>
                </div>

                <form id="passwordForm">
                    <!-- ✅ Campo oculto para accesibilidad -->
                    <input type="text" name="username" autocomplete="username" hidden>

                    <div class="form-group">
                        <label for="newPassword">Nueva Contraseña</label>
                        <input type="password" id="newPassword" name="password" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Contraseña</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </form>

                <div class="recovery-actions">
                    <button class="btn-link" id="backToCode">Volver al codigo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta flotante -->
    <div id="successAlert" class="success-alert"></div>
    <script src="../js/Registro.js"></script>

</body>
</html>
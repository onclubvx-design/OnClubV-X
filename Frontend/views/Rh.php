<?php
session_start();

// Proteger acceso: solo RH
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'rh') {
    header("Location: ../views/Registro.php?open=login"); 
    exit();
}

require_once '../../Backend/Config/conexion.php';
$database = new Database();
$db = $database->getConnection();
?>


<!-- El resto de tu código -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>On Club - Control de Asistencia</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏌️ Control de Asistencia - Fundación San Andrés</h1>
            <p>Sistema de registro de entrada y salida de caddies</p>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number" id="totalCaddies">5</div>
                <div class="stat-label">Caddies Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="activeCaddies">3</div>
                <div class="stat-label">Activos Ahora</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayEntries">8</div>
                <div class="stat-label">Entradas Hoy</div>
            </div>
        </div>

        <div class="main-content">
            <div class="form-section">
                <h2>📝 Registrar Entrada</h2>
                <form id="entradaForm">
                    <div class="Group">
                        <label for="caddieId">ID del Caddie *</label>
                        <div class="id-input-container">
                            <input type="text" id="caddieId" name="caddieId" placeholder="Ej: CD001" required>
                            <div class="id-hint">Ingresa el ID único del caddie</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        🚪 Registrar Entrada
                    </button>
                </form>
                
                <div id="successMessage" class="success-message"></div>
                <div id="errorMessage" class="error-message"></div>
                <div id="autoCloseNotification" class="auto-close-notification"></div>
            </div>

            <div class="caddies-section">
                <h2>👥 Control de Caddies</h2>

                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Buscar por nombre o ID...">
                </div>

                <div id="caddiesList">
                    <!-- Caddie cards will be inserted here -->
                </div>
            </div>
        </div>

        <!-- Modal para historial completo -->
        <div id="historyModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>📊 Historial Completo</h3>
                    <span class="close" onclick="closeHistoryModal()">&times;</span>
                </div>
                <div id="modalContent">
                    <!-- Historial completo se insertará aquí -->
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

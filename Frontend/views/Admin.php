<?php
session_start();

// Proteger acceso: solo admins
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../views/Registro.php?open=login"); 
    exit();
}

require_once '../../Backend/Config/conexion.php';

// Crear conexión con la BD usando la clase Conexion
$conexion = new Conexion();
$conn = $conexion->conectar();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../css/Admin.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Panel de Administración</h1>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Caddies Activos</h3>
                <p id="active-caddies">0</p>
            </div>
            <div class="stat-card">
                <h3>Total Caddies</h3>
                <p id="total-caddies">0</p>
            </div>
            <div class="stat-card">
                <h3>Reportes Mensuales</h3>
                <p id="total-reports">0</p>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="gestion">Gestión de Caddies</button>
            <button class="tab-btn" data-tab="asistencias">Control de Asistencias</button>
            <button class="tab-btn" data-tab="historial">Historial de Asistencias</button>
            <button class="tab-btn" data-tab="reportes">Reportes</button>
        </div>

        <div class="tab-content">
            <!-- Gestión de Caddies -->
            <div id="gestion" class="tab-pane active">
                <h2>Gestión de Caddies</h2>
                <form id="caddie-form">
                    <input type="hidden" id="caddie-id">
                    <div class="form-row">
                        <input type="text" id="nombre" placeholder="Nombre" required>
                        <input type="text" id="apellido" placeholder="Apellido" required>
                    </div>
                    <div class="form-row">
                        <input type="number" id="edad" placeholder="Edad" required min="16">
                        <input type="text" id="telefono" placeholder="Teléfono" required>
                    </div>
                    <div class="form-row">
                        <input type="text" id="documento" placeholder="Documento de Identidad" required>
                        <select id="tipo" required>
                            <option value="">Seleccione tipo</option>
                            <option value="tenis">Tenis</option>
                            <option value="golf">Golf</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <input type="email" id="correo" placeholder="Correo Electrónico (opcional)">
                    </div>
                    <button type="submit" id="btn-submit">Agregar Caddie</button>
                </form>

                <div class="caddies-list">
                    <h3>Lista de Caddies</h3>
                    <input type="text" id="search-caddie" placeholder="Buscar caddie...">
                    <table id="caddies-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los caddies se cargarán aquí mediante JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Control de Asistencias -->
            <div id="asistencias" class="tab-pane">
                <h2>Control de Asistencias</h2>
                <div class="attendance-control">
                    <input type="text" id="search-attendance" placeholder="Buscar por nombre, documento o código">
                    <button onclick="searchCaddie()">Buscar</button>
                    
                    <div id="attendance-result" style="display:none;">
                        <h3 id="result-name"></h3>
                        <p id="result-info"></p>
                        <button id="btn-entrada" onclick="registrarEntrada()">Registrar Entrada</button>
                        <button id="btn-salida" onclick="registrarSalida()">Registrar Salida</button>
                    </div>
                </div>
            </div>

            <!-- Historial de Asistencias -->
            <div id="historial" class="tab-pane">
                <h2>Historial de Asistencias</h2>
                
                <div class="filtros-historial">
                    <select id="filtro-caddie">
                        <option value="all">Todos los caddies</option>
                        <!-- Se llenará dinámicamente -->
                    </select>
                    
                    <input type="date" id="filtro-fecha" value="<?php echo date('Y-m-d'); ?>">
                    
                    <button onclick="cargarHistorial()">Buscar</button>
                    <button onclick="exportarHistorial()">Exportar CSV</button>
                </div>
                
                <div class="historial-container">
                    <table id="tabla-historial">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Fecha Entrada</th>
                                <th>Hora Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Hora Salida</th>
                                <th>Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reportes -->
            <div id="reportes" class="tab-pane">
                <h2>Reportes Mensuales</h2>
                <div class="report-controls">
                    <select id="report-month">
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <select id="report-year">
                        <!-- Los años se cargarán dinámicamente -->
                    </select>
                    <select id="report-caddie">
                        <option value="all">Todos los caddies</option>
                        <!-- Los caddies se cargarán dinámicamente -->
                    </select>
                    <button onclick="generateReport()">Generar Reporte</button>
                    <button onclick="exportPDF()">Exportar PDF</button>
                    <button onclick="exportExcel()">Exportar Excel</button>
                </div>
                <div id="report-results">
                    <!-- Los resultados del reporte aparecerán aquí -->
                </div>
            </div>
        </div>
    </div>

    <script src="../js/Admin.js"></script>
</body>
</html>

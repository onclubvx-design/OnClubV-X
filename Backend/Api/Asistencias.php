<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/conexion.php';


class Asistencias {
    private $db;
    private $table_name = "asistencias";

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Registrar entrada
    public function registrarEntrada($caddie_id) {
        try {
            // Verificar si el caddie existe y está activo
            $query_check = "SELECT id, activo FROM caddies WHERE id = ?";
            $stmt_check = $this->db->prepare($query_check);
            $stmt_check->bindParam(1, $caddie_id);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() == 0) {
                return array("success" => false, "message" => "Caddie no encontrado");
            }
            
            $caddie = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($caddie['activo']) {
                return array("success" => false, "message" => "El caddie ya está activo (ya registró entrada)");
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            // Registrar entrada
            $query = "INSERT INTO " . $this->table_name . " (caddie_id, fecha_entrada) VALUES (?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $caddie_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al registrar entrada: " . implode(", ", $stmt->errorInfo()));
            }
            
            // Actualizar estado del caddie a activo
            $query_update = "UPDATE caddies SET activo = 1 WHERE id = ?";
            $stmt_update = $this->db->prepare($query_update);
            $stmt_update->bindParam(1, $caddie_id);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Error al actualizar estado: " . implode(", ", $stmt_update->errorInfo()));
            }
            
            $this->db->commit();
            
            return array("success" => true, "message" => "Entrada registrada exitosamente");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return array("success" => false, "message" => $e->getMessage());
        }
    }

    // Registrar salida
    public function registrarSalida($caddie_id) {
        try {
            // Verificar si el caddie existe y está inactivo
            $query_check = "SELECT id, activo FROM caddies WHERE id = ?";
            $stmt_check = $this->db->prepare($query_check);
            $stmt_check->bindParam(1, $caddie_id);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() == 0) {
                return array("success" => false, "message" => "Caddie no encontrado");
            }
            
            $caddie = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if (!$caddie['activo']) {
                return array("success" => false, "message" => "El caddie no está activo (no ha registrado entrada)");
            }
            
            // Obtener el último registro de entrada sin salida
            $query_find = "SELECT id FROM " . $this->table_name . " 
                          WHERE caddie_id = ? AND fecha_salida IS NULL 
                          ORDER BY fecha_entrada DESC LIMIT 1";
            $stmt_find = $this->db->prepare($query_find);
            $stmt_find->bindParam(1, $caddie_id);
            $stmt_find->execute();
            
            if ($stmt_find->rowCount() == 0) {
                return array("success" => false, "message" => "No se encontró registro de entrada para este caddie");
            }
            
            $asistencia = $stmt_find->fetch(PDO::FETCH_ASSOC);
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            // Registrar salida
            $query = "UPDATE " . $this->table_name . " SET fecha_salida = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $asistencia['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al registrar salida: " . implode(", ", $stmt->errorInfo()));
            }
            
            // Actualizar estado del caddie a inactivo
            $query_update = "UPDATE caddies SET activo = 0 WHERE id = ?";
            $stmt_update = $this->db->prepare($query_update);
            $stmt_update->bindParam(1, $caddie_id);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Error al actualizar estado: " . implode(", ", $stmt_update->errorInfo()));
            }
            
            $this->db->commit();
            
            return array("success" => true, "message" => "Salida registrada exitosamente");
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return array("success" => false, "message" => $e->getMessage());
        }
    }

    // Obtener historial de asistencias
    public function obtenerHistorial($fecha = null, $caddie_id = null) {
        $query = "SELECT a.*, c.codigo_unico, c.nombre, c.apellido 
                  FROM " . $this->table_name . " a 
                  INNER JOIN caddies c ON a.caddie_id = c.id 
                  WHERE 1=1";
        
        $params = array();
        
        if ($fecha) {
            $query .= " AND DATE(a.fecha_entrada) = ?";
            $params[] = $fecha;
        }
        
        if ($caddie_id && $caddie_id != 'all') {
            $query .= " AND a.caddie_id = ?";
            $params[] = $caddie_id;
        }
        
        $query .= " ORDER BY a.fecha_entrada DESC";
        
        $stmt = $this->db->prepare($query);
        
        if ($params) {
            foreach ($params as $key => $value) {
                $stmt->bindValue($key + 1, $value);
            }
        }
        
        $stmt->execute();
        
        $historial = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historial[] = $row;
        }
        
        return $historial;
    }
}

// Procesar la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$asistencias = new Asistencias();

// Obtener datos JSON del cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'entrada':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($input['caddie_id'])) {
                $result = $asistencias->registrarEntrada($input['caddie_id']);
                echo json_encode($result);
            } else {
                echo json_encode(array("success" => false, "message" => "Falta el ID del caddie"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Método no permitido"));
        }
        break;
        
    case 'salida':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($input['caddie_id'])) {
                $result = $asistencias->registrarSalida($input['caddie_id']);
                echo json_encode($result);
            } else {
                echo json_encode(array("success" => false, "message" => "Falta el ID del caddie"));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Método no permitido"));
        }
        break;
        
    case 'historial':
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $caddie_id = isset($_GET['caddie_id']) ? $_GET['caddie_id'] : null;
        
        $historial = $asistencias->obtenerHistorial($fecha, $caddie_id);
        echo json_encode($historial);
        break;
        
    default:
        echo json_encode(array(
            "success" => false, 
            "message" => "Acción no válida", 
            "acciones_disponibles" => ["entrada", "salida", "historial"]
        ));
        break;
}
?>
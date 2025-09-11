<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../config/conexion.php");

class Caddies {
    private $db;
    private $table_name = "caddies";

    public function __construct() {
        $this->db = Conexion::conectar();
        if(!$this->db){
            die("❌ Error: No se pudo conectar a la BD");
        }
    }
}


    // Generar código único numérico secuencial (001, 002, 003, ...)
private function generateUniqueCode() {
    // Obtener el último código numérico usado
    $query = "SELECT codigo_unico FROM " . $this->table_name . " 
              WHERE codigo_unico REGEXP '^[0-9]+$' 
              ORDER BY CAST(codigo_unico AS UNSIGNED) DESC 
              LIMIT 1";
    
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    
    $lastCode = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lastCode && isset($lastCode['codigo_unico'])) {
        // Incrementar el último código
        $newCode = (int)$lastCode['codigo_unico'] + 1;
    } else {
        // Primer código
        $newCode = 1;
    }
    
    // Formatear con ceros a la izquierda (3 dígitos)
    $formattedCode = str_pad($newCode, 3, '0', STR_PAD_LEFT);
    
    // Verificar que el código no exista (por si acaso)
    $queryCheck = "SELECT id FROM " . $this->table_name . " WHERE codigo_unico = ?";
    $stmtCheck = $this->db->prepare($queryCheck);
    $stmtCheck->bindParam(1, $formattedCode);
    $stmtCheck->execute();
    
    if ($stmtCheck->rowCount() > 0) {
        // Si por alguna razón existe, buscar el siguiente disponible
        return $this->findNextAvailableCode($newCode);
    }
    
    return $formattedCode;
}

// Función auxiliar para encontrar el siguiente código disponible
private function findNextAvailableCode($startFrom) {
    $code = $startFrom + 1;
    $maxAttempts = 1000; // Límite de seguridad
    
    for ($i = 0; $i < $maxAttempts; $i++) {
        $formattedCode = str_pad($code, 3, '0', STR_PAD_LEFT);
        
        $query = "SELECT id FROM " . $this->table_name . " WHERE codigo_unico = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $formattedCode);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return $formattedCode;
        }
        
        $code++;
    }
    
    // Si no encuentra después de muchos intentos, generar aleatorio como fallback
    return str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}
    // Obtener estadísticas
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(activo) as active 
                  FROM " . $this->table_name;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array(
            "total" => $row['total'] ?? 0,
            "active" => $row['active'] ?? 0
        );
    }

    // Obtener caddie por ID
    public function get($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

        // Obtener todos los caddies
    public function getAll(){
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    // Buscar caddies
    public function search($term) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE nombre LIKE ? OR apellido LIKE ? OR documento_identidad LIKE ? OR codigo_unico LIKE ?
                  ORDER BY nombre, apellido";
        
        $stmt = $this->db->prepare($query);
        $term = "%$term%";
        $stmt->bindParam(1, $term);
        $stmt->bindParam(2, $term);
        $stmt->bindParam(3, $term);
        $stmt->bindParam(4, $term);
        $stmt->execute();
        
        $caddies = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $caddies[] = $row;
        }
        
        return $caddies;
    }

    // Crear caddie
    public function create($data) {
        $code = $this->generateUniqueCode();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  SET codigo_unico=:codigo_unico, nombre=:nombre, apellido=:apellido, 
                  edad=:edad, telefono=:telefono, documento_identidad=:documento_identidad, 
                  tipo=:tipo, correo=:correo";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":codigo_unico", $code);
        $stmt->bindParam(":nombre", $data['nombre']);
        $stmt->bindParam(":apellido", $data['apellido']);
        $stmt->bindParam(":edad", $data['edad']);
        $stmt->bindParam(":telefono", $data['telefono']);
        $stmt->bindParam(":documento_identidad", $data['documento_identidad']);
        $stmt->bindParam(":tipo", $data['tipo']);
        $stmt->bindParam(":correo", $data['correo']);
        
        if ($stmt->execute()) {
            return array("success" => true, "message" => "Caddie creado exitosamente");
        }
        
        return array("success" => false, "message" => "Error al crear caddie: " . implode(", ", $stmt->errorInfo()));
    }

    // Actualizar caddie
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre=:nombre, apellido=:apellido, edad=:edad, 
                  telefono=:telefono, documento_identidad=:documento_identidad, 
                  tipo=:tipo, correo=:correo 
                  WHERE id=:id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(":nombre", $data['nombre']);
        $stmt->bindParam(":apellido", $data['apellido']);
        $stmt->bindParam(":edad", $data['edad']);
        $stmt->bindParam(":telefono", $data['telefono']);
        $stmt->bindParam(":documento_identidad", $data['documento_identidad']);
        $stmt->bindParam(":tipo", $data['tipo']);
        $stmt->bindParam(":correo", $data['correo']);
        $stmt->bindParam(":id", $data['id']);
        
        if ($stmt->execute()) {
            return array("success" => true, "message" => "Caddie actualizado exitosamente");
        }
        
        return array("success" => false, "message" => "Error al actualizar caddie: " . implode(", ", $stmt->errorInfo()));
    }

    // Eliminar caddie
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            return array("success" => true, "message" => "Caddie eliminado exitosamente");
        }
        
        return array("success" => false, "message" => "Error al eliminar caddie: " . implode(", ", $stmt->errorInfo()));
    }
}

// Procesar la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

$caddies = new Caddies();

switch ($action) {
    case 'stats':
        $stats = $caddies->getStats();
        echo json_encode($stats);
        break;
        
    case 'get':
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        if ($id) {
            $caddie = $caddies->get($id);
            echo json_encode($caddie);
        } else {
            echo json_encode(array("error" => "ID no especificado"));
        }
        break;
        
    case 'getAll':
        $stmt = $caddies->getAll();
        $caddies_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $caddies_arr[] = $row;
        }
        echo json_encode($caddies_arr);
        break;

    case 'search':
        $term = isset($_GET['term']) ? $_GET['term'] : '';
        $results = $caddies->search($term);
        echo json_encode($results);
        break;
        
    case 'create':
        $data = $_POST;
        $result = $caddies->create($data);
        echo json_encode($result);
        break;
        
    case 'update':
        $data = $_POST;
        $result = $caddies->update($data);
        echo json_encode($result);
        break;
        
    case 'delete':
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $result = $caddies->delete($id);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(array("message" => "Acción no válida", "acciones_disponibles" => ["stats", "get", "getAll", "search", "create", "update", "delete"]));
        break;
}
?>

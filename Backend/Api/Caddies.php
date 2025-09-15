<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    $db = new Database();
    $this->db = $db->getConnection(); // Asignación correcta

    if (!$this->db) {
        die(json_encode(["error" => "No se pudo conectar a la base de datos."]));
    }
}

    private function generateUniqueCode() {
        $query = "SELECT codigo_unico FROM {$this->table_name} 
                  WHERE codigo_unico REGEXP '^[0-9]+$' 
                  ORDER BY CAST(codigo_unico AS UNSIGNED) DESC LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $lastCode = $stmt->fetch(PDO::FETCH_ASSOC);

        $newCode = ($lastCode && isset($lastCode['codigo_unico']))
            ? (int)$lastCode['codigo_unico'] + 1
            : 1;

        $formattedCode = str_pad($newCode, 3, '0', STR_PAD_LEFT);

        $queryCheck = "SELECT id FROM {$this->table_name} WHERE codigo_unico = ?";
        $stmtCheck = $this->db->prepare($queryCheck);
        $stmtCheck->bindParam(1, $formattedCode);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            return $this->findNextAvailableCode($newCode);
        }

        return $formattedCode;
    }

    private function findNextAvailableCode($startFrom) {
        $code = $startFrom + 1;
        $maxAttempts = 1000;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $formattedCode = str_pad($code, 3, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare("SELECT id FROM {$this->table_name} WHERE codigo_unico = ?");
            $stmt->bindParam(1, $formattedCode);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return $formattedCode;
            }
            $code++;
        }

        return str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    public function getStats() {
        $query = "SELECT COUNT(*) as total, SUM(activo) as active FROM {$this->table_name}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "total" => $row['total'] ?? 0,
            "active" => $row['active'] ?? 0
        ];
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table_name} WHERE id = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table_name}");
        $stmt->execute();
        return $stmt;
    }

    public function search($term) {
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE nombre LIKE ? OR apellido LIKE ? OR documento_identidad LIKE ? OR codigo_unico LIKE ?
                  ORDER BY nombre, apellido";

        $stmt = $this->db->prepare($query);
        $searchTerm = "%$term%";
        for ($i = 1; $i <= 4; $i++) {
            $stmt->bindParam($i, $searchTerm);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $code = $this->generateUniqueCode();

        $query = "INSERT INTO {$this->table_name} 
                  SET codigo_unico=:codigo_unico, nombre=:nombre, apellido=:apellido, edad=:edad, 
                      telefono=:telefono, documento_identidad=:documento_identidad, tipo=:tipo, correo=:correo";

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
            return ["success" => true, "message" => "Caddie creado exitosamente"];
        }

        return ["success" => false, "message" => "Error al crear caddie: " . implode(", ", $stmt->errorInfo())];
    }

    public function update($data) {
        $query = "UPDATE {$this->table_name} 
                  SET nombre=:nombre, apellido=:apellido, edad=:edad, telefono=:telefono, 
                      documento_identidad=:documento_identidad, tipo=:tipo, correo=:correo 
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
            return ["success" => true, "message" => "Caddie actualizado exitosamente"];
        }

        return ["success" => false, "message" => "Error al actualizar caddie: " . implode(", ", $stmt->errorInfo())];
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table_name} WHERE id = ?");
        $stmt->bindParam(1, $id);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Caddie eliminado exitosamente"];
        }

        return ["success" => false, "message" => "Error al eliminar caddie: " . implode(", ", $stmt->errorInfo())];
    }
}

// ===== API ROUTER ===== //
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$caddies = new Caddies();

switch ($action) {
    case 'stats':
        echo json_encode($caddies->getStats());
        break;

    case 'get':
        $id = $_GET['id'] ?? null;
        echo json_encode($id ? $caddies->get($id) : ["error" => "ID no especificado"]);
        break;

    case 'getAll':
        $stmt = $caddies->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'search':
        $term = $_GET['term'] ?? '';
        echo json_encode($caddies->search($term));
        break;

    case 'create':
        $data = $_POST;
        echo json_encode($caddies->create($data));
        break;

    case 'update':
        $data = $_POST;
        echo json_encode($caddies->update($data));
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        echo json_encode($id ? $caddies->delete($id) : ["error" => "ID no especificado"]);
        break;

    default:
        echo json_encode([
            "message" => "Acción no válida",
            "acciones_disponibles" => ["stats", "get", "getAll", "search", "create", "update", "delete"]
        ]);
        break;
}
?>

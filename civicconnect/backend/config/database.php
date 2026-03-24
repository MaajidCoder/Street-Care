<?php
// backend/config/database.php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Use configured host, user, pass, db, and PORT
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            // If connection failed, it might be due to exception not being thrown for mysqli sometimes, check error
            if ($this->connection->connect_error) {
                // Return JSON error and exit
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => "Connection failed: " . $this->connection->connect_error]);
                exit();
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            // Fallback
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'error' => "Database connection error: " . $e->getMessage()]);
             exit();
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => "Query preparation failed: " . $this->connection->error]);
            exit();
        }
        
        if (!empty($params)) {
            $types = '';
            foreach($params as $param) {
                if(is_int($param)) $types .= 'i';
                elseif(is_double($param)) $types .= 'd';
                else $types .= 's';
            }
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return [];
        
        $result = $stmt->get_result();
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        $stmt->close();
        return $rows;
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return false;
        
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if (!$stmt) return false;
        
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
}
// End of file

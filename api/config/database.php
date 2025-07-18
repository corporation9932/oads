<?php
class Database {
    private $host = "localhost";
    private $db_name = "raspadinha_db";
    private $username = "root";
    private $password = "";
    private $charset = "utf8mb4";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
    
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return ["status" => "success", "message" => "Conexión exitosa a la base de datos"];
            }
        } catch(Exception $e) {
            return ["status" => "error", "message" => "Error de conexión: " . $e->getMessage()];
        }
    }
}

// Configuración de la API
class ApiConfig {
    const BASE_URL = 'http://localhost/api';
    const NITRO_API_TOKEN = 'AJTQzn8xWuYXrjNu5XWajspWi8i6sd9XzkgEViaDpkIrwyKRKCkC1fHCFY1P';
    const NITRO_OFFER_HASH = 'ydpamubeay';
    const NITRO_PRODUCT_HASH = '8cru5klgqv';
    const NITRO_ENDPOINT = 'https://api.nitropagamentos.com/api/public/v1/transactions';
    
    public static function getNitroUrl() {
        return self::NITRO_ENDPOINT . '?api_token=' . self::NITRO_API_TOKEN;
    }
}
?>
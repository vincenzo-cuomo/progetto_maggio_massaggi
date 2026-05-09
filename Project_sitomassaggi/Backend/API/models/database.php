<?php
namespace API\Models;
class database extends \PDO
{
    private static $istanza;
    private $conn;

    function __construct()
    {
        $serverName = $_SERVER['DB_SERVER'] ?? '';
        $database = $_SERVER['DB_NAME'] ?? '';
        $username = $_SERVER['DB_USER'] ?? '';
        $password = $_SERVER['DB_PASSWORD'] ?? '';
        try {
            $this->conn = $this->connect("sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true;", $username, $password);
        } catch (\PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi"]);
            exit;
        }
    }

    public static function getInstance(): static {
        if (self::$istanza == null) {
            self::$istanza = new database();
        }
        return self::$istanza;
    }

    public function getConnection(){
        return $this->conn;
    }
}
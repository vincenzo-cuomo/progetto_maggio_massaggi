<?php

namespace API\Models;

use PDO;

class database extends \PDO
{
    private static $istanza;
    private $conn;

    function __construct()
    {
        $serverName = getenv('DB_SERVER');
        $database = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        try {
            $this->conn= new PDO("pgsql:host=$serverName;port=17864;dbname=$database;user=$username;password=$password;sslmode=verify-ca;sslrootcert=./ca.pem");
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi", "error"=>$e->getMessage()]);
            exit;
        }
    }

    public static function getInstance(): static
    {
        if (self::$istanza == null) {
            self::$istanza = new database();
        }
        return self::$istanza;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

<?php



class database extends PDO
{

    private readonly string $serverName;
    private readonly string $database;
    private readonly string $username;
    private readonly string $password;
    private PDO $conn;

    function __construct($trustServerCertificate = true, bool $encrypt = false, int $loginTimeout = 3)
    {
        $this->serverName = getenv('DB_SERVER') ?? '';
        $this->database = getenv('DB_NAME') ?? '';
        $this->username = getenv('DB_USER') ?? '';
        $this->password = getenv('DB_PASSWORD') ?? '';
        $this->conn = $this->dbConnect($trustServerCertificate, $encrypt, $loginTimeout);
    }

    public function dbConnect($trustServerCertificate = true, bool $encrypt = false, int $loginTimeout = 3)
    {
        if (
            empty($this->serverName) || empty($this->database) || empty($this->username) || empty($this->password)
        ) {
            throw new Exception("Variabili DB mancanti");
            exit;
        }
        $trustServerCertificate = $trustServerCertificate ? "yes" : "no";
        $encrypt = $encrypt ? "yes" : "no";
        $loginTimeout = (string) $loginTimeout;
        $connectionString = "sqlsrv:Server=$this->serverName;Database=$this->database;TrustServerCertificate=$trustServerCertificate;Encrypt=$encrypt;LoginTimeout=$loginTimeout";
        try {
            $this->connect($connectionString, $this->username, $this->password);
        } catch (PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi"]);
            exit;
        }

        return $this->connect($connectionString, $this->username, $this->password);
    }
}

<?php

namespace API\Controllers;

use API\Models\database;
use API\middleware\jwtCentre as jwt;

class User
{
    private $db;

    public function __construct()
    {
        $database = database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getUserName($jwtToken)
    {
        $jwt = new jwt;
        $decoded = $jwt->jwtValidator($jwtToken);
        $sql = "SELECT NOME FROM sitoMassaggiDB.dbo.userAccount WHERE IDUTENTE = :idutente";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':idutente', $decoded['sub']);
            $stmt->execute();
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e]);
            exit;
        }
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        http_response_code(200);
        header("Content-Type: application/json");
        echo json_encode(["username" => $rows["NOME"]]);
        exit;
    }

    function verifyLogin(string $email, string $password)
    {
        header("Access-Control-Expose-Headers: Authorization");
        $sql = "SELECT PASSWORDUSER, IDUTENTE, UpdatedAt FROM sitoMassaggiDB.dbo.userAccount WHERE EMAIL = :email";

        $db = database::getInstance();
        if ($this->db) {
            try {
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':email', $email);
                $stmt->execute();
            } catch (\PDOException $e) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Sono state inserite una passwordd o mail sbagliata", "error" => $e->getMessage()]);
                exit;
            }

            $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($rows) {
                if (password_verify(htmlspecialchars($password), $rows["PASSWORDUSER"])) {
                    $jwt = new jwt;
                    $userId = $rows['IDUTENTE'];
                    $payload = ["sub" => $userId, "iat" => time(), "exp" => time() + 3600]; #jwt payload
                    http_response_code(200);
                    header("Content-Type: application/json");
                    header("Cache-Control: no-cache, private");
                    header("Authorization: Bearer " . $jwt->jwtCreate($payload));
                    echo json_encode(["success" => true]);
                } else {
                    http_response_code(400);
                    header("Content-Type: application/json");
                    echo json_encode(["success" => false, "description" => "Sono state inserite una password o mail sbagliata"]);
                    exit();
                }
            } else {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Sono state inserite una password o mail sbagliata"]);
                exit;
            }
        } else {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi"]);
            exit;
        }
    }

    function signup(string $name, int $age, string $tel, string $email, string $password)
    {
        $password = htmlspecialchars($password);
        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 9]);
        try {
            $sql = "INSERT INTO sitoMassaggiDB.dbo.userAccount (NOME, ETA, TEL, EMAIL, PASSWORDUSER) VALUES(:name, :age, :tel, :email, :password)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':name' => $name, ':age' => $age, ':tel' => $tel, ':email' => $email, ':password' => $password]);
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["success" => true, "Description" => "L'user è stato registrato correttamente"]);
            exit;
        } catch (\PDOException $e) {
            if ($e->getCode() == 23505) {

                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "Description" => "L'email è associata a un altro account"]);
            } else {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "Description" => $e]);
            }
        }
    }
}

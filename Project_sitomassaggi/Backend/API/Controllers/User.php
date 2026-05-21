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
        $sql = "SELECT NOME FROM userAccount WHERE IDUTENTE = :idutente";
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

        try {
            $sql = "SELECT PASSWORDUSER, IDUTENTE, UpdatedAt FROM userAccount WHERE EMAIL = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Sono state inserite una passwordd o mail sbagliata", "error" => $e->getMessage()]);
            exit;
        }

        if ($rows) {
            if (password_verify(htmlspecialchars($password), $rows["passworduser"])) {
                $jwt = new jwt;
                $userId = $rows['idutente'];
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
    }

    function signup(string $name, int $age, string $tel, string $email, string $password)
    {
        $password = htmlspecialchars($password);
        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 9]);
        try {
            $sql = "INSERT INTO userAccount (NOME, ETA, TEL, EMAIL, PASSWORDUSER) VALUES(:name, :age, :tel, :email, :password)";
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

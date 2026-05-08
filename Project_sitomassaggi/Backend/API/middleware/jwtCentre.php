<?php

namespace API\middleware;

require_once __DIR__ . '/../../vendor/autoload.php';
# $payload = ["userID" => $userId, "iat" => time() , "exp" => time() + 3600];

use Firebase\JWT\Key;
use API\Models\database;
use Firebase\JWT\JWT;




class jwtCentre
{
    public function getUserName($userID)
    {
        $db = new database();
        $db = $db->dbConnect();
        $sql = "SELECT NOME FROM sitoMassaggiDB.dbo.userAccount WHERE IDUTENTE = :idutente";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':idutente', $userID);
            $stmt->execute();
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e]);
            exit;
        }
        $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($rows) {
            $username = $rows["NOME"];
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["username" => $username]);
            exit;
        }
    }

    public function jwtCreate(array $payload)
    {
        $key = $_ENV['JWT_KEY'];
        try {
            $jwt = JWT::encode($payload, $key, 'HS256');
            return $jwt;
        } catch (\Throwable $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e]);
            exit;
        }
    }

    public function jwtValidator(string $jwt, bool $getUserName = false)
    {
        $jwt = trim($jwt, ' ');
        $key = $_ENV['JWT_KEY'];
        try {
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            $decoded = (array) $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            header("Content-Type: application/json");
            header('WWW-Authenticate: Bearer authorization_uri="http://localhost:3080/login"');
            echo json_encode(["error"=>"Expired JWT token"]);
            exit;
        }


        if ($getUserName) {
            $this->getUserName($decoded['userId']);
            exit;
        }
        return $decoded;
    }
}

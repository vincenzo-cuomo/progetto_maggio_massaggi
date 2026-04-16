<?php
require_once __DIR__ . '/../../vendor/autoload.php';
# $payload = ["userID" => $userId, "iat" => time() , "exp" => time() + 3600];

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function jwtCreator($payload)
{
    $key = getenv('JWT_KEY');
    $jwt = JWT::encode($payload, $key, 'HS256');
    return $jwt;
}


function jwtValidator($jwt)
{
    $header = new stdClass();
    $key = getenv('JWT_KEY');
    $decoded = JWT::decode($jwt, new Key($key, 'HS256'), $header);
    $decoded = (array) $decoded;
    if ($decoded["exp"] < time()) {
        http_response_code(401);
        header("Content-Type: application/json");
        header('WWW-Authenticate: Bearer authorization_uri="http://localhost:3080/login", error="Expired JWT token"');
        exit;
    } else {
        http_response_code(200);
        require __DIR__ . "/../models/conf.php";
        $db = dbConnection();
        $sql = "SELECT NOME FROM sitoMassaggiDB.dbo.userAccount WHERE IDUTENTE = :idutente";
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':idutente', $decoded["userID"]);
            $stmt->execute();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e]);
            exit;
        }
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rows) {
            $username = $rows["NOME"];
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["username" => $username]);
        }


        exit;
    }
}



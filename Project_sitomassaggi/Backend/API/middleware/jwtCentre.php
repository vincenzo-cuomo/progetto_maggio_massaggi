<?php

namespace API\middleware;
# $payload = ["sub" => $userId, "iat" => time() , "exp" => time() + 3600];

use Firebase\JWT\Key;
use API\Models\database;
use Firebase\JWT\JWT;




class jwtCentre
{
    private $db;

    public function __construct() {
        $database = database::getInstance();
        $this->db = $database->getConnection();
    }

    public function jwtCreate(array $payload)
    {
        $key = getenv('JWT_KEY');
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

    public function jwtValidator(string $jwt)
    {

        $jwt = trim($jwt, ' ');
        $key =  getenv('JWT_KEY');

        try {
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            $decoded = (array) $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            header("Content-Type: application/json");
            header('WWW-Authenticate: Bearer authorization_uri="http://localhost:3080/login"');
            echo json_encode(["error" => "Expired JWT token"]);
            exit;
        }
        return $decoded;
    }
}

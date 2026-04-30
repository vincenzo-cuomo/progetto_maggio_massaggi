<?php

namespace API\Controllers;
use API\models\database;

class signUp
{
    function signup(string $name, string $age, string $tel, string $email, string $password)
    {
        $age = (int) $age;
        $pass = htmlspecialchars($password);
        $db = new database();
        if (!$db->dbConnect()) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode([
                "success" => false,
                "description" => "Connessione al db fallita",
            ]);
            exit;
        }
        $db = $db->dbConnect();
        $pass = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO sitoMassaggiDB.dbo.userAccount (NOME, ETA, TEL, EMAIL, PASSWORDUSER) VALUES(:name, :age, :tel, :email, :password)";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':name' => $name, ':age' => $age, ':tel' => $tel, ':email' => $email, ':password' => $pass]);
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["success" => true, "Description" => "L'user è stato registrato correttamente"]);
            exit;
        } catch (\PDOException $e) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "Description" => "$e"]);
        }
    }
}

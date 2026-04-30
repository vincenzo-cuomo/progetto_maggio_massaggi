<?php

namespace API\Controllers;
use API\Models\database;

class Login
{
    function verifyLogin(string $email, string $password)
    {
        header("Access-Control-Expose-Headers: Authorization");
        $password = htmlspecialchars($password);
        $sql = "SELECT PASSWORDUSER, IDUTENTE, UpdatedAt FROM sitoMassaggiDB.dbo.userAccount WHERE EMAIL = :email";

        $db = new database();

        if ($db->dbConnect()) {
            $db = $db->dbConnect();
            try {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':email', $email);
                $stmt->execute();
            } catch (\PDOException $e) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Sono state inserite una password o mail sbagliata", "error" => $e->getMessage()]);
                exit;
            }

            $rows = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($rows) {
                if (password_verify($password, $rows["PASSWORDUSER"])) {
                    require __DIR__ . "/../middleware/jwtValidator.php";
                    $userId = $rows['IDUTENTE'];
                    $payload = ["userID" => $userId, "iat" => time(), "exp" => time() + 3600]; #jwt payload
                    http_response_code(200);
                    header("Content-Type: application/json");
                    header("Cache-Control: no-cache, private");
                    header("Authorization: Bearer " . jwtCreator($payload));
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
}

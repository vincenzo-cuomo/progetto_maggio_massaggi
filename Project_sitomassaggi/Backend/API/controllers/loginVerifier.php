<?php
function verifyLogin($email, $password)
{
    header("Access-Control-Expose-Headers: Authorization");
    $password = htmlspecialchars($password);
    require __DIR__ . "/../models/conf.php";
    $sql = "SELECT PASSWORDUSER, IDUTENTE, NOME, UpdatedAt FROM sitoMassaggiDB.dbo.userAccount WHERE EMAIL = :email";
    if ($db = dbConnection()) {
        try {
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
        } catch (PDOException $e) {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Sono state inserite una password o mail sbagliata", "error" => $e->getMessage()]);
            exit;
        }

        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rows) {
            if (password_verify($password, $rows["PASSWORDUSER"])) {
                require __DIR__ . "/../middleware/jwtValidator.php";
                $userId = $rows['IDUTENTE'];
                $name = $rows['NOME'];
                #$etag = md5(json_encode([$rows['UpdatedAt'], $rows['IDUTENTE']]));  inutile e sostituito da jwt
                $payload = ["userID" => $userId, "iat" => time() , "exp" => time() + 3600]; #jwt payload
                http_response_code(200);
                header("Content-Type: application/json");
                header("Cache-Control: no-cache, private");
                #header("ETag: \"$etag\""); inutile e sostituito da jwt
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
    }
}

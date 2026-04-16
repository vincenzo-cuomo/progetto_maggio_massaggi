<?php
function ETagValidator($JWT)
{
    require __DIR__ . "/../models/conf.php";
    require __DIR__ . "/jwtValidator.php";
    $eTag = $_SERVER["HTTP_IF_NONE_MATCH"];


    if ($userId = jwtValidator($JWT)) {
        $IDUtente = $userId['userID'];
        if ($db = dbConnection()) {
            $sql = "SELECT UpdatedAt FROM sitoMassaggiDB.dbo.userAccount WHERE IDUTENTE = :idutente";
            try {
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':idutente', $IDUtente);
                $stmt->execute();
            } catch (PDOException $e) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "error" => $e->getMessage()]);
                exit;
            }
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $dbETag =  md5(json_encode([$rows['UpdatedAt'], $rows['IDUTENTE']]));
        }

        if (isset($eTag) && $eTag === $dbETag) {
            http_response_code(304);
        } else {
            http_response_code(400);
            header("Content-Type: application/json");
            echo (json_encode(["success" => false, "error" => "ETag hasn't been given"]));
        }
    } else {
        http_response_code(400);
        header("Content-Type: application/json");
        echo (json_encode(["success" => false, "error" => "JWT incorrect on not given"]));
    }
}

<?php

namespace API\Controllers;

use \API\Models\database;
use \API\middleware\jwtCentre as jwt;
use \PDO;
use PDOException;

class Massage
{



    public function addMassage($jwtToken, string $name, int $durmed, int $costo, string $urlImage, string $description = 'NULL', bool $active = true)
    {
        $jwt = new jwt();
        $jwt = $jwt->jwtValidator($jwtToken);
        $db = new database();
        $db = $db->dbConnect();
        try {
            $sql = "SELECT ISADMIN FROM sitoMassaggiDB.dbo.userAccount WHERE IDUTENTE = :idUtente";
            $stmt = $db->prepare($sql);
            $stmt->execute([':idUtente' => $jwt['userID']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (\PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al db fallita", "error" => $e]);
            exit;
        }
        $admin = $row['ISADMIN'] ?? 0;
        $stmt->closeCursor();
        if ($admin == 1) {
            $active = $active ? 1 : 0;
            try {
                $sql = "INSERT INTO sitoMassaggiDB.dbo.tipoMassaggio (NOMEMASSAGGIO, DESCRIZIONE, DURMED, COSTO, URLIMAGE, ACTIVE) VALUES (?,?,?,?,?,?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$name, $description, $durmed, $costo, $urlImage, $active]);
            } catch (PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Connessione al db fallita", "error" => $e]);
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["success" => true, "Description" => "Il massaggio è stato aggiunto correttamente"]);
            exit;
        } else {
            http_response_code(403);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Non hai i permessi per accedere a questa funzione"]);
            exit;
        }
    }


    public function getAllMassages()
    {

        $db = new database();
        if ($db = $db->dbConnect()) {
            if (!empty(apache_request_headers()['If-None-Match'])) {
                $requestEtag = apache_request_headers()['If-None-Match'];
                try {
                    $sql = "SELECT MAX(UpdatedAt) FROM sitoMassaggiDB.dbo.tipoMassaggio WHERE ATTIVO = :attivo";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([":attivo" => 1]);
                } catch (\PDOException $e) {
                    http_response_code(500);
                    header("Content-Type: application/json");
                    echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db", "error" => $e]);
                    exit;
                }
                $rows = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
                $dbEtag = md5(json_encode($rows['UpdatedAt'])) ?? '';
                if ($dbEtag == $requestEtag) {
                    http_response_code(304);
                    header("ETag: $dbEtag");
                    header("Cache-Control: public, max-age=86400, no-cache");
                    exit;
                }
            }
            $data = [];
            try {
                $sql = "SELECT * FROM sitoMassaggiDB.dbo.tipoMassaggio WHERE ATTIVO = :attivo";
                $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->bindValue(":attivo", 1);
                $stmt->execute();
                $etag = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                    $nameMassaggio = $row['NOMEMASSAGGIO'];
                    $etag[] = $row['UpdatedAt'];
                    $rowData = array("ID" => $row['IDMASSAGGIO'], "Nome" => $row['NOMEMASSAGGIO'], "Descrizione" => $row['DESCRIZIONE'], "URLImage" => $row["URLIMAGE"]);
                    $data[$nameMassaggio] = $rowData;
                }
                $etag = md5(json_encode($etag));
            } catch (\PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db", "error" => $e]);
                exit;
            }
            http_response_code(200);
            header("Content-Type: application/json");
            header("ETag: $etag");
            header("Cache-Control: public, max-age=86400, no-cache");
            echo json_encode(["success" => True, "massages" => $data]);
            exit;
        } else {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi"]);
            exit;
        }
    }


    public function getMassage(array $id)
    {
        $id = (array) $id;
        $db = new database();
        $db = $db->dbConnect();
        if (!empty($id)) {
            $data = array();
            try {
                $sql = "SELECT * FROM sitoMassaggiDB.dbo.tipoMassaggio WHERE IDMASSAGGIO = :idmassaggio";
                $stmt = $db->prepare($sql);
                for ($i = 0; count($id) - 1; $i++) {
                    $stmt->execute([':idmassaggio' => $id[$i]]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $rowData = array("ID" => $row['IDMASSAGGIO'], "Nome" => $row['NOMEMASSAGGIO'], "Descrizione" => $row['DESCRIZIONE'], "URLImage" => $row['URL'], "ETag" => md5(json_encode([$row['IDMASSAGGIO'], $row['NOMEMASSAGGIO'], $row['DESCRIZIONE'], $row['URL']])));
                    $data[$row['NOMEMASSAGGIO']] = $rowData;
                }
                http_response_code(200);
                header("Content-Type: application/json");
                echo json_encode(["success" => True, $data]);
            } catch (\PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db", "error" => $e]);
                exit;
            }
        }
    }
}

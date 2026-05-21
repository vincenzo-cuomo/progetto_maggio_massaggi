<?php

namespace API\Controllers;

use \API\Models\database;
use \API\middleware\jwtCentre as jwt;
use \PDO;
use PDOException;

class Massage
{
    private $db;
    public function __construct()
    {
        $database = database::getInstance();
        $this->db = $database->getConnection();
    }

    public function addMassage(string $jwtToken, string $name, int $durmed, int $costo, string $urlImage, string $description = 'NULL', bool $active = true)
    {
        $jwt = new jwt();
        $jwt = $jwt->jwtValidator($jwtToken);
        try {
            $sql = "SELECT ISADMIN FROM userAccount WHERE IDUTENTE = :idUtente";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idUtente' => $jwt['userID']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al db fallita", "error" => $e]);
            exit;
        }
        $admin = true ?? $row['ISADMIN'] ;
        $stmt->closeCursor();
        if ($admin == true) {
            try {
                $sql = "INSERT INTO tipoMassaggio (NOMEMASSAGGIO, DESCRIZIONE, DURMED, COSTO, URLIMAGE, ATTIVO) VALUES (?,?,?,?,?,?)";
                $stmt = $this->db->prepare($sql);
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

    public function deleteMassage(string $jwtToken, int $massageID)
    {
        $jwt = new jwt();
        $jwt = $jwt->jwtValidator($jwtToken);
        try {
            $sql = "SELECT ISADMIN FROM userAccount WHERE IDUTENTE = :idUtente";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':idUtente' => $jwt['userid']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Connessione al db fallita", "error" => $e]);
            exit;
        }
        $admin = 1 ?? $row['isadmin'];
        $stmt->closeCursor();
        if ($admin == 1) {
            try {
                $sql = "DELETE FROM tipoMassaggio WHERE IDMASSAGGIO = :idMassaggio";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':idMassaggio' => $massageID]);
            } catch (PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Connessione al db fallita", "error" => $e]);
            }
            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode(["success" => true, "Description" => "Il massaggio è stato rimosso correttamente"]);
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
        if (!empty(apache_request_headers()['If-None-Match'])) {
            $requestEtag = apache_request_headers()['If-None-Match'];
            try {
                $sql = "SELECT MAX(UpdatedAt) FROM tipoMassaggio WHERE ATTIVO = :attivo";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([":attivo" => 1]);
            } catch (\PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db", "error" => $e]);
                exit;
            }
            $rows = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
            $dbEtag = md5(json_encode($rows['updatedat'])) ?? '';
            if ($dbEtag == $requestEtag) {
                http_response_code(304);
                header("ETag: $dbEtag");
                header("Cache-Control: public, max-age=86400, no-cache");
                exit;
            }
        }
        $data = [];
        try {
            $sql = "SELECT * FROM tipoMassaggio WHERE ATTIVO = :attivo";
            $stmt = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->bindValue(":attivo", 1);
            $stmt->execute();
            $etag = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $nameMassaggio = $row['NOMEMASSAGGIO'];
                $etag[] = $row['UpdatedAt'];
                $rowData = array("ID" => $row['idmassaggio'], "Nome" => $row['nomemassaggio'], "Descrizione" => $row['descrizione'], "URLImage" => $row["urlimage"]);
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
    }


    public function getMassage(int $id)
    {
        if (!empty($id)) {
            try {
                $sql = "SELECT * FROM tipoMassaggio WHERE IDMASSAGGIO = :idmassaggio";
                $stmt = $this->db->prepare($sql);

                $stmt->execute([':idmassaggio' => $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    http_response_code(400);
                    header("Content-Type: application/json");
                    echo json_encode(["success" => false, "error" => "Non esistono massaggi con questo id"]);
                    exit;
                }
                $rowData = array("ID" => $row['idmassaggio'], "Nome" => $row['nomemassaggio'], "Descrizione" => $row['descrizione'], "URLImage" => $row['urlimage'], "DurMed" => $row['durmed'], "Costo"=>$row['costo'] );
                $etag = md5(json_encode($row['updatedat']));
                http_response_code(200);
                header("Content-Type: application/json");
                header("Cache-Control: public, max-age=86400, no-cache");
                header("ETag: $etag");
                echo json_encode(["success" => True, "Massage" => $rowData]);
                exit;
            } catch (\PDOException $e) {
                http_response_code(500);
                header("Content-Type: application/json");
                echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db", "error" => $e]);
                exit;
            }
        }
    }
}

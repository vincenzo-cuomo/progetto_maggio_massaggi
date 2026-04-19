<?php
function getAllMassages()
{
    require __DIR__ . "/../models/conf.php";
    $db = new database();
    if ($db->dbConnect()) {
        $db = $db->dbConnect();
        $data = array();
        try {
            $sql = "SELECT * URL FROM sitoMassaggiDB.dbo.tipoMassaggio WHERE ATTIVO = :attivo";
            $stmt = $db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->bindValue(":attivo", 1);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $nameMassaggio = $row['NOMEMASSAGGIO'];
                $rowData = array("ID" => $row['IDMASSAGGIO'],"Nome" => $row['NOMEMASSAGGIO'], "Descrizione" => $row['DESCRIZIONE'], "URLImage" => $row['URL']);
                $data[$nameMassaggio] = $rowData;
            }
        } catch (PDOException $e) {
            http_response_code(500);
            header("Content-Type: application/json");
            echo json_encode(["success" => false, "description" => "Non è stato possibile ricavare informazioni dal db"]);
            exit;
        }
        http_response_code(200);
        header("Content-Type: application/json");
        echo json_encode($data);
    } else {
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "description" => "Connessione al DB fallita, riprovare più tardi"]);
        exit;
    }
}

function getMassage($id){

}

?>

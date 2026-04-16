<?php

function dbConnection()
{
    $serverName = getenv('DB_SERVER');
    $database =  getenv('DB_NAME');
    $userName = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    try {
        $connectionString = "sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=yes;Encrypt=no;LoginTimeout=3";
        $db = new PDO($connectionString, $userName, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $db;
    } catch (PDOException) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "description" => "Connessione al db fallita"
        ]);
        exit;
    }
}

<?php
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$path = preg_replace('#^.*(/api/)#', '$1', $path);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Data
{
    public $objData = "";

    public function __construct()
    {
        $this->objData = json_decode(file_get_contents("php://input"), false);
    }

    public function paramsChecking($params)
    {
        foreach ($params as $field) {
            if (!isset($this->objData->$field) || empty($this->objData->$field)) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo (json_encode(["success" => false, "error" => "Not all parameters have been inserted"]));
                exit;
            }
        }
        return true;
    }
}


switch ($path) {
    case 'api/users/login':
        require __DIR__ . "/../API/controllers/loginVerifier.php";
        $jsonRequiredFields = ["email", "password"];
        $data = new Data;
        if ($data->paramsChecking($jsonRequiredFields)) {
            $email = $data->objData->email;
            $password = $data->objData->password;
        }
        verifyLogin($email, $password);
        break;
    case 'api/users/signup':
        require __DIR__ . "/../API/controllers/singnup.php";
        $jsonRequiredFields = ["name", "age", "tel", "email", "password"];
        $data = new Data;
        if ($data->paramsChecking($jsonRequiredFields)) {
            $name = $data->objData->name;
            $age = $data->objData->age;
            $tel = $data->objData->tel;
            $email = $data->objData->email;
            $password = $data->objData->password;
        }
        signup($name, $age, $tel, $email, $password);
        break;
    case 'api/users/JWTvalidation':
        require __DIR__ . "/../API/middleware/jwtValidator.php";
        $jsonRequiredFields = ["token"];
        $data = new Data;
        if ($data->paramsChecking($jsonRequiredFields)) {
            $jwt = $data->objData->token;
        }
        jwtValidator($jwt);
        break;
    default:
        http_response_code(400);
        header("Content-Type: application/json");
        echo (json_encode(["success" => false, "error" => "Incorrect syntax"]));
        break;
}

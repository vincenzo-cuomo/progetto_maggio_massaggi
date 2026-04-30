<?php
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', trim($class, "/"));
    require __DIR__ . "/../$class.php";
});

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


class Data
{
    public array $objData = [];

    public function __construct()
    {
        $this->objData = json_decode(file_get_contents("php://input"), true);
    }

    public function paramsChecking(array $params)
    {
        foreach ($params as $field) {
            if (!isset($this->objData[$field]) || empty($this->objData[$field])) {
                http_response_code(400);
                header("Content-Type: application/json");
                echo (json_encode(["success" => false, "error" => "Not all parameters have been inserted"]));
                exit;
            }
        }
        return true;
    }
}

class Router
{
    private array $routes = [];
    private function normalizePath($path)
    {
        $path = trim($path, '/');
        $path = preg_replace('#[/]{2,}#', '/', $path);
        $path = strtolower($path);
        return $path;
    }

    public function addRoute(string $path, string $method, array $controller, array $params = [])
    {
        $path = $this->normalizePath($path);
        $this->routes[] = ['path' => $path, 'method' => $method, 'controller' => $controller, 'params' => $params];
    }
    public function dispatch(string $path)
    {
        $method =  $_SERVER['REQUEST_METHOD'];
        $path = str_replace('\\', '/', $path);
        $path = $this->normalizePath($path);
        foreach ($this->routes as $route) {
            if (!preg_match("#^{$route['path']}$#", $path) || $route['method'] !== $method) {continue;}
            [$class, $function] = $route['controller'];
            
            if (!empty( $route['params'])){$paramsChecker = new Data(); $params = $route['params']; $paramsChecker->paramsChecking($route['params']); unset($paramsChecker);}
            $controllerClass = new $class;
            call_user_func_array(array($controllerClass, $function), $params);
            return;
        }
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(["Error" => "Unable to find path"]);
        exit;
    }
}

$router = new Router;
$router->addRoute('api/users/login', 'POST', [\API\Controllers\Login::class, 'verifyLogin'], ['email', 'password']);
$router->addRoute('api/users/signup', 'POST', [\API\Controllers\signUp::class, 'signup'], ['name', 'age', 'tel', 'email', 'password']);
$router->dispatch($path);

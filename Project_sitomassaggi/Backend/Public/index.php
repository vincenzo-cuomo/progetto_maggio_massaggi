<?php
require __DIR__ . "/../vendor/autoload.php";
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', trim($class, "/"));
    require __DIR__ . "/../$class.php";
});
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}



class Router
{
    private array $routes = [];

    private function normalizePath($path)
    {
        $path = preg_replace('#[/]{2,}#', '/', $path);
        $path = str_replace('\\', '/', $path);
        $path = trim($path, '/');
        $path = strtolower($path);
        return $path;
    }

    public function addRoute(string $path, string $method, array $controller, array $params = [])
    {
        $path = $this->normalizePath($path);
        $this->routes[] = ['path' => $path, 'method' => $method, 'controller' => $controller, 'params' => $params];
    }
    public function dispatch(string $path){
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->normalizePath($path);
        foreach ($this->routes as $route) {
            $regex = preg_replace_callback('/{\w+(:([^}\s]+))?}/', function ($matches) {
                return isset($matches[1]) ? '(' . $matches[2] . ')' : '([a-zA-Z0-9_-]+)';
            }, $route['path']);
            $regex = '#^' . $regex . '$#'; #Aggiungo i delimitatori
            #Per il me del futuro, esempio .../qualcosa/{id:[0-9]} regex call back SOSTITUISCE LE SINGOLE ISTANZE E RIAVVIA LA FUNZIONE A OGNI ISTANZA prende come pattern quello dopo i due punti, matches[0] equivale alla corrispondenza ('id:[0-9]'), matches[1] al primo gruppo di cattura (':[0-9]') e matches[2] al secondo gruppo alias il patterm ('[0-9]) se non ci sono i : allora usa un pattern standard, se non c'è niente c'è il regex che prende tutto e se è un path statico 
            #ora che ho il pattern del path del controller devo confrontarlo con quello richiesto dall'utente
            if (preg_match($regex, $path, $match)) {
                array_shift($match); #Leva il primo elemento di un array (si riferisce direttamente ad essa)
                $valoriParametriPath = $match;
                $nomiParametriPath = [];
                if (preg_match_all('/{(\w+)(:([^}]+))?}/', $route['path'], $matches)) { #adesso (\w+) fa parte di un gruppo di cattura a differenza di preg replace call back; matches[1] equivale a id o qualunque altra cosa prima dei : e con preg match all raggruppa tutte le corrispondenze di unpattern in una unica array
                    $nomiParametriPath = $matches[1];
                }
                $parametri = array_combine($nomiParametriPath, $valoriParametriPath) ?? $route['params']; #combino nomi e valori in una unica array con array combine
                $body = json_decode(file_get_contents("php://input"), true) ?? [];
                $parametri = array_merge($parametri, $body);
                [$class, $function] = $route['controller'];
                $controllerClass = new $class;
                call_user_func_array(array($controllerClass, $function), $parametri);
            
                return;
            } else {
                continue;
            }
            #il pattern non corrisponde e quindi continua l'iterazione
        }
        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode(["Error" => "Unable to find path "]);
        exit;
    }
}

$router = new Router;
$router->addRoute('api/users/login', 'POST', [\API\Controllers\User::class, 'verifyLogin'], ['email', 'password']);
$router->addRoute('api/users/signup', 'POST', [\API\Controllers\User::class, 'signup'], ['name', 'age', 'tel', 'email', 'password']);
$router->addRoute('api/massages', 'GET', [\API\Controllers\Massage::class, 'getAllMassages'], []);
$router->addRoute('api/massages/{id:[0-9]+}', 'GET', [\API\Controllers\Massage::class, 'getMassage'], []);
$router->addRoute('api/massages/add', 'POST', [\API\Controllers\Massage::class, 'addMassage'], ['jwtToken', 'name', 'durmed', 'costo', 'urlImage', 'description', 'active']);
$router->addRoute('api/massages/delete', 'POST', [\API\Controllers\Massage::class, 'deleteMassage'], ['jwtToken', 'massageID']);
$router->dispatch($path);

<?php
require "../bootstrap.php";
require_once "../vendor/autoload.php";

use App\Core\Router;
use App\Controllers\UserController;
use App\Controllers\CentrosCivicosController;
use App\Controllers\InstalacionesController;
use App\Controllers\ActividadesController;
use App\Controllers\ReservasController;
use App\Controllers\InscripcionesController;
use App\Controllers\AuthController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE"); 

$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

// Recuperamos el método utilizado

$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_METHOD'];

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $request );

$userId = null;
if(isset($uri[3])){
    $userId = (int) $uri[3];
}

// Proceso de login

// if($request == '/api/login/'){
//     $auth = new AuthController($requestMethod);
//     if (!$auth->loginFromRequest()) {
//         exit(http_response_code(401));
//     };
// } 

function sesion(){
    // $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    $arr = explode(" ", $authHeader);
    $jwt = null;

    if ($authHeader) {
        $arr = explode(" ", $authHeader);
        if (isset($arr[1])) {
            $jwt = $arr[1];
        }
    }

    // var_dump($authHeader);
    
    if($jwt){
        try{
            JWT::decode($jwt, new Key(KEY, 'HS256'));
            return true;
        } catch (Exception $e){
            echo json_encode([
                'message' => 'Acceso denegado', 
                           'error' => $e->getMessage() 
            ]);
            return false;
            http_response_code(401);
            exit();
        }
    }
    return false;
}


$router = new Router();

// CONSULTAS PARA USUARIOS
$router->add(array(
    'name'=>'login',
    'path'=>'/^\/api\/login+$/',
    'action'=> AuthController::class,
    'perfil'=>  ['registros']
));

$router->add(array(
    'name'=>'register',
    'path'=>'/^\/api\/register$/',
    'action'=> UserController::class,
    'perfil'=>  ['registros']
));

$router->add(array(
    'name'=>'usuarios',
    'path'=>'/^\/api\/user$/',
    'action'=> UserController::class,
    'perfil'=>  ['usuario']
));

$router->add(array(
    'name'=>'token',
    'path'=>'/^\/api\/token\/refresh$/',
    'action'=> UserController::class,
    'perfil'=>  ['usuario']
));


// CONSULTAS PARA CENTROS CIVICOS

$router->add(array(
    'name'=>'centros',
    'path'=>'/^\/api\/centros$/',
    'action'=> CentrosCivicosController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'centro',
    'path'=>'/^\/api\/centros\/[0-9]+$/',
    'action'=> CentrosCivicosController::class,
    'perfil'=>  ['publico']
));

// CONSULTAS PARA INSTALACIONES

$router->add(array(
    'name'=>'instalaciones',
    'path'=>'/^\/api\/instalaciones$/',
    'action'=> InstalacionesController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'instalacion',
    'path'=>'/^\/api\/centros\/[0-9]+\/instalaciones$/',
    'action'=> InstalacionesController::class,
    'perfil'=>  ['publico']
));

// CONSULTAS PARA ACTIVIDADES

$router->add(array(
    'name'=>'actividades',
    'path'=>'/^\/api\/actividades$/',
    'action'=> ActividadesController::class,
    'perfil'=>  ['publico']
));

$router->add(array(
    'name'=>'actividad',
    'path'=>'/^\/api\/centros\/[0-9]+\/actividades$/',
    'action'=> ActividadesController::class,
    'perfil'=>  ['publico']
));

// CONSULTAS PARA RESERVAS

$router->add(array(
    'name'=>'reservas',
    'path'=>'/^\/api\/reservas$/',
    'action'=> ReservasController::class,
    'perfil'=>  ['usuario']
));

$router->add(array(
    'name'=>'reserva',
    'path'=>'/^\/api\/reservas\/[0-9]+$/',
    'action'=> ReservasController::class,
    'perfil'=>  ['usuario']
));

// CONSULTAS PARA INSCRIPCIONES 

$router->add(array(
    'name'=>'inscripciones',
    'path'=>'/^\/api\/inscripciones$/',
    'action'=> InscripcionesController::class,
    'perfil'=>  ['usuario']
));

$router->add(array(
    'name'=>'inscripcion',
    'path'=>'/^\/api\/inscripciones\/[0-9]+$/',
    'action'=> InscripcionesController::class,
    'perfil'=>  ['usuario']
));


$route = $router->match($request);
if ($route) {

    if($route['perfil'][0] == 'usuario' && !sesion()){
        $response['body'] = json_encode(['message' => 'Acceso no autorizado']);
        $response['body'] = json_encode(array('message' => 'Acceso no autorizado'));
        echo json_encode($response['body']);
        exit();
    }

    if($route['perfil'][0] == 'registros' && sesion()){
        $response['body'] = json_encode(['message' => 'Acceso no permitido']);
        $response['body'] = json_encode(array('message' => 'Acceso no permitido'));
        echo json_encode($response['body']);
        exit();
    }
   
    $controllerName = $route['action'];
    $controller = new $controllerName($requestMethod, $userId);
    $controller->processRequest();
} else {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    echo json_encode($response);
}

?>
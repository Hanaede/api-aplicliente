<?php

namespace App\Controllers;

use App\Models\Actividades;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ActividadesController
{

    private $requestMethod;
    private $actividad;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->actividad = Actividades::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch($this->requestMethod){
            case 'GET':
                if(isset($uri[3])){
                    $response = $this->getActividad($uri[3]);
                } else {
                    $response = $this->getActividadFilter();
                }
                break;
                
            default:
                $response = $this->notFoundResponse();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }
    
    // Método para obtener las actividades filtradas
    private function getActividadFilter()
    {
        // Verificar si los datos vienen por GET
        if (!empty($_GET)) {
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = $_GET;
        } else {
            // Si no hay datos en GET, intenta obtenerlos desde JSON en el cuerpo de la petición
            $input = (array) json_decode(file_get_contents('php://input'), TRUE);

            $result = $this->actividad->getByFilter($input);

            if (!$result) {
                return $this->notFoundResponse();
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;  

            error_log("Recibidos datos por JSON: " . print_r($input, true));
        }

        // Obtener los resultados filtrados
        $result = $this->actividad->getByFilter($input);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    
    // Método para obtener una actividad por ID
    private function getActividad($id = ''){
        $result = $this->actividad->get(['id' => $id]);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Método para devolver una respuesta de error 422
    private function notFoundResponse(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Actividad no encontrada'], JSON_UNESCAPED_UNICODE)
        ];
    }

}

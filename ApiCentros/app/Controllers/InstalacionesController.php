<?php

namespace App\Controllers;

use App\Models\Instalaciones;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class InstalacionesController
{

    private $requestMethod;
    private $instalaciones;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->instalaciones = Instalaciones::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);
        
        switch($this->requestMethod){
            case 'GET':
                if(isset($uri[3])){
                    $response = $this->getInstalacion($uri[3]);
                } else {
                    $response = $this->getInstalacionFilter();
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
    

    private function getInstalacionFilter()
    {
        // Verificar si los datos vienen por GET
        if (!empty($_GET)) {
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = $_GET;
        } else {
            // En caso de que no haya datos en GET, intenta obtenerlos desde JSON en el cuerpo de la petición
            $input = (array) json_decode(file_get_contents('php://input'), TRUE);

            $result = $this->instalaciones->getByFilter($input);

            if (!$result) {
                return $this->notFoundResponse();
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;  

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error al decodificar JSON: " . json_last_error_msg());
                return $this->unprocessableEntityResponse();
            }

            error_log("Recibidos datos por JSON: " . print_r($input, true));
        }

        // Obtener los resultados filtrados
        $result = $this->instalaciones->getByFilter($input);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    
    
    // Método para obtener una instalación por ID

    private function getInstalacion($id = ''){
        $result = $this->instalaciones->get(['id' => $id]);


        if (!$result) {
            return $this->notFoundResponse();
        }


        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }


    private function notFoundResponse(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Instalación no encontrada'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function unprocessableEntityResponse() {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(["error" => "Datos inválidos"]);
        return $response;
    }

}
?>
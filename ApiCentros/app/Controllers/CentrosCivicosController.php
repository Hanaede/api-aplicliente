<?php

namespace App\Controllers;

use App\Models\Centros;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CentrosCivicosController
{

    private $requestMethod;
    private $centros;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->centros = Centros::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch($this->requestMethod){
            case 'GET':
                if(isset($uri[3])){
                    $response = $this->getCentro($uri[3]);
                } else {
                    $response = $this->getAllCentros();
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
    
    // Método para obtener todos los centros cívicos
    private function getAllCentros(){

        $result = $this->centros->getAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;    

    }

    // Método para obtener un centro cívico
    private function getCentro($id = ''){
        // var_dump(['id' => $id]); die();

        $result = $this->centros->get(['id' => $id]);


        if (!$result) {
            return $this->notFoundResponse();
        }


        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Método para devolver un error 404
    private function notFoundResponse(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Centro cívivo no encontrado'], JSON_UNESCAPED_UNICODE)
        ];
    }

}
?>
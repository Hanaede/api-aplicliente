<?php

namespace App\Controllers;

use App\Models\Reservas;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ReservasController
{

    private $requestMethod;
    private $reservas;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->reservas = Reservas::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch($this->requestMethod){
            case 'GET':
                $response = $this->getReservas();
                break;

            case 'POST':
                $response = $this->NuevaReserva();
                break;
            
            case 'DELETE':
                $response = $this->deleteReserva($uri[3]);
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
    
    private function NuevaReserva(){

        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        $result = $this->reservas->set($input);

        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Reserva creada con éxito'], JSON_UNESCAPED_UNICODE);
        return $response; 

    }

    private function getReservas(){
        // Comprobar que el campo id no esté vacío
        if (!empty($_GET['id'])) { 
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = ['id' => $_GET['id']];
            $result = $this->reservas->get($input);

            if (!$result) {
                return $this->notFoundResponse();
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
            

        }
        // Obtenemos las cabeceras de autorización
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        // Obtenemos el token
        $jwt = explode(" ", $authHeader)[1] ?? null;

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;

        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }
        
        // Obtenemos las reservas del usuario
        $input['id'] = $idUser;

        $result = $this->reservas->get(['id' => $idUser]);
        
        if (!$result) {
            return $this->noData();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    // Método para eliminar una reserva
    private function deleteReserva($id = ''){

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $reserva = null;
        // Obtenemos el token
        $jwt = explode(" ", $authHeader)[1] ?? null;

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;

        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }
        // Obtenemos las reservas del usuario
        $reservasUsuario = $this->reservas->get(['id' => $idUser]);

        foreach ($reservasUsuario as $reserva) {
            if ($reserva['id'] == $id) {
                $reserva = $reserva;
                $result = $this->reservas->delete($id); // Eliminamos la reserva
            } else {
                $reserva = null;
            }
        }

        if (!$reserva) {
            return $this->notFound();
        }


        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Reserva eliminada con éxito'], JSON_UNESCAPED_UNICODE);
        return $response;
    }


    private function notFoundResponse(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'No se ha podido realizar la consulta'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function noData(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'No se han encontrado reservas'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function notFound(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'No se ha encontrado la reserva'], JSON_UNESCAPED_UNICODE)
        ];
    }

}
?>
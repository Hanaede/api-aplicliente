<?php

namespace App\Controllers;

use App\Models\Inscripciones;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use \Exception;

class InscripcionesController
{

    private $requestMethod;
    private $inscripciones;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->inscripciones = Inscripciones::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch($this->requestMethod){

            case 'POST':
                $response = $this->nuevaInscripcion();
                break;
            
            case 'DELETE':
                $response = $this->deleteInscripcion($uri[3]);
                break;
                
            case 'GET':
                $response = $this->getInscripciones();
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

    // Método para obtener las inscripciones
    private function getInscripciones(){

        if (!empty($_GET['id'])) {  // Aseguramos que el campo 'id' no esté vacío
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = ['id' => $_GET['id']];
            $result = $this->inscripciones->get($input);

            if (!$result) {
                return $this->notFoundResponse();
            }

            $response['status_code_header'] = 'HTTP/1.1 200 OK';
            $response['body'] = json_encode($result);
            return $response;
        }
        // Verificar si los datos vienen por GET
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        
        $jwt = explode(" ", $authHeader)[1] ?? null;
        // Verificar si el token es válido
        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;

        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }
        // Si el token es válido, obtenemos las inscripciones del usuario
        $input['id'] = $idUser;

        $result = $this->inscripciones->get(['id' => $idUser]);
        
        if (!$result) {
            return $this->noData();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }
    
    // Método para crear una nueva inscripción
    private function nuevaInscripcion(){
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    
        // Registro de depuración para verificar los datos recibidos
        error_log("Datos recibidos: " . print_r($input, true));
    
        // Verificar que 'actividad_id' esté presente en el array $input
        if (!isset($input['actividad_id'])) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['message' => 'El campo actividad_id es requerido'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
    
        $plazas = $this->inscripciones->getPlazas($input['actividad_id']);
        $inscripcionesActuales = $this->inscripciones->getInscripcionesCount($input['actividad_id']);
    
        // Verificar que se encontraron plazas para la actividad
        if (empty($plazas) || !isset($plazas[0]['plazas'])) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['message' => 'No se encontraron plazas para la actividad'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
        // Verificar que hay plazas disponibles
        if ($inscripcionesActuales >= $plazas[0]['plazas']) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['message' => 'No hay plazas disponibles'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
    
        $result = $this->inscripciones->set($input);
    
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(['message' => 'Inscripcion creada con éxito'], JSON_UNESCAPED_UNICODE);
        return $response;
    }

    // Método para eliminar una inscripción
    private function deleteInscripcion($id = ''){
     // Obtenemos el valor de la cabecera 'Authorization'
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;  
        $inscripcion = null;

        $jwt = explode(" ", $authHeader)[1] ?? null;
        // Verificar si el token es válido
        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;
            
        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }
        // Verificar si el usuario tiene inscripciones
        $inscripcionesUsuario = $this->inscripciones->getById($idUser);
        
        foreach ($inscripcionesUsuario as $inscripcion) {
            if ($inscripcion['id'] == $id) {
                $inscripcion = $inscripcion;
                $result = $this->inscripciones->delete($id);
            } else {
                $inscripcion = null;
            }
        }
        // Verificar si se encontró la inscripción
        if (!$inscripcion) {
            return $this->notFound();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['message' => 'Inscripcion eliminada con éxito'], JSON_UNESCAPED_UNICODE);
        return $response;
    }

    private function noData(){
        return [
            'status_code_header' => 'HTTP/1.1 204 No Content',
            'body' => json_encode(['message' => 'No data found'], JSON_UNESCAPED_UNICODE)
        ];
    }
    
    private function notFoundResponse(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'No se ha podido realizar la consulta'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function notFound(){
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'No se ha encontrado la Inscripcion'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function unauthorizedResponse($message){
        return [
            'status_code_header' => 'HTTP/1.1 401 Unauthorized',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }
}
?>

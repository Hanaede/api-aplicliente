<?php

namespace App\Controllers;

use App\Models\Usuarios;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class UserController
{

    private $requestMethod;
    private $usuarios;
    private $usuariosId;

    public function __construct($requestMethod, $usuariosId)
    {
        $this->requestMethod = $requestMethod;
        $this->usuariosId = $usuariosId;
        $this->usuarios = Usuarios::getInstancia();
    }

    // Método para procesar la petición
    public function processRequest(){

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode('/', $uri);

        switch($this->requestMethod){
            case 'GET':
                $response = $this->getUsuario();
                break;

            case 'POST':
                if (isset($uri[3]) && $uri[3] === 'refresh') {
                    $response = $this->refreshToken();
                } else {
                    $response = $this->register();
                }
                break;

            case 'PUT':
                $response = $this->updateUsuario();
                break;

            case 'DELETE':
                $response = $this->deleteUsuario();
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


    private function getUsuario()
    {
        // Verificar si los datos vienen por GET
        if (!empty($_GET)) {
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = $_GET;
        } else {
            $input = (array) json_decode(file_get_contents('php://input'), TRUE);

            if(empty($input)){
                $result = $this->usuarios->getAll();
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode($result);
                return $response;
            } else {

                if (!$this->usuarios->get($input)) {
                    return $this->notFoundResponse();
                }


                $result = $this->usuarios->get($input);
                $response['status_code_header'] = 'HTTP/1.1 200 OK';
                $response['body'] = json_encode($result);
                return $response;
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error al decodificar JSON: " . json_last_error_msg());
                return $this->unprocessableEntityResponse();
            }

            error_log("Recibidos datos por JSON: " . print_r($input, true));
        }

        // Obtener los resultados filtrados
        $result = $this->usuarios->get($input);

        if (!$result) {
            return $this->notFoundResponse();
        }

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function register()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    
        // Verificación de datos
        if (!$this->validateUsuario($input)) {
            $response['status_code_header'] = 'HTTP/1.1 400 Bad Request';
            $response['body'] = json_encode(['message' => 'Datos de usuario inválidos'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
        // Verificar si el usuario ya existe
        $existingUser = $this->usuarios->get(['email' => $input['email']]);
        if ($existingUser) {
            $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
            $response['body'] = json_encode(['message' => 'Este usuario ya existe'], JSON_UNESCAPED_UNICODE);
            return $response;
        } else {
            // Crear un nuevo usuario
            $this->usuarios->set($input);
            // Devolver una respuesta de éxito
            $response['status_code_header'] = 'HTTP/1.1 201 Created';
            $response['body'] = json_encode(['message' => 'Usuario creado con éxito'], JSON_UNESCAPED_UNICODE);
            return $response;
        }
    }


    private function updateUsuario()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);


        $jwt = explode(" ", $authHeader)[1] ?? null;

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;
            $emailUser = $decoded->data->usuario ?? null;

        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }

        // $existingUser = $this->usuarios->get(['email' => $input['email']]);
        if ($input['email'] !== $emailUser) {
            $existingUser = $this->usuarios->get(['email' => $input['email']]);
            if ($existingUser) {
                $response['status_code_header'] = 'HTTP/1.1 409 Conflict';
                $response['body'] = json_encode(['message' => 'Este usuario ya existe'], JSON_UNESCAPED_UNICODE);
                return $response;
            }
        }

        $usuarioRegistrado = $this->usuarios->get(['id' => $idUser]);

        if(!isset($input['password'])){
            $input['password'] = $usuarioRegistrado['password'];
        }

        // var_dump($usuarioRegistrado['id']); die();

        if (!$this->validateUsuario($input)) {
            echo json_encode(['Error' => "Falta nombre o email"], JSON_UNESCAPED_UNICODE);
            return $this->notFoundResponse();
        }



        if(!isset($input['password'])){
            $input['password'] = $decoded->data->{1};
        } 

        $input['id'] = $idUser;

        $this->usuarios->edit($input);

        return $this->successResponse("Usuario actualizado con éxito");
    }

    private function deleteUsuario()
    {
        // Verificar si los datos vienen por GET
        if (!empty($_GET['id'])) {  // Aseguramos que 'id' esté presente
            error_log("Recibidos datos por GET: " . print_r($_GET, true));
            $input = ['id' => $_GET['id']];
            $this->usuarios->delete($input);
            return $this->successResponse("Usuario eliminado con éxito");
        }

        // Manejar el caso donde los datos vienen en JSON con JWT
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $jwt = explode(" ", $authHeader)[1] ?? null;

        if (!$jwt) {
            return $this->unauthorizedResponse("Acceso denegado: Token no proporcionado.");
        }

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
            $idUser = $decoded->data->{0} ?? null;

            if (!$idUser) {
                return $this->unprocessableEntityResponse("No se pudo obtener el ID del usuario.");
            }

            $this->usuarios->delete(['id' => $idUser]);
            return $this->successResponse("Usuario eliminado con éxito");

        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }
    }


    private function refreshToken()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            return $this->unauthorizedResponse("Token no proporcionado");
        }

        // Extraer el token JWT del encabezado de autorización
        $jwt = explode(" ", $authHeader)[1] ?? null;
        if (!$jwt) {
            return $this->unauthorizedResponse("Token inválido");
        }

        try {
            $decoded = JWT::decode($jwt, new Key(KEY, 'HS256'));
        } catch (Exception $e) {
            return $this->unauthorizedResponse("Acceso denegado: " . $e->getMessage());
        }

        // Generar un nuevo token
        $key = KEY; // Clave de encriptación
        $issuer_claim = "http://apicentros.local"; // Emisor del token
        $audience_claim = "http://apicentros.local"; // Destinatario del token
        $issuedat_claim = time(); // Tiempo en que fue emitido el token
        $notbofore_claim = time(); // Tiempo antes del cual no es válido el token
        $expire_claim = $issuedat_claim + 3600; // Tiempo de expiración del token

        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbofore_claim,
            "exp" => $expire_claim,
            "data" => $decoded->data
        );

        $jwt = JWT::encode($token, $key, 'HS256');
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(
            array(
                "message" => "Token renovado con éxito.",
                "jwt" => $jwt,
                "expireAt" => $expire_claim
            )
        );

        return $response;
    }








    private function validateUsuario($input){
        if (!isset($input['nombre']) || !isset($input['email'])) {
            return false;
        }
        return true;
    }

    private function unauthorizedResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 401 Unauthorized',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function successResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 200 OK',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }

    private function unprocessableEntityResponse() {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode(["error" => "Datos inválidos"]);
        return $response;
    }


}
?>
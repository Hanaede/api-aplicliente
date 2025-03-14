<?php

namespace App\Controllers;

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use App\Models\Usuarios;

class AuthController
{
    private $requestMethod;
    private $userId;
    private $users;

    public function __construct($requestMethod)
    {
        $this->requestMethod = $requestMethod;
        $this->users = Usuarios::getInstancia();

    }

    // Método para procesar la petición
    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'POST':
                $response = $this->loginFromRequest();
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

    // Método para iniciar sesión
    public function loginFromRequest()
    {
        // Leemos flujo de entrada
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);

        // Determinamos si el JSON es válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(["message" => "El JSON recibido no es válido.", "error" => exit()]);
        }

        if (!isset($input['email']) || !isset($input['password'])) {
            return $this->unprocessableEntityResponse("El email y la contraseña son obligatorios.");
        }

        if (count($input) > 2) {
            return $this->unprocessableEntityResponse("Solo puede escribir el correo y la contraseña.");
        }

        $email = $input['email'];
        $password = $input['password'];
        $dataUser = $this->users->login($email, $password);

        $id = $dataUser['id'] ?? null;

        // Si hemos logueado, generamos un token
        if ($dataUser) {
            $key = KEY; 
            $issuer_claim = "http://apicentros.local"; 
            $audience_claim = "http://apicentros.local"; 
            $issuedat_claim = time(); 
            $notbofore_claim = time(); 
            $expire_claim = $issuedat_claim + 3600; 

            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $notbofore_claim,
                "exp" => $expire_claim,
                "data" => array(
                    "usuario" => $email, $id
                )
            );

            $jwt = JWT::encode($token, $key, 'HS256');
            $res['status_code_header'] = 'HTTP/1.1 200 OK';
            $res['body'] = json_encode(
                array(
                    "message" => "Inicio de sesión exitoso.",
                    "jwt" => $jwt,
                    "usuario" => $email,
                    "expireAt" => $expire_claim
                )
            );

        } else {
            $res['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
            $res['body'] = json_encode(["message" => "Usuario o contraseña incorrectos."]);
        }

        return $res;
    }

    // Función para devolver una respuesta de error 422
    private function unprocessableEntityResponse($message)
    {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode(['message' => $message], JSON_UNESCAPED_UNICODE)
        ];
    }

    // Función para devolver una respuesta de error 404
    private function notFoundResponse()
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Recurso no encontrado'], JSON_UNESCAPED_UNICODE)
        ];
    }
}
?>
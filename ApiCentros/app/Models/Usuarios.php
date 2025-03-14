<?php
namespace App\Models;

class usuarios extends DBAbstractModel
{
    private static $instancia;

    public static function getInstancia()
    {
        if (!isset(self::$instancia)) {
            $miClase = __CLASS__;
            self::$instancia = new $miClase;
        }
        return self::$instancia;
    }

    public function __clone()
    {
        trigger_error('La clonación no es permitida!.', E_USER_ERROR);
    }

    // Función para obtener todos los usuarios
    public function getAll(){
        $this->query = "SELECT * FROM usuarios";
        $this->getResultFromQuery();
        return $this->rows;
    }

    // Función para obtener un usuario por id
    public function get($sh_data = array()){
        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }
    
        if(isset($id)){
            $this->query = "SELECT * FROM usuarios WHERE id = :id";

            $this->parametros['id'] = $id;

        } elseif (isset($email)){
            $this->query = "SELECT * FROM usuarios WHERE email = :email";

            $this->parametros['email'] = $email;

        } else {
            return null;
        }

        // Ejecución de la consulta
        $this->getResultFromQuery();

        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad=>$valor){
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Usuario encontrado';
        } else {
            $this->mensaje = 'Usuario no encontrado';
        }
        return $this->rows[0] ?? null;
    }

    // Función para insertar un usuario
    public function set($dataCont=array()) {
        foreach ($dataCont as $campo=>$valor) {
            $$campo = $valor;
        }

        $this->query = "INSERT INTO usuarios(nombre, email, password) VALUES (:nombre, :email, :password)";
        $this->parametros['nombre'] = $nombre;
        $this->parametros['email'] = $email;
        $this->parametros['password'] = $password;

        $this->getResultFromQuery();
      
        $this->mensaje = 'Usuario agregado';

        return $this->mensaje;
    }

    // Función para editar un usuario
    public function edit($dataCont=array()){

        foreach ($dataCont as $campo=>$valor) {
            $$campo = $valor;
        }
        $this->query = "UPDATE usuarios SET nombre = :nombre, email = :email, password = :password WHERE id = :id";
        $this->parametros['nombre'] = $nombre;
        $this->parametros['email'] = $email;
        $this->parametros['password'] = $password;

        
        $this->parametros['id'] = $id;

        $this->getResultFromQuery();
 
        $this->mensaje = 'Contacto modificado';

    }

    // Función para eliminar un usuario
    public function delete($dataCont=array()){
        foreach ($dataCont as $campo=>$valor) {
            $$campo = $valor;
        }
        $this->query = "DELETE FROM usuarios WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();

        $this->mensaje = 'Usuario eliminado';
        
    }
    
    // Función para loguear un usuario
    public function login($email = '', $password = ''){
        if($email != '' && $password != ''){
            $this->query= "SELECT * FROM usuarios WHERE email = :email AND password = :password";

            $this->parametros['email'] = $email;
            $this->parametros['password'] = $password;

            $this->getResultFromQuery();
        }
        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad => $valor) {
                $this->$propiedad = $valor;
            }
            $this->mensaje = "Usuario encontrado";
        } else {
            $this->mensaje = 'Usuario no encontrado';
        }
        return $this->rows[0] ?? null;
    }
}

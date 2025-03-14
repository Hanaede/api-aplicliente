<?php
namespace App\Models;

class Centros extends DBAbstractModel
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

    // Para obtener todos los usuarios
    public function getAll(){
        $this->query = "SELECT * FROM centroscivicos";
        $this->getResultFromQuery();
        return $this->rows;
    }

    // Para obtener un centro por id
    public function get($sh_data = array()){
    
        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }
    
        if(isset($id)){
            $this->query = "SELECT * FROM CentrosCivicos WHERE id = :id";

            // Cargamos los parametros
            $this->parametros['id'] = $id;

        }

        // Ejecución de la consulta
        $this->getResultFromQuery();

        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad=>$valor){
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Centro encontrado';
        } else {
            $this->mensaje = 'Centro no encontrado';
        }
        return $this->rows[0] ?? null;
    }

    public function set(){}
    public function edit(){}
    public function delete(){}
    

}

<?php
namespace App\Models;

class Instalaciones extends DBAbstractModel
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

    // Función para obtener un una instalación por id de centro
    public function get($sh_data = array()){
        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }
    
        if(isset($id)){
            $this->query = "SELECT * FROM instalaciones WHERE id_centro = :id_centro";

            // Cargamos los parametros
            $this->parametros['id_centro'] = $id;
        }

        // Ejecución de la consulta
        $this->getResultFromQuery();

        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad=>$valor){
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Instalaciones encontrado';
        } else {
            $this->mensaje = 'Instalaciones no encontrado';
        }
        return $this->rows ?? null;
    }

    // Función para obtener por filtro
    public function getByFilter($sh_data = array()) {
        if (!is_array($sh_data)) {
            return null;
        }
    
        $this->query = "SELECT instalaciones.*, centroscivicos.nombre AS nombre_centro 
                        FROM instalaciones 
                        JOIN centroscivicos ON instalaciones.centro_id = centroscivicos.id";
        $this->parametros = [];
        $conditions = [];
    
        if (!empty($sh_data['nombre'])) {
            $conditions[] = "instalaciones.nombre LIKE :nombre";
            $this->parametros['nombre'] = '%' . $sh_data['nombre'] . '%';
        }
    
        if (!empty($sh_data['descripcion'])) {
            $conditions[] = "instalaciones.descripcion LIKE :descripcion";
            $this->parametros['descripcion'] = '%' . $sh_data['descripcion'] . '%';
        }
    
        if (!empty($sh_data['capacidad_maxima'])) {
            $conditions[] = "instalaciones.capacidad_maxima = :capacidad_maxima";
            $this->parametros['capacidad_maxima'] = $sh_data['capacidad_maxima'];
        }
    
        // Se agregan las condiciones a la consulta
        if (!empty($conditions)) {
            $this->query .= " WHERE " . implode(" AND ", $conditions);
        }
    
        $this->getResultFromQuery();
    
        if(count($this->rows) > 0){
            $this->mensaje = 'Instalaciones encontradas';
        } else {
            $this->mensaje = 'Instalaciones no encontradas';
        }
        return $this->rows ?? null;
    }
    

    public function set(){}
    public function edit(){}
    public function delete(){}
    

}

?>
<?php
namespace App\Models;

class Inscripciones extends DBAbstractModel
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

    // Función para obtener una inscripción por id de usuario
    public function get($sh_data = array()){
        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }
        if(isset($id)){
            $this->query = "SELECT inscripciones.*, actividades.nombre AS nombre_actividad, centroscivicos.nombre AS nombre_centro 
                            FROM inscripciones 
                            JOIN actividades ON inscripciones.actividad_id = actividades.id 
                            JOIN centroscivicos ON actividades.centro_id = centroscivicos.id 
                            WHERE inscripciones.id_usuario = :id_usuario";
    
            // Cargamos los parametros
            $this->parametros['id_usuario'] = $id;
        }
    
        // Ejecución de la consulta
        $this->getResultFromQuery();
    
        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad=>$valor){
                $this->$propiedad = $valor;
            }
            $this->mensaje = 'Inscripción encontrada';
        } else {
            $this->mensaje = 'Inscripción no encontrada';
        }
        return $this->rows ?? null;
    }

    // Función para insertar una inscripción
    public function set($sh_data = array()){
        if (!is_array($sh_data)) {
            return null;
        }

        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }

        $this->query = "INSERT INTO inscripciones (nombre_solicitante, id_usuario, telefono, email, actividad_id, fecha_inscripcion, estado ) VALUES (:nombre_solicitante, :id_usuario, :telefono, :email, :actividad_id, :fecha_inscripcion, :estado)";
        $this->parametros['nombre_solicitante'] = $nombre_solicitante;
        $this->parametros['id_usuario'] = $id_usuario;
        $this->parametros['telefono'] = $telefono;
        $this->parametros['email'] = $email;
        $this->parametros['actividad_id'] = $actividad_id;
        $this->parametros['fecha_inscripcion'] = $fecha_inscripcion;
        $this->parametros['estado'] = $estado;

        
        $this->getResultFromQuery();
        $this->mensaje = 'Inscripción realizada';
    }

    // Función para editar un usuario
    public function edit($sh_data = array()){
        if (!is_array($sh_data)) {
            return null;
        }

        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }

        $this->query = "UPDATE inscripciones SET nombre_solicitante = :nombre_solicitante, telefono = :telefono, email = :email, actividad_id = :actividad_id, fecha_inscripcion = :fecha_inscripcion, estado = :estado WHERE id_usuario = :id_usuario";
        $this->parametros['nombre_solicitante'] = $nombre_solicitante;
        $this->parametros['telefono'] = $telefono;
        $this->parametros['email'] = $email;
        $this->parametros['actividad_id'] = $actividad_id;
        $this->parametros['fecha_inscripcion'] = $fecha_inscripcion;
        $this->parametros['estado'] = $estado;
        $this->parametros['id_usuario'] = $id_usuario;

        $this->getResultFromQuery();
        $this->mensaje = 'Inscripción actualizada';
    }

    // Función para obtener un usuario por id
    public function getById($id = ''){
        $this->query = "SELECT * FROM inscripciones WHERE id_usuario = :id_usuario";
        $this->parametros['id_usuario'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Inscripción encontrada';
        return $this->rows;
    }

    // Función para obtener plazas de una actividad
    public function getPlazas($id = ''){
        $this->query = "SELECT plazas FROM actividades WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Plazas encontradas';
        return $this->rows;
    }

    // Función para obtener el número de inscripciones de una actividad
    public function getInscripcionesCount($id_actividad = ''){
        $this->query = "SELECT COUNT(*) as count FROM inscripciones WHERE actividad_id = :actividad_id";
        $this->parametros['actividad_id'] = $id_actividad;
        $this->getResultFromQuery();
        return $this->rows[0]['count'];
    }

    // Función para eliminar un usuario
    public function delete($id = ''){
        $this->query = "DELETE FROM inscripciones WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Inscripción eliminada';
    }
}

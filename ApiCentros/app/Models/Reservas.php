<?php
namespace App\Models;

class Reservas extends DBAbstractModel
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

    // Para obtener un usuario por id
    public function get($sh_data = array()){

        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }
    
        if(isset($id)){
            $this->query = "SELECT reservas.*, centroscivicos.nombre AS nombre_centro 
                        FROM reservas 
                        JOIN instalaciones ON reservas.id_instalacion = instalaciones.id 
                        JOIN centroscivicos ON instalaciones.centro_id = centroscivicos.id 
                        WHERE reservas.id_usuario = :id_usuario";

            // Cargamos los parametros
            $this->parametros['id_usuario'] = $id;

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

    // Para insertar una reserva
    public function set($sh_data = array()){
        if (!is_array($sh_data)) {
            return null;
        }

        foreach ($sh_data as $campo=>$valor) {
            $$campo = $valor;
        }

        $nombre_solicitante = $nombre_solicitante ?? '';
        $id_usuario = $id_usuario ?? '';
        $telefono = $telefono ?? '';
        $email = $email ?? '';
        $id_instalacion = $id_instalacion ?? '';
        $fecha_hora_inicio = $fecha_hora_inicio ?? '';
        $fecha_hora_final = $fecha_hora_final ?? '';
        $estado = $estado ?? '';

        $this->query = "INSERT INTO reservas (nombre_solicitante, id_usuario, telefono, email, id_instalacion, fecha_hora_inicio, fecha_hora_final, estado) VALUES (:nombre_solicitante, :id_usuario, :telefono, :email, :id_instalacion, :fecha_hora_inicio, :fecha_hora_final, :estado)";

        $this->parametros['nombre_solicitante'] = $nombre_solicitante;
        $this->parametros['id_usuario'] = $id_usuario;
        $this->parametros['telefono'] = $telefono;
        $this->parametros['email'] = $email;
        $this->parametros['id_instalacion'] = $id_instalacion;
        $this->parametros['fecha_hora_inicio'] = $fecha_hora_inicio;
        $this->parametros['fecha_hora_final'] = $fecha_hora_final;
        $this->parametros['estado'] = $estado;

        $this->getResultFromQuery();

        $this->mensaje = 'Reserva creada';
    }

    // Para editar una reserva
    public function edit(){}

    // Para obtener reserva por id
    public function getById($id = ''){
        $this->query = "SELECT * FROM reservas WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Reserva encontrada';
    }

    // Para eliminar una reserva
    public function delete($id = ''){
        $this->query = "DELETE FROM reservas WHERE id = :id";
        $this->parametros['id'] = $id;
        $this->getResultFromQuery();
        $this->mensaje = 'Reserva eliminada';
    }
    

}

?>
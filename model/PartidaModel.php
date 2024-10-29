<?php

class PartidaModel{
  private $database;
    public function __construct($database){
        $this->database = $database;
    }

    public function getIdDeUsuarioActivo(){
        $sql = "SELECT id FROM usuario where activo = 1;";
        $usuarios = $this->database->query($sql);

        return $usuarios[0]['id'];
    }

    private function generarPartida(){
        $id_user = $this->getIdDeUsuarioActivo();
        if($id_user != null){
            $insert = "INSERT INTO partida(estado,puntaje,fecha,usuario_id)
                    values (1,0,CURRENT_DATE,$id_user);";
            $this->database->execute($insert);
        }
        $sql = "SELECT * FROM partida where estado = 1;";
        $partidas = $this->database->query($sql);

        return $partidas[0];
    }

    public function obtenerPregunta(){
        $sql = "SELECT id FROM pregunta where estado = 1 order by rand() limit 1;";
        $preguntas = $this->database->query($sql);

        return $preguntas[0]['id'];
    }

    public function getPartidaConSusPreguntas(){
        $partida = $this->generarPartida();
        $insert = "INSERT INTO partida_pregunta(partida_id,pregunta_id)
                   values (".$partida['id'].",".$this->obtenerPregunta().");";
        $this->database->execute($insert);

        $sql = "select pr.descripcion, op.descripcion 
                from pregunta pr 
                join partida_pregunta parp on pr.id = parp.pregunta_id
                join partida part on part.id = parp.partida_id
                join pregunta_opcion prop on prop.pregunta_id = parp.pregunta_id
                join opcion op on op.id = prop.opcion_incorrecta
                where op.id = pr.opcion_correcta;";

    }

}
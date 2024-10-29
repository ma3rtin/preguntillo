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

    public function generarPartida(){
        $id_user = $this->getIdDeUsuarioActivo();
        if($id_user != null){
            $sql = "INSERT INTO partida(estado,puntaje,fecha,usuario_id)
                    values (1,0,CURRENT_DATE,$id_user);";
            $this->database->execute($sql);
        }

    }
}
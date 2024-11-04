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

    private function generatePartida(){
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

    public function getPreguntaRandom(){
        $sql = "SELECT id FROM pregunta where estado_id = 1 order by rand() limit 1;";
        $preguntas = $this->database->query($sql);

        return $preguntas[0]['id'];
    }

    public function getGame(){
        $partida = $this->generatePartida();
        $insert = "INSERT INTO partida_pregunta(partida_id,pregunta_id)
                   values (".$partida['id'].",".$this->getPreguntaRandom().");";
        $this->database->execute($insert);

        $sql = "select pr.id,pr.pregunta_desc,incorrecta.id , incorrecta.opcion_desc,correcta.id ,correcta.opcion_desc
                from partida_pregunta parp
                join partida part on part.id = parp.partida_id
                join usuario us on us.id = part.usuario_id
                join pregunta pr on pr.id = parp.pregunta_id
                join opcion correcta on correcta.id = pr.opcion_correcta
                join pregunta_opcion prop on prop.pregunta_id = pr.id
                join opcion incorrecta on incorrecta.id = prop.opcion_incorrecta
                order by pr.id;";

        $question = $this->database->query($sql);

        return [
            'pregunta_desc' => $question[0]['pregunta_desc'],
            'opciones' => [
                ['id' => $question[0]['id'], 'correcta' => $question[0]['opcion_desc']],
                ['id' => $question[0]['id'], 'incorrecta' => $question[0]['opcion_desc']],
                ['id' => $question[0]['id'], 'incorrecta' => $question[0]['opcion_desc']]
            ]
        ];
    }

    /*Dividir las opciones correcta e incorrecta*/
    public function getCorrectAnswer(){
        $sql = "select id from opcion 
                where id in 
                (select opcion_correcta from pregunta 
                 where opcion_correcta = opcion.id );";
        return $this->database->execute($sql);
    }

    public function isCorrect(){
        $correctAnswer = $this->getCorrectAnswer();
        if($correctAnswer != null){
            $sql = "update pregunta 
                    set usuario_id
                    where opcion_correcta = $correctAnswer;";
            $this->database->execute($sql);
            return true;
        }
        return false;
    }

}
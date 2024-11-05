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

    public function getIncorrectAnswer($preguntaId){
        $sql = "select incorrecta.id as incorrecta_id, incorrecta.opcion_desc as incorrecta_desc
                from opcion incorrecta
                join pregunta_opcion prop on prop.opcion_incorrecta = incorrecta.id
                where prop.pregunta_id = $preguntaId;";
        return $this->database->query($sql);
    }

    public function getGame() {
        $partida = $this->generatePartida();
        if ($partida['id'] === null) {
            $insert = "INSERT INTO partida_pregunta(partida_id, pregunta_id)
                   VALUES (" . $partida['id'] . ", " . $this->getPreguntaRandom() . ");";
            $this->database->execute($insert);
        }

        $sql = "SELECT pr.id, pr.pregunta_desc,
                   correcta.id AS correcta_id, correcta.opcion_desc AS correcta_desc
            FROM partida_pregunta parp
            JOIN partida part ON part.id = parp.partida_id
            JOIN usuario us ON us.id = part.usuario_id
            JOIN pregunta pr ON pr.id = parp.pregunta_id
            JOIN opcion correcta ON correcta.id = pr.opcion_correcta
            JOIN pregunta_opcion prop ON prop.pregunta_id = pr.id";

        $question = $this->database->query($sql);
        $incorrectas = $this->getIncorrectAnswer($question[0]['id']);

        return [
            'pregunta_desc' => $question[0]['pregunta_desc'],
            'opciones' => [
                ['id' => $question[0]['correcta_id'], 'opcion_desc' => $question[0]['correcta_desc']],
                ['id' => $incorrectas[0]['incorrecta_id'], 'opcion_desc' => $incorrectas[0]['incorrecta_desc']],
                ['id' => $incorrectas[1]['incorrecta_id'], 'opcion_desc' => $incorrectas[1]['incorrecta_desc']]
            ]
        ];
    }

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
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
    public function getPreguntaRandom($usuarioId) {
        $sql = "SELECT p.id 
            FROM pregunta p
            WHERE NOT EXISTS (
                SELECT 1 
                FROM usuario_pregunta up
                WHERE up.pregunta_id = p.id AND up.usuario_id = $usuarioId
            )
            ORDER BY RAND() 
            LIMIT 1;";

        $preguntas = $this->database->query($sql);

        if (!empty($preguntas)) {
            return $preguntas[0]['id'];
        }

        return null;
    }

    public function getIncorrectAnswer($preguntaId){
        $sql = "select incorrecta.id as incorrecta_id, incorrecta.opcion_desc as incorrecta_desc
                from opcion incorrecta
                join pregunta_opcion prop on prop.opcion_incorrecta = incorrecta.id
                where prop.pregunta_id = $preguntaId;";
        return $this->database->query($sql);
    }

    public function getCorrectAnswer($preguntaId){
        $sql = "select id as correcta_id, opcion_desc as correcta_desc
                from opcion
                where id in 
                (select opcion_correcta from pregunta 
                 where opcion_correcta = opcion.id 
                 and pregunta.id = $preguntaId);";
        return $this->database->query($sql);
    }

    public function getGame($usuarioId) {
        $partida = $this->generatePartida();
        $pregunta = $this->getPreguntaRandom($usuarioId);
        if ($partida['id'] === null) {
            $insert = "INSERT INTO partida_pregunta(partida_id, pregunta_id)
                   VALUES (" . $partida['id'] . ", " . $pregunta . ");";
            $this->database->execute($insert);
        }

        $sql = "SELECT pr.pregunta_desc from pregunta pr
                where pr.id = $pregunta";

        $question = $this->database->query($sql);
        $incorrectas = $this->getIncorrectAnswer($pregunta);
        $correcta = $this->getCorrectAnswer($pregunta);

        return [
            'pregunta_desc' => $question[0]['pregunta_desc'],'pregunta_id' => $pregunta,
            'opciones' => [
                ['id' => $correcta[0]['correcta_id'], 'opcion_desc' => $correcta[0]['correcta_desc']],
                ['id' => $incorrectas[0]['incorrecta_id'], 'opcion_desc' => $incorrectas[0]['incorrecta_desc']],
                ['id' => $incorrectas[1]['incorrecta_id'], 'opcion_desc' => $incorrectas[1]['incorrecta_desc']],
                ['id' => $incorrectas[2]['incorrecta_id'], 'opcion_desc' => $incorrectas[2]['incorrecta_desc']]
            ]];
    }

    public function theAnswerIsCorrect($optionId,$preguntaId,$usuarioId){

        $correcta = $this->getCorrectAnswer($preguntaId);

        $update = "update partida set estado = 2 where usuario_id = $usuarioId;";
        $this->database->execute($update);

        if($optionId == $correcta[0]['correcta_id']){
            $insert = "insert into usuario_pregunta(usuario_id, pregunta_id,estado_id)
                       values($usuarioId,$preguntaId,2);";
            $this ->database->execute($insert);
            return true;
        }else{
            $insert = "insert into usuario_pregunta(usuario_id, pregunta_id,estado_id)
                       values($usuarioId,$preguntaId,1);";
            $this ->database->execute($insert);
            return false;
        }
    }
}
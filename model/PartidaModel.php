<?php

class PartidaModel {

    private $database;
    public function __construct($database){
        $this->database = $database;
    }

    public function run($sql){
        return $this->database->query($sql);
    }

    public function createPartida($id, $fecha, $hora) {
        $fechaInicio = date('Y-m-d', strtotime($fecha));
        $horaInicio = date('H:i:s', strtotime($hora));

        $sql = "INSERT INTO partida (usuario_id, puntaje, fecha, hora) VALUES ('$id', 0, '$fechaInicio', '$horaInicio')";

        $this->database->execute($sql);
    }


    public function actualizarPartida($partida_id){
        $sql = "SELECT id FROM partida WHERE id = $partida_id";
        $resultado = $this->database->query($sql);

        if (isset($resultado[0]['id'])) {
            $id = $resultado[0]['id'];
            $sqlUpdate = "UPDATE partida SET puntaje = puntaje + 1  WHERE id = $id";
            $this->database->execute($sqlUpdate);
        }
    }

    public function actualizarRanking($usuario_id,$nuevoPuntaje)
    {
        $sql = "SELECT usuario_id FROM ranking WHERE usuario_id = $usuario_id";
        $resultado = $this->database->query($sql);

        if(!empty($resultado)){
            $sql = "UPDATE ranking SET puntaje = $nuevoPuntaje";
            $this->database->execute($sql);

        }else{
            $sql = "INSERT INTO ranking (usuario_id, puntaje) VALUES ($usuario_id,$nuevoPuntaje)";
            $this->database->execute($sql);
        }
    }

    public function obtenerRankingConUsuarios()
    {
        $sql = "SELECT ranking.id, ranking.puntaje, usuario.usuario, usuario.nivel
            FROM ranking
            JOIN usuario ON ranking.usuario_id = usuario.id
            ORDER BY ranking.puntaje ASC";
        $resultado = $this->database->query($sql);

        return $resultado;
    }

    public function getPartidaPuntaje($usuario_id){
        $sql = "SELECT id, puntaje FROM partida WHERE usuario_id = $usuario_id ORDER BY id DESC LIMIT 1";
        $resultado = $this->database->query($sql);

        return isset($resultado[0]) ? $resultado[0] : false;
    }

    public function getPartidas($sort = false){
        if ($sort) {
            $sql = "SELECT usuario.id as usuario_id, usuario.usuario as nombre_usuairo, SUM(partida.puntaje) as puntaje
                    FROM partida
                    JOIN usuario ON partida.usuario_id = usuario.id
                    GROUP BY usuario.id
                    ORDER BY puntaje DESC";
        } else {
            $sql = "SELECT user_id, SUM(puntaje) as puntaje FROM partidas GROUP BY user_id";
        }

        return $this->database->query($sql);
    }

    public function getPartidasPDF(){
        return $this->getPartidas(true);
    }

    public function getPartidasUsuario($id){
        $sql = "SELECT * FROM partida WHERE usuario_id = $id ORDER BY fecha DESC, hora DESC LIMIT 5";
        return $this->database->query($sql);
    }

    public function getCantidadPartidasUsuario($id){
        $sql = "SELECT COUNT(*) as cantidad FROM partida WHERE usuario_id = $id";
        $result = $this->database->query($sql);
        return $result[0]['cantidad'] ?? 0;
    }

    public function getPuntajeUser($id){
        $sql = "SELECT SUM(puntaje) AS puntaje_total FROM partida WHERE usuario_id = $id";
        $resultado = $this->database->query($sql);

        return isset($resultado[0]['puntaje_total']) ? $resultado[0]['puntaje_total'] : 0;
    }

    public function getPartida($id){
        $sql = "SELECT * FROM partida WHERE usuario_id = $id";
        $resultado = $this->database->query($sql);

        return $resultado[0] ?? null;
    }

    public function getPartidasConCantidad(){
        $partidas = $this->getPartidas();
        $partidas['cantidad_de_partida'] = count($partidas);
        return $partidas;
    }

    public function getPartidasAPI(){
        $partidas['partidas'] = $this->getPartidas(true);
        $partidas['cantidad_partidas'] = count($partidas['partidas']);
        return $partidas;
    }

    public function getNivelUsuario($id) {
        if (!$id) {
            return null;
        }

        $sql = "SELECT nivel FROM usuario WHERE id = '$id'";
        $resultado = $this->database->query($sql);

        if (isset($resultado[0]['nivel'])) {
            return $resultado[0]['nivel'];
        }

        return 0;
    }


}
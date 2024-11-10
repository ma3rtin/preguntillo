<?php

class PartidaModel {

    private $database;
    public function __construct($database){
        $this->database = $database;
    }

    public function run($sql){
        return $this->database->query($sql);
    }

    public function createPartida($id){
        $sql = "INSERT INTO partida (usuario_id, puntaje) VALUES ($id, 0)";
        $this->database->execute($sql);
    }

    public function actualizarPartida($usuario_id, $puntaje){
        $sql = "SELECT id FROM partida WHERE usuario_id = $usuario_id ORDER BY id DESC LIMIT 1";
        $resultado = $this->database->query($sql);

        if (isset($resultado[0]['id'])) {
            $id = $resultado[0]['id'];
            $sqlUpdate = "UPDATE partida SET puntaje = $puntaje WHERE id = $id";
            $this->database->query($sqlUpdate);
        }
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

    public function getPartidasUser($id){
        $sql = "SELECT * FROM partida WHERE usuario_id = $id";
        return $this->database->query($sql);;
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

    public function preguntaContestada($id){
        $sql = "UPDATE pregunta SET contestada = contestada + 1 WHERE id = $id";
        $this->database->query($sql);
    }

    public function getNivelUsuario($id){
        if (!$id) {
            return null;
        }

        $sql = "SELECT AVG(puntaje) AS promedio FROM partida WHERE usuario_id = '$id'";
        $modelResponse = $this->database->query($sql);

        return isset($modelResponse[0]['promedio']) ? number_format($modelResponse[0]['promedio'] / 10, 2) : 0;
    }

}
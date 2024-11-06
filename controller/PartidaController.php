<?php
class PartidaController{

    private $model;
    private $presenter;

    public function __construct($model, $presenter){
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function startGame(){
       return $this->model->getGame($_SESSION["id"]);
    }

    public function showGame(){
        $data["partidas"] = $this->startGame();
        return $this->presenter->show('partida', $data);
    }

    public function isCorrect(){
        $optionId = $_POST["option"];
        $preguntaId = $_POST["pregunta_id"];
        $usuarioId = $_SESSION["id"];
        $data["mensaje"] = "Corrrrrecctttooooooooooooo";

        if($this->model->theAnswerIsCorrect($optionId, $preguntaId, $usuarioId)){
            $this->presenter->show('partida', $data);

        }else{
            //esto es para probar
            echo "Respuesta incorrecta";
        }
    }


}
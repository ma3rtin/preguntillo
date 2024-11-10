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
        if($data["partidas"] != null){
            return $this->presenter->show('partida', $data);
        }else{
           header("location: /home");
           exit();
        }
    }

    public function isCorrect(){
        $optionId = $_POST["option"];
        $preguntaId = $_POST["pregunta_id"];
        $usuarioId = $_SESSION["id"];

        if($this->model->theAnswerIsCorrect($optionId, $preguntaId, $usuarioId)){
            $data["partidas"] = $this->startGame();
            $data['message'] = "respuesta correcta";
            $this->presenter->show('partida', $data);
        }else{
            $data["partidas"] = $this->startGame();
            $data['message'] = "respuesta incorrecta";
            $this->presenter->show('partida', $data);
        }
    }


}
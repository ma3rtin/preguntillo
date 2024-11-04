<?php
class PartidaController{

    private $model;
    private $presenter;

    public function __construct($model, $presenter){
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function startGame(){
       return $this->model->getGame();
    }

    public function showGame(){
        $data["partidas"] = $this->startGame();
        return $this->presenter->show('partida', $data);
    }

    /*En el controller tiene que estar el metodo de seleccionar la respuesta*/
    public function chooseAnswer(){

    }

}
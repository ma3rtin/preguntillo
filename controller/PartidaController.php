<?php
class PartidaController{

    private $model;
    private $presenter;

    public function __construct($model, $presenter){
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function llamarPartida(){
       return $this->model->getPartida();
    }

    public function mostrarPartida()
    {

    }

}
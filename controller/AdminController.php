<?php

class AdminController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function showStadistics()
    {
        if($_SESSION['admin']){
            $data['cantJugadores'] = $this->model->getCantJugadores();
            $data['cantPartidas'] = $this->model->getCantPartidas();
            $data['cantPreguntas'] = $this->model->getCantPreguntas();

            $data['usuarios'] = $this->model->getEstadisticasDeUsuarios();
            $data['css'] = '/public/css/estadisticas.css';

            $this->presenter->show('estadisticas', $data);
        }else{
            header('location: /loginForm');
            exit();
        }
    }
}
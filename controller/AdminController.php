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
        $data['cantJugadores'] = $this->model->getCantJugadores();
        $data['cantPartidas'] = $this->model->getCantPartidas();
        $data['cantPreguntas'] = $this->model->getCantPreguntas();

        $data['usuarios'] = $this->model->getEstadisticasDeUsuarios();

        $this->presenter->show('estadisticas', $data);
    }
}
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
    public function imprimirReporte()
    {
        if ($_SESSION['admin']) {
            require_once __DIR__ . '/../helper/PDF.php';

            $cantJugadores = $this->model->getCantJugadores();
            $cantPartidas = $this->model->getCantPartidas();
            $cantPreguntas = $this->model->getCantPreguntas();
            $data['usuarios'] = $this->model->getEstadisticasDeUsuarios();

            $pdf = new PDF();
            $pdf->agregarTitulo('Reporte de estadísticas');
            $pdf->agregarEstadisticasGenerales($cantJugadores, $cantPartidas, $cantPreguntas);
            $pdf->agregarTablaUsuarios($data);
            $pdf->generarReporte();
        } else {
            header('location: /loginForm');
            exit();
        }
    }
}
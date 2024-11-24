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
            $data['js'] = '/public/js/estadisticas.js';

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

    public function obtenerNuevosUsuarios()
    {
        $cantDias = $_GET['dias'] ?? 7;
        $data = $this->model->getCantJugadoresNuevos($cantDias);
        echo json_encode($data);
    }

    public function showGraphs()
    {
        if($_SESSION['admin']){
            $data['css'] = '/public/css/graficos.css';
            $data['js'] = '/public/js/graficos.js';

            $this->presenter->show('graficos', $data);
        }else{
            header('location: /loginForm');
            exit();
        }
    }

    public function cantJugadoresPorGenero()
    {
        $dias = $_GET['dias'] ?? null;
        $data = $this->model->getCantPorGenero($dias);
        echo json_encode($data);
    }

    public function cantJugadoresPorPais()
    {
        $dias = $_GET['dias'] ?? null;
        $data = $this->model->getCantPorPais($dias);
        echo json_encode($data);
    }

    public function cantJugadoresPorEdad()
    {
        $dias = $_GET['dias'] ?? null;
        $data = $this->model->getCantPorEdad($dias);
        echo json_encode($data);
    }

}
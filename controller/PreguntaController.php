<?php

require_once 'C:\xampp\htdocs\PW2\preguntillo\vendor\sdk-php\src\MercadoPago\MercadoPagoConfig.php';


use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
class PreguntaController{

    private $usuarioModel;
    private $preguntaModel;
    private $partidaModel;
    private $opcionModel;
    private $presenter;
    private $trampitaModel;
    public function __construct($usuarioModel, $preguntaModel, $partidaModel, $opcionModel, $presenter, $trampitaModel) {
        $this->usuarioModel = $usuarioModel;
        $this->preguntaModel = $preguntaModel;
        $this->partidaModel = $partidaModel;
        $this->opcionModel = $opcionModel;
        $this->presenter = $presenter;
        $this->trampitaModel = $trampitaModel;
    }

    public function list() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function show(){
        $data['css'] = "/public/css/pregunta.css";
        $idPregunta = $_GET['params'] ?? $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
        $this->usuarioModel->registrarPreguntaEntregada($_SESSION['id']);
        if($idPregunta == null){
            Redirect::to('/juego/perdido');
        }
        $data['pregunta'] = $this->preguntaModel->getPreguntaById($idPregunta);
        $data['pregunta_id'] = $idPregunta;
        $data['opciones'] = $this->opcionModel->getOpciones($idPregunta);
        $data['categoria'] = $this->preguntaModel->getCategoria($idPregunta);

        $this->presenter->show('pregunta', $data);
    }


    public function validarOpcion(){
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['css'] = "/public/css/pregunta.css";

        $pregunta_id=  $_POST['pregunta_id'];
        $opcionSeleccionada = $_POST['opcion_id'];

        $this->partidaModel->preguntaContestada($pregunta_id, $_SESSION['id'] );

        $opcionCorrecta = $this->opcionModel->getOpcionCorrecta($pregunta_id)[0];

        if ($opcionSeleccionada == $opcionCorrecta['id']){
            $data['opcionEsCorrecta']= "La es opcion correcta ";

            $this->preguntaModel->actualizarDificultad($pregunta_id, true);
            $this->usuarioModel->actualizarNivelPorRespuestaCorrecta($_SESSION['id']);

            $partida = $this->partidaModel->getPartidasUsuario($_SESSION['id'])[0];
            $this->partidaModel->actualizarPartida($partida['id']);

            $data['siguiente_id'] = $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
            $data['correcta'] = true;
            $data['pregunta'] = $this->preguntaModel->getPreguntaById($pregunta_id);
            $data['pregunta_id'] = $pregunta_id;
            $data['opciones'] = $this->opcionModel->getOpciones($pregunta_id);
            $data['categoria'] = $this->preguntaModel->getCategoria($pregunta_id);

            $this->presenter->authView($data['userSession'],'pregunta',$data);
        }else{
            $data['opcionEsCorrecta']= "fin ";

            Redirect::to('/juego/perdido');
        }
    }

    public function sugerir(){
        $data['modulos'] = $this->preguntaModel->getAllModules();
        $data['tipos'] = $this->preguntaModel->getAllTypes();

        $this->presenter->show('sugerirPregunta', $data);
    }

    public function reporteForm()
    {
        $data['pregunta_id'] = $_GET['pregunta'];

        $this->presenter->show('reporte', $data);
    }

    public function reportar()
    {
        $pregunta_id = $_POST['pregunta_id'];
        $caso = $_POST['caso'];
        $mensaje = $_POST['mensaje'];
        $user_id = $_SESSION['id'];

        $this->preguntaModel->crearReporte($user_id, $pregunta_id, $caso, $mensaje);

        Redirect::to('/usuario/home');
    }

        public function comprarTrampita() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $cantidad = (int) $_POST['cantidad'];
                if ($cantidad > 0) {
                    $userId = $_SESSION['id'];
                    $nuevasTrampitas = $this->trampitaModel->comprarTrampita($userId);
                    echo json_encode(['success' => true, 'nuevasTrampitas' => $nuevasTrampitas]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Cantidad inválida de trampitas.']);
                }
            }
        }

        public function usarTrampita()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $cantidadUsar = (int) $_POST['cantidadUsar'];
                $userId = $_SESSION['id'];
                if ($this->trampitaModel->getTrampitas()>=$cantidadUsar) {
                    $this->trampitaModel->usarTrampitas($userId);
                    echo json_encode(['success' => true, 'mensaje' => 'Trampitas usadas correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'No tienes suficientes trampitas.']);
                }
            }
        }

    // Ver ventas totales de trampitas (solo para el administrador)
    public function ventasTrampitas() {
        // Solo el administrador puede acceder a esta vista
        if ($_SESSION['role'] != 'admin') {
            Redirect::to('/usuario/home');
        }

        // Obtener las ventas totales
        $data['totalVentas'] = $this->trampitaModel->obtenerVentasTotales();

        // Mostrar el reporte al administrador
        $this->presenter->authView($data['userSession'], 'admin', $data);
    }
}
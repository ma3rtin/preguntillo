<?php
class PreguntaController{

    private $usuarioModel;
    private $preguntaModel;
    private $partidaModel;
    private $opcionModel;
    private $presenter;
    public function __construct($usuarioModel, $preguntaModel, $partidaModel, $opcionModel, $presenter) {
        $this->usuarioModel = $usuarioModel;
        $this->preguntaModel = $preguntaModel;
        $this->partidaModel = $partidaModel;
        $this->opcionModel = $opcionModel;
        $this->presenter = $presenter;
    }

    public function list() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function modulo() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['preguntasByModule'] = $this->preguntaModel->getAllBy($_GET['name']);

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function show(){
        $data['css'] = "/public/css/pregunta.css";
//        $data['puntaje'] = $this->partidaModel->getPartidaPuntaje($data['id']);
        $idPregunta = $_GET['params'] ?? $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
        $this->usuarioModel->registrarPreguntaEntregada($_SESSION['id']);
//        $_SESSION['tiempo_inicio'] = time();
        if($idPregunta == null){
            Redirect::to('/juego/perdido');
        }
        $data['pregunta'] = $this->preguntaModel->getPreguntaById($idPregunta);
        $data['pregunta_id'] = $idPregunta;
        $data['opciones'] = $this->opcionModel->getOpciones($idPregunta);

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

            $this->presenter->authView($data['userSession'],'pregunta',$data);
            //$this->presenter->show('pregunta', $data);
            //Redirect::to("/pregunta/show/$pregunta_id");
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
}
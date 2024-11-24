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

    public function show(){
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        $data['css'] = "/public/css/pregunta.css";
        $idPregunta = $_GET['params'] ?? $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
        $idPregunta = $_SESSION['pregunta_id'] ?? $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
        $_SESSION['pregunta_id'] = $idPregunta;

        $this->usuarioModel->registrarPreguntaEntregada($_SESSION['id']);
        if($idPregunta == null){
            Redirect::to('/juego/perdido');
        }
        $data['pregunta'] = $this->preguntaModel->getPreguntaById($idPregunta);
        $data['pregunta_id'] = $idPregunta;
        $data['opciones'] = $this->opcionModel->getOpciones($idPregunta);
        $data['categoria'] = $this->preguntaModel->getCategoria($idPregunta);
        $_SESSION['hora_inicio'] = time();

        $tiempoInicio = $_SESSION['pregunta_start_time'] ?? time();
        $_SESSION['pregunta_start_time'] = $tiempoInicio;

        $tiempoTotal = 20;
        $tiempoTranscurrido = time() - $tiempoInicio;
        $tiempoRestante = max(0, $tiempoTotal - $tiempoTranscurrido);

        if($tiempoRestante <= 0){
            unset($_SESSION['pregunta_id']);
            unset($_SESSION['pregunta_start_time']);
            Redirect::to('/juego/perdido');
            return;
        }

        $data['tiempoRestante'] = $tiempoRestante;

        $this->presenter->show('pregunta', $data);
    }


    public function validarOpcion() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['css'] = "/public/css/pregunta.css";

        $pregunta_id = $_POST['pregunta_id'];
        $opcionSeleccionada = $_POST['opcion_id'];

        $horaInicio = $_SESSION['hora_inicio'] ?? time();
        $tiempoTranscurrido = time() - $horaInicio;

        if ($tiempoTranscurrido > 20) {
            Redirect::to('/juego/perdido');
            return;
        }

        $this->partidaModel->preguntaContestada($pregunta_id, $_SESSION['id']);
        $opcionCorrecta = $this->opcionModel->getOpcionCorrecta($pregunta_id)[0];

        if ($opcionSeleccionada == $opcionCorrecta['id']) {
            $data['opcionEsCorrecta'] = "La opción es correcta";

            $this->preguntaModel->actualizarDificultad($pregunta_id, true);
            $this->usuarioModel->actualizarNivelPorRespuestaCorrecta($_SESSION['id']);

            $partida = $this->partidaModel->getPartidasUsuario($_SESSION['id'])[0];
            $this->partidaModel->actualizarPartida($partida['id']);

            $siguiente_id = $this->preguntaModel->getPreguntaRandom($_SESSION['id']);
            if ($siguiente_id === null) {
                Redirect::to('/juego/perdido');
                return;
            }

            $data['siguiente_id'] = $siguiente_id;
            $_SESSION['pregunta_id'] = $siguiente_id; // Asignar solo si existe

            $data['correcta'] = true;
            $data['pregunta'] = $this->preguntaModel->getPreguntaById($pregunta_id);
            $data['pregunta_id'] = $pregunta_id;
            $data['opciones'] = $this->opcionModel->getOpciones($pregunta_id);
            $data['categoria'] = $this->preguntaModel->getCategoria($pregunta_id);

            $this->presenter->authView($data['userSession'], 'pregunta', $data);
        } else {
            $data['opcionEsCorrecta'] = "Fin";
            Redirect::to('/juego/perdido');
        }
    }


    public function sugerir(){
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        $data['modulos'] = $this->preguntaModel->getAllModules();
        $data['tipos'] = $this->preguntaModel->getAllTypes();

        $this->presenter->show('sugerirPregunta', $data);
    }

    public function reporteForm() {
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        $data['pregunta_id'] = $_GET['pregunta'];

        $this->presenter->show('reporte', $data);
    }

    public function reportar(){
        $pregunta_id = $_POST['pregunta_id'];
        $caso = $_POST['caso'];
        $mensaje = $_POST['mensaje'];
        $user_id = $_SESSION['id'];

        $this->preguntaModel->crearReporte($user_id, $pregunta_id, $caso, $mensaje);

        Redirect::to('/usuario/home');
    }
}
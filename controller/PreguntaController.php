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

    public function show() {
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        $data['css'] = "/public/css/pregunta.css";
        $usuarioId = $data['user']['id'];

        $idPregunta = $this->preguntaModel->getIdUltimaPreguntaNoRespondida($_SESSION['id']);

        if ($idPregunta == null) {
            $idPregunta = $this->preguntaModel->getPreguntaRandom($usuarioId);
            Redirect::to('/juego/perdido');
        }

        $_SESSION['pregunta_id'] = $idPregunta;

        $_SESSION['pregunta_start_time'] = $_SESSION['pregunta_start_time'] ?? time();

        $tiempoTranscurrido = time() - $_SESSION['pregunta_start_time'];
        $tiempoTotal = 20;
        $tiempoRestante = max(0, $tiempoTotal - $tiempoTranscurrido);

        $data['tiempoRestante'] = $tiempoRestante;

        $data['pregunta'] = $this->preguntaModel->getPreguntaById($idPregunta);
        $data['pregunta_id'] = $idPregunta;
        $data['opciones'] = $this->opcionModel->getOpciones($idPregunta);
        $data['categoria'] = $this->preguntaModel->getCategoria($idPregunta);

        $this->preguntaModel->preguntaMostrada($usuarioId,$idPregunta);

        $this->presenter->show('pregunta', $data);
    }

    public function validarOpcion() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['css'] = "/public/css/pregunta.css";

        $_SESSION['pregunta_start_time'] = time();

        unset($_SESSION['pregunta_id']);

        $pregunta_id = $_POST['pregunta_id'];
        $opcionSeleccionada = $_POST['opcion_id'];

        $horaInicio = $_SESSION['pregunta_start_time'];
        $tiempoTranscurrido = time() - $horaInicio;

        if ($tiempoTranscurrido > 20) {
            Redirect::to('/juego/perdido');
            return;
        }

        $this->preguntaModel->preguntaContestada($pregunta_id, $_SESSION['id']);
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
            $_SESSION['pregunta_id'] = $siguiente_id;

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


//    public function sugerir(){
//        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
//        $data['categorias'] = $this->preguntaModel->getAllCategorias();
//
//        $this->presenter->show('sugerirPregunta', $data);
//    }

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

    public function sugerirPregunta(){
        $data['css'] = "/public/css/crearPreguntas.css";
        $data['categorias'] = $this->preguntaModel->getAllCategorias();

        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        $this->presenter->show('sugerirPregunta', $data);
    }

    public function sugerir()
    {
        $pregunta = $_POST['pregunta'] ?? null;
        $opciones = $_POST['opciones'] ?? [];
        $respuestaCorrecta = $_POST['respuesta_correcta'] ?? null;
        $categoria = $_POST['categoria_id'] ?? null;

        $data['css'] = "/public/css/crearPreguntas.css";
        if ($pregunta && $opciones && $respuestaCorrecta && count($opciones) === 4 && $categoria) {
            $opcionCorrecta = $opciones[$respuestaCorrecta] ?? null;

            if ($opcionCorrecta) {
                $this->preguntaModel->sugerirPregunta($pregunta, $opciones, $respuestaCorrecta, $categoria);

                $data['exito'] = "Pregunta sugerida con exito.";
                $this->presenter->show('sugerirPregunta', $data);
            } else {
                $data['error'] = "La opción correcta no es válida.";
                $this->presenter->show('sugerirPregunta', $data);
            }
        } else {
            $data['error'] = "Todos los campos son obligatorios.";
            $this->presenter->show('sugerirPregunta', $data);
        }
    }

}
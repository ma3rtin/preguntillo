<?php
class JuegoController{

    private $usuarioModel;
    private $preguntaModel;
    private $partidaModel;
    private $presenter;

    public function __construct($usuarioModel, $preguntaModel, $partidaModel, $presenter) {
        $this->usuarioModel = $usuarioModel;
        $this->preguntaModel = $preguntaModel;
        $this->partidaModel = $partidaModel;
        $this->presenter = $presenter;
    }

    public function list() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['categoria'] = $this->preguntaModel->getCategoria();
        $this->presenter->authView($data['userSession'],'juego', $data);
    }

    public function perdido() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
//        $data['error'] = $_SESSION['error'];
//        unset($_SESSION['error']);
        $nuevoPuntaje = $this->partidaModel->getPuntajeUser($_SESSION['id']);
        $this->partidaModel->actualizarRanking($_SESSION['id'],$nuevoPuntaje);
        $this->presenter->authView($data['userSession'],'juegoPerdido', $data);
    }

    public function crear() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['modulo'] = $this->preguntaModel->getModulos();
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);

        $fechaInicio = date('Y-m-d');
        $horaInicio = date('H:i:s');

        $this->partidaModel->createPartida($_SESSION['id'], $fechaInicio, $horaInicio);

        $idRandom = $this->preguntaModel->getPreguntaRandom($_SESSION['id']);

        Redirect::to("/pregunta/show/$idRandom");
    }

    public function verRanking() {
        $data['css'] = "/public/css/ranking.css";
        $data['ranking'] = $this->partidaModel->obtenerRankingConUsuarios();

        $this->presenter->show('ranking', $data);
    }
}
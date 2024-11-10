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
        $data['modulos'] = $this->preguntaModel->getModules();
        $this->presenter->authView($data['userSession'],'juego', $data);
    }

    public function ganado() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $this->presenter->authView($data['userSession'],'juegoGanado', $data);
    }

    public function perdido() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['error'] = $_SESSION['error'];
        unset($_SESSION['error']);
        $this->presenter->authView($data['userSession'],'juegoPerdido', $data);
    }

    public function crear() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['modulo'] = $this->preguntaModel->getModulos();
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);

        $data['id'] = $_SESSION['id'];
        $this->partidaModel->createPartida($data['id']);
//        echo '<script>localStorage.removeItem("tiempoRestante");</script>';
        $idRandom = $this->preguntaModel->getRandomId();

        Redirect::to("/pregunta/show/$idRandom");
    }
}
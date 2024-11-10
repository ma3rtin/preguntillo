<?php
class PartidaController{

    private $usuarioModel;
    private $partidaModel;

    private $presenter;

    public function __construct($usuarioModel, $partidaModel, $presenter) {
        $this->usuarioModel = $usuarioModel;
        $this->partidaModel = $partidaModel;
        $this->presenter = $presenter;
    }

    public function list() {

        if (!isset($_SESSION['id'])) {
            header('Location: /login');
            exit();
        }
        $data = [
            'partidas' => $this->partidaModel->getPartidasUser($_SESSION['id']),
            'userSession' => $this->usuarioModel->getCurrentSession(),
            'error' => $_SESSION['error'],
            'success' => $_SESSION['success'],
        ];

        $this->presenter->authView($data['userSession'],'partida', $data,'/login');
    }
}
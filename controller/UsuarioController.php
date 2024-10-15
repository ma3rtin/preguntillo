<?php


class UsuarioController
{

    private $model;
    private $presenter;

    public function  __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function loginForm()
    {
        $this->presenter->show('login', []);
    }

    public function registerForm()
    {
        $this->presenter->show('register', []);
    }

    public function register()
    {
        $_POST['user'] = isset($_POST['user']) ? $_POST['user'] : null;
        $_POST['name'] = isset($_POST['name']) ? $_POST['name'] : null;
        $_POST['email'] = isset($_POST['email']) ? $_POST['email'] : null;
        $_POST['pass'] = isset($_POST['pass']) ? $_POST['pass'] : null;
        $_POST['birthyear'] = isset($_POST['birthyear']) ? $_POST['birthyear'] : null;
        $_POST['photo'] = isset($_POST['photo']) ? $_POST['photo'] : null;

        if ($_POST['user'] == null || $_POST['name'] == null || $_POST['email'] == null || $_POST['pass'] == null || $_POST['birthyear'] == null) {
            $this->presenter->show('register', ["error" => "Todos los campos son obligatorios"]);
            return;
        }
        $this->model->register($_POST['user'], $_POST['name'], $_POST['email'], $_POST['pass'], $_POST['birthyear'], $_POST['photo']);
        $this->presenter->show('login', []);
    }
}
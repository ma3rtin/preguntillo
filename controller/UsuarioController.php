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

    public function login()
    {
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $pass = isset($_POST['pass']) ? $_POST['pass'] : null;

        if (is_null($username) || is_null($pass)) {
            $this->presenter->show('login', ["error" => "Todos los campos son obligatorios"]);
            return;
        }
        $this->model->validate($username, $pass) ?
                    $this->redirectHome()
                    : $this->presenter->show('login', ["error" => "Usuario o contraseña incorrectos"]);

    }

    public function registerForm()
    {
        $this->presenter->show('register', []);
    }

    public function register()
    {
        $user = isset($_POST['user']) ? $_POST['user'] : null;
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        $pass = isset($_POST['pass']) ? $_POST['pass'] : null;
        $birtihyear = isset($_POST['birthyear']) ? $_POST['birthyear'] : null;
        $photo = ($_FILES['photo']['size'] > 0) ? $_FILES['photo'] : null;

        if (is_null($user) || is_null($name) || is_null($email) || is_null($pass) || is_null($birtihyear)
         || is_null($photo)) {
            $this->presenter->show('register', ["error" => "Todos los campos son obligatorios"]);
            return;
        }
        $this->model->register($user, $name, $email, $pass, $birtihyear, $photo);
        $this->presenter->show('login', []);
    }

    public function profile()
    {
        $username = isset($_GET['username']) ? $_GET['username'] : null;
        $data['user'] = $this->model->getUserData($username);
        $this->presenter->show('profile', $data);
    }


    public function home()
    {
        $data['user'] = $this->model->getUserData($_SESSION['username']);
        $this->presenter->show('home', $data);
    }

    public function redirectHome()
    {
        header('location: /usuario/home');
        exit();
    }
}
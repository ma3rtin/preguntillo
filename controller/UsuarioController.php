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
        $data['css'] = '/public/css/loginForm.css';
        $this->presenter->show('login', $data);
    }

    public function login()
    {
        $data['css'] = '/public/css/loginForm.css';
        $username = isset($_POST['username']) ? $_POST['username'] : null;
        $pass = isset($_POST['pass']) ? $_POST['pass'] : null;

        if (empty($username) || empty($pass)) {
            $data['error'] = "Todos los campos son obligatorios";
            $this->presenter->show('login', $data);
            return;
        }
        $this->model->validate($username, $pass) ?
                    $this->redirectHome()
                    : $this->presenter->show('login', ["error" => "Usuario o contraseña incorrectos"]);

    }

    public function registerForm()
    {
        $data['css'] = '/public/css/registerForm.css';
        $this->presenter->show('register', $data);
    }

    public function register()
    {
        $user = isset($_POST['user']) ? $_POST['user'] : null;
        $name = isset($_POST['name']) ? $_POST['name'] : null;
        $email = isset($_POST['email']) ? $_POST['email'] : null;
        $pass = isset($_POST['pass']) ? $_POST['pass'] : null;
        $birthyear = isset($_POST['birthyear']) ? $_POST['birthyear'] : null;
        $photo = ($_FILES['photo']['size'] > 0) ? $_FILES['photo'] : null;

        if (is_null($user) || is_null($name) || is_null($email) || is_null($pass) || is_null($birthyear)
         || is_null($photo)) {
            $this->presenter->show('register', ["error" => "Todos los campos son obligatorios"]);
            return;
        }
        $this->model->register($user, $name, $email, $pass, $birthyear, $photo);
        header("location: /usuario/login");
    }

    public function validateEmail()
    {
        $token = isset($_GET['token']) ? $_GET['token'] : null;
        $userId = isset($_GET['userid']) ? $_GET['userid'] : null;

        if (is_null($token) || is_null($userId)) {
            $this->presenter->show('validateEmail', ["error" => "Usuario y token requeridos"]);
            return;
        }
        if($this->model->validateToken($token, $userId))
            $this->presenter->show('login', ["success" => "Cuenta verificada"]);
        else
            $this->presenter->show('login', ["error" => "Token invalido"]);
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
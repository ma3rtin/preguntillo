<?php
class UsuarioController
{

    private $model;
    
    private $emailSender;
    private $presenter;

    public function  __construct($model, $emailSender, $presenter)
    {
        $this->model = $model;
        $this->emailSender = $emailSender;
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
        $user = $this->model->validate($username, $pass);
        if(!$user){
            $data['error'] = "Usuario o contraseña incorrectos";
            $this->presenter->show('login', $data);
        }else if(!$user['activo']){
            $data['error'] = "Correo electrónico no validado.";
            $this->presenter->show('login', $data);
        }
        else{
            $_SESSION['username'] = $user['usuario'];
            $_SESSION['id'] = $user['id'];
            $this->redirectHome();
        }
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
        $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : null;
        $birthyear = isset($_POST['birthyear']) ? $_POST['birthyear'] : null;
        $photo = ($_FILES['photo']['size'] > 0) ? $_FILES['photo'] : null;

        if (is_null($user) || is_null($name) || is_null($email) || is_null($pass) || is_null($pass2) || is_null($birthyear)
         || is_null($photo)) {
            $data['css'] = "/public/css/registerForm.css";
            $data['error'] = "Todos los campos son obligatorios";
            $this->presenter->show('register', $data);
            return;
        }

        if ($pass != $pass2) {
            $data['css'] = "/public/css/registerForm.css";
            $data['error'] = "Las contraseñas no coinciden";
            $this->presenter->show('register', $data);
            return;
        }

        $data = $this->model->register($user, $name, $email, $pass, $birthyear, $photo);

        $this->emailSender->sendValidationMail($data[0], $data[1], $data[2]);

        header("location: /usuario/loginForm");
        exit();
    }

    public function validateEmail()
    {
        $token = isset($_GET['token']) ? $_GET['token'] : null;
        $userId = isset($_GET['id']) ? $_GET['id'] : null;
        $data['css'] = '/public/css/loginForm.css';

        if (empty($token) || empty($userId))
            $data['error'] = "Usuario y token requeridos";

        $this->model->validateToken($token, $userId) ?
            $data['success'] = "Cuenta verificada" : $data['error'] = "Token invalido";

        $this->presenter->show('login', $data);
    }

    public function profile()
    {
        $username = isset($_GET['username']) ? $_GET['username'] : null;
        $data['css'] = "/public/css/profile.css";
        $data['user'] = $this->model->getUserData($username);
        $this->presenter->show('profile', $data);
    }


    public function home()
    {
        $data['css'] = "/public/css/home.css";
        $data['user'] = $this->model->getUserData($_SESSION['username']);
        $this->presenter->show('home', $data);
    }

    public function redirectHome()
    {
        header('location: /usuario/home');
        exit();
    }
}
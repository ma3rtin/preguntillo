<?php
class UsuarioController
{
    private $model;
    private $emailSender;
    private $presenter;
    private $qrMaker;

    public function  __construct($model, $qrMaker,$emailSender, $presenter)
    {
        $this->model = $model;
        $this->qrMaker = $qrMaker;
        $this->emailSender = $emailSender;
        $this->presenter = $presenter;
    }

    public function loginForm()
    {
        if (isset($_SESSION['username'])) {
            $data = ['username' => $_SESSION['username']];
        }

        $data['css'] = '/public/css/loginForm.css';
        $this->presenter->show('login', $data);
    }

    public function logIn()
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
            $_SESSION['admin'] = $user['rol'] == 'ADMIN';
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
        $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

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

        $data = $this->model->register($user, $name, $email, $pass, $birthyear, $photo, $latitude, $longitude);

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
        $latitud = $data['user']['latitud'];
        $longitud = $data['user']['longitud'];
        $apiKey = 'AIzaSyCUkdndYG9gSK35o6qXfqaG1w8i5oj1TGA';
        $data['mapa'] = "https://maps.googleapis.com/maps/api/staticmap?center={$latitud},{$longitud}&zoom=15&size=600x300&markers=color:red%7Clabel:U%7C{$latitud},{$longitud}&key={$apiKey}";
        $data['qr'] = $this->qrMaker->createQRCode("http://localhost/usuario/profile/username=" . $data['user']['usuario']);

        $this->presenter->show('profile', $data);
    }


    public function home()
    {
        if (isset($_SESSION['id'])){
            $data['css'] = "/public/css/home.css";
            $data['user'] = $this->model->getUserData($_SESSION['username']);
            $data['admin'] = $_SESSION['admin'];
            $this->presenter->show('home', $data);
        }
        else{
            $this->redirectLoginForm();
        }
    }

    public function logOut()
    {
        session_start();
        session_unset();
        session_destroy();
        $this->redirectLoginForm();
    }

    public function redirectHome()
    {
        header('location: /usuario/home');
        exit();
    }

    public function redirectLoginForm()
    {
        header('location: /loginForm');
        exit();
    }
}
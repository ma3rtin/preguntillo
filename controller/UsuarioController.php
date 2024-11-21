<?php
class UsuarioController
{
    private $model;
    private $emailSender;
    private $presenter;
    private $qrMaker;
    private $partidaModel;

    public function  __construct($model, $qrMaker,$emailSender, $partidaModel, $presenter)
    {
        $this->model = $model;
        $this->qrMaker = $qrMaker;
        $this->emailSender = $emailSender;
        $this->partidaModel = $partidaModel;
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
            switch($user['rol']){
                case 'ADMIN':
                    $_SESSION['admin'] = true;
                    break;
                case 'USER':
                    $_SESSION['jugador'] = true;
                    break;
                case 'EDITOR':
                    $_SESSION['editor'] = true;
                    break;
            }
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
        $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
        $country = isset($_POST['country']) ? $_POST['country'] : null;
        $photo = ($_FILES['photo']['size'] > 0) ? $_FILES['photo'] : null;
        $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

        if (is_null($user) || is_null($name) || is_null($email) || is_null($pass) || is_null($pass2) || is_null($birthyear)
            || is_null($gender) || is_null($country) || is_null($photo)) {
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

        $data = $this->model->register($user, $name, $email, $pass, $birthyear, $gender, $country, $photo, $latitude, $longitude);

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
        $data['partidas'] = $this->partidaModel->getCantidadPartidasUsuario($data['user']['id']);
        $data['puntaje'] = $this->partidaModel->getPuntajeUser($data['user']['id']);
        $data['nivel'] = $this->partidaModel->getNivelUsuario($data['user']['id']);
        $latitud = $data['user']['latitud'];
        $longitud = $data['user']['longitud'];
        $apiKey = 'AIzaSyCUkdndYG9gSK35o6qXfqaG1w8i5oj1TGA';
        $data['mapa'] = "https://maps.googleapis.com/maps/api/staticmap?center={$latitud},{$longitud}&zoom=15&size=600x300&markers=color:red%7Clabel:U%7C{$latitud},{$longitud}&key={$apiKey}";
        $data['qr'] = $this->qrMaker->createQRCode("http://localhost/usuario/profile/username=" . $data['user']['usuario']);

        $this->presenter->show('profile', $data);
    }


    public function home()
    {
        $data = $this->verificarSesion();
        $data['css'] = "/public/css/home.css";
        $data['partidas'] = $this->partidaModel->getPartidasUsuario($data['user']['id']);
        $this->presenter->show('home', $data);
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

    public function verificarSesion(){
        if(isset($_SESSION['id'])){
            $data['user'] = $this->model->getUserData($_SESSION['username']);

            if(isset($_SESSION['admin']) && $_SESSION['admin'])
                $data['admin'] = true;
            if(isset($_SESSION['jugador']) && $_SESSION['jugador'])
                $data['jugador'] = true;
            if(isset($_SESSION['editor']) && $_SESSION['editor'])
                $data['editor'] = true;

            return $data;
        }else{
            $this->redirectLoginForm();
            return null;
        }
    }
}
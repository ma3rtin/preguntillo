<?php

class UsuarioModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function validate($user, $pass)
    {
        $sql = "SELECT u.id, u.usuario
                FROM usuario u
                WHERE usuario = '" . $user . "' 
                AND contraseña = '" . $pass . "'";

        $user = $this->database->query($sql);
        if (sizeof($user) == 0) {
            return false;
        }else{
            return $user[0];
        }
    }

    public function getUserData($username){
        $sql = "SELECT u.id, u.usuario, u.nombre, u.mail, u.año_nac, u.foto 
                FROM usuario u
                WHERE usuario = '" . $username . "'";
        $user = $this->database->query($sql);
        return $user[0];
    }

    public function register($user, $name, $email, $pass, $birthyear, $photo){
        if(!$this->checkUserExists($user) && !$this->checkEmailExists($email)){
            $photoType = explode('/', $photo['type'])[1];
            $photoValue = $user . "." . $photoType;
            $path = "public/users/" . $photoValue;
            move_uploaded_file($photo['tmp_name'], $path);

            $sql = "INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, activo) 
            VALUES ('" . $user . "', '" . $name . "', '" . $email . "', '" . $pass . "', '" . $birthyear . "', '" . $photoValue . "', 0)";
            $this->database->execute($sql);

            $this->createToken($user);
        }
    }

    private function checkUserExists($user)
    {
        $sql = "SELECT 1 
                FROM usuario 
                WHERE usuario = '" . $user . "'";
        $result = $this->database->query($sql);
        return sizeof($result) == 1;
    }

    private function checkEmailExists($email)
    {
        $sql = "SELECT 1 
                FROM usuario 
                WHERE mail = '" . $email . "'";
        $result = $this->database->query($sql);
        return sizeof($result) == 1;
    }

    private function createToken($user)
    {
        $user = $this->getUserData($user);

        $token = rand(0, 2000);

        $sql = "INSERT INTO token (valor, usuario_id) 
                VALUES (" . $token . ", " . $user['id'] . ")";
        $this->database->execute($sql);

        $this->sendMail($user['mail'], $user['id'], $token);
    }

    private function sendMail($email, $user, $token)
    {
//        $subject = 'Registro Exitoso';
//        $message = "Gracias por registrarte en Preguntillo. Tu usuario es: " . $user . ", valida tu cuenta haciendo click en el siguiente <a href='http://localhost/usuario/validateEmail?token='" . $token . "&user=" . $user . "'>enlace</a>. '";
//
//        $sender = new FileEmailSender();
//        $sender->validateMail($email, $subject, $message);

        $filePath = 'C:\xampp\htdocs\preguntillo\public\tokens.json';

        if (!file_exists($filePath)) {
            $data = [];
        } else {
            $data = json_decode(file_get_contents($filePath), true);
        }
        $data[$user] = $token;
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }


    public function validateToken($token, $userId)
    {
        $sql = "SELECT 1 
                FROM token 
                WHERE valor = '" . $token . "' 
                AND usuario_id = '" . $userId . "'";
        $result = $this->database->query($sql);
        if(sizeof($result) == 1){
            $sql = "UPDATE usuario 
                    SET activo = 1
                    WHERE id = '" . $userId . "'";
            $this->database->execute($sql);
            return true;
        }else
            return false;
    }

}
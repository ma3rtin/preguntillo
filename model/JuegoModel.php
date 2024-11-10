<?php

class juegoModel{

    private $database;
    public function __construct($database){
        $this->database = $database;
    }
    public function registrar($nombre_completo, $ano_nacimiento,$mail, $password,$user_name,$lat, $lng) {
        $sql = "INSERT INTO `usuario` ( `nombre_completo`, `ano_nacimiento`,`mail`,`password`,`user_name`,`imagen_path`,`lat`,`lng`) 
                VALUES ( '$nombre_completo', '$ano_nacimiento','$mail', '$password', '$user_name','$lat','$lng')";
        Logger::info('Usuario registro: ' . $sql);

        $this->database->query($sql);
    }

    public function buscarUsuario($usuario_name,$password){
        $sql="SELECT * FROM usuario WHERE usuario_name = '$usuario_name'";
        $this->database->select($sql);

    }

    public function validarUsuario($usuario_name, $password){
        $sql="SELECT * FROM usuario WHERE usuario_name = '$usuario_name'";
        $resultado = $this->database->select($sql);

        if (!$resultado || count($resultado) === 0) {
            Logger::info('NO econtro el usuario: ' . $sql);
            return false;
        }

        $user = $resultado;
        Logger::info(print_r($user,true));

        if ($password === $user[0]['password']) {
            Logger::info('La contraseña es correcta');
            $_SESSION["user"] = $user[0];
            return true;

        } else {
            $_SESSION['error']= "Credenciales Incorrectas";
            unset($_SESSION['error']);
            return false;
        }
    }
}
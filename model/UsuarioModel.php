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
        $sql = "SELECT 1 
                FROM usuario 
                WHERE usuario = '" . $user . "' 
                AND contraseña = '" . $pass . "'";

        $usuario = $this->database->query($sql);

        return sizeof($usuario) == 1;
    }

    public function register($user, $name, $email, $pass, $birthyear, $photo){

        if(!$this->checkUserExists($user) && !$this->checkEmailExists($email)){
            $sql = "INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac) 
                VALUES ('" . $user . "', '" . $name . "', '" . $email . "', '" . $pass . "', '" . $birthyear . "')";
            $this->database->execute($sql);
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

}
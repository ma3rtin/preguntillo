<?php

class UsuarioModel
{
    private $database;
    private $partidaModel;
    public function __construct($database, $partidaModel)
    {
        $this->database = $database;
        $this->partidaModel = $partidaModel;
    }

    public function validate($user, $pass)
    {
        $sql = "SELECT u.id, u.usuario, u.activo
                FROM usuario u
                WHERE usuario = '" . $user . "' 
                AND contraseña = '" . $pass . "'";

        $user = $this->database->query($sql);
        if (sizeof($user) == 0) {
            return false;
        }else{
            if ($user[0]['activo'] == 0) {
                $sqlUpdate = "UPDATE usuario 
                          SET activo = 1 
                          WHERE id = '" . $user[0]["id"] . "'";
                $this->database->execute($sqlUpdate);
            }

            return $user[0];
        }
    }

    public function getUserData($username){
        $sql = "SELECT u.id, u.usuario, u.nombre, u.mail, u.año_nac, u.foto, u.longitud, u.latitud 
                FROM usuario u
                WHERE usuario = '" . $username . "'";
        $user = $this->database->query($sql);
        return $user[0];
    }

    public function register($user, $name, $email, $pass, $birthyear, $photo, $lat, $lon){
        if(!$this->checkUserExists($user) && !$this->checkEmailExists($email)){
            $photoType = explode('/', $photo['type'])[1];
            $photoValue = $user . "." . $photoType;
            $path = "public/img/" . $photoValue;
            move_uploaded_file($photo['tmp_name'], $path);

            $sql = "INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, activo, latitud, longitud) 
            VALUES ('" . $user . "', '" . $name . "', '" . $email . "', '" . $pass . "', '" . $birthyear . "', '" . $photoValue . "', 0, '" . $lat . "', '" . $lon . "')";
            $this->database->execute($sql);
        }
        return $this->createToken($user);
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

        return [$user['mail'], $user['id'], $token];
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

    public function getCurrentSession(){
        $data['id'] = $_SESSION['id'] ?? null;

        if(isset($data['user']['id'])){
            $data['puntaje'] = $this->partidaModel->getPuntajeUser($data['id']);
        }

        $data['nivel'] = $this->partidaModel->getNivelUsuario($data['id']);

        return $data;
    }

    public function registrarPreguntaEntregada($id) {
        $sql = "SELECT * FROM usuario WHERE id = $id";
        $user = $this->database->query($sql)[0];

        $preguntasRecibidas = $user['preguntas_recibidas'] + 1;

        $this->database->execute("UPDATE usuario SET preguntas_recibidas = $preguntasRecibidas WHERE id = $id");

        if ($preguntasRecibidas < 10) {
            $this->database->execute("UPDATE usuario SET nivel = 0.5 WHERE id = $id");
        }else{
            $preguntasAcertadas = $user['preguntas_acertadas'];
            $porcentajeAciertos = $preguntasAcertadas / $preguntasRecibidas;

            $this->database->execute("UPDATE usuario SET nivel = $porcentajeAciertos WHERE id = $id");
        }
    }

    public function actualizarNivelPorRespuestaCorrecta($id) {
        $sql = "SELECT * FROM usuario WHERE id = $id";
        $user = $this->database->query($sql)[0];

        $preguntasRecibidas = $user['preguntas_recibidas'];
        $preguntasAcertadas = $user['preguntas_acertadas'] + 1;

        if ($preguntasRecibidas >= 10) {
            $porcentajeAciertos = $preguntasAcertadas / $preguntasRecibidas;
            $this->database->execute("UPDATE usuario SET nivel = $porcentajeAciertos WHERE id = $id");
        }

        $this->database->execute("UPDATE usuario SET preguntas_acertadas = $preguntasAcertadas WHERE id = $id");
    }

    public function getUsuarios()
    {
        $sql = "SELECT * FROM usuario";
        $resultado = $this->database->query($sql);

        return $resultado;
    }

}
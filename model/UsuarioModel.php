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
        $sql = "SELECT u.id, u.usuario, u.activo, u.rol
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

    public function register($user, $name, $email, $pass, $birthyear,$gender, $country, $photo, $lat, $lon){
        if(!$this->checkUserExists($user) && !$this->checkEmailExists($email)){
            $photoType = explode('/', $photo['type'])[1];
            $photoValue = $user . "." . $photoType;
            $path = "public/img/" . $photoValue;
            move_uploaded_file($photo['tmp_name'], $path);

            $sql = "INSERT INTO usuario (usuario, nombre, mail, contraseña, año_nac, foto, pais, genero, activo, latitud, longitud) 
            VALUES ('" . $user . "', '" . $name . "', '" . $email . "', '" . $pass . "', '" . $birthyear . "', '" . $photoValue . "','" . $country . "', '" . $gender ."', 0, '" . $lat . "', '" . $lon . "')";
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
        $user = $this->getUserById($id);

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
        $user = $this->getUserById($id);

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

    public function getCantJugadores(){
        $sql = "SELECT COUNT(id) AS cant_jugadores FROM usuario WHERE activo = 1 AND rol = 'USER'";
        $resultado = $this->database->query($sql)[0];
        return $resultado['cant_jugadores'];
    }

    public function getCantJugadoresNuevos($cantDias){
        $sql = "SELECT DATE(fecha_creacion) AS fecha, COUNT(id) AS cant_jugadores FROM usuario WHERE fecha_creacion > DATE_SUB(NOW(), INTERVAL " . $cantDias . " DAY) AND rol = 'USER' GROUP BY DATE(fecha_creacion) ORDER BY fecha ASC";
        return $this->database->query($sql);
    }

    public function getCantPartidas(){
        $sql = "SELECT COUNT(id) AS cant_partidas FROM partida";
        $resultado = $this->database->query($sql)[0];
        return $resultado['cant_partidas'];
    }

    public function getCantPreguntas(){
        $sql = "SELECT COUNT(id) AS cant_preguntas FROM pregunta";
        $resultado = $this->database->query($sql)[0];
        return $resultado['cant_preguntas'];
    }

    public function getEstadisticasDeUsuarios()
    {
        $sql = "SELECT u.usuario, u.nombre, COUNT(p.usuario_id) AS cant_partidas, CASE WHEN u.preguntas_recibidas = 0 THEN 0 ELSE ROUND((u.preguntas_acertadas * 100 / u.preguntas_recibidas), 0) END AS porcentaje_aciertos FROM usuario u JOIN partida p ON p.usuario_id = u.id WHERE u.activo = 1 AND u.rol = 'USER' GROUP BY u.usuario, u.nombre, u.preguntas_acertadas, u.preguntas_recibidas;";

        return $this->database->query($sql);
    }

    public function getCantUsuariosNuevos($dias)
    {
        $sql = "SELECT COUNT(id) AS cant_nuevos FROM usuario WHERE fecha_creacion > DATE_SUB(NOW(), INTERVAL " . $dias . " DAY)";

        return $this->database->query($sql)[0]['cant_nuevos'];
    }

    public function getCantPorGenero($dias = null)
    {
        if ($dias) {
            $fecha = date('Y-m-d', strtotime("-$dias days"));
            $sql = "SELECT genero, COUNT(*) AS cantidad_usuarios FROM usuario WHERE fecha_creacion >= '$fecha' GROUP BY genero ORDER BY cantidad_usuarios DESC;";
        } else {
            $sql = "SELECT genero, COUNT(*) AS cantidad_usuarios FROM usuario GROUP BY genero ORDER BY cantidad_usuarios DESC;";
        }
        $cantidades = $this->database->query($sql);
        return $cantidades;
    }

    public function getCantPorPais($dias = null)
    {
        if ($dias) {
            $fecha = date('Y-m-d', strtotime("-$dias days"));
            $sql = "SELECT pais, COUNT(*) AS cantidad_usuarios FROM usuario WHERE fecha_creacion >= '$fecha' GROUP BY pais ORDER BY cantidad_usuarios DESC;";
        } else {
            $sql = "SELECT pais, COUNT(*) AS cantidad_usuarios FROM usuario GROUP BY pais ORDER BY cantidad_usuarios DESC;";
        }
        $cantidades = $this->database->query($sql);
        return $cantidades;
    }

    public function getCantPorEdad($dias = null)
    {
        if ($dias) {
            $fecha = date('Y-m-d', strtotime("-$dias days"));
            $condicionFecha = " AND fecha_creacion >= '$fecha'";
        } else {
            $condicionFecha = '';
        }

        $sql = "SELECT COUNT(*) AS cantidad_usuarios FROM usuario WHERE año_nac >= YEAR(CURDATE()) - 18" . $condicionFecha;
        $cantidades['menores'] = $this->database->query($sql)[0]['cantidad_usuarios'];

        $sql = "SELECT COUNT(*) AS cantidad_usuarios FROM usuario WHERE año_nac < YEAR(CURDATE()) - 18 AND año_nac >= YEAR(CURDATE()) - 65" . $condicionFecha;
        $cantidades['medios'] = $this->database->query($sql)[0]['cantidad_usuarios'];

        $sql = "SELECT COUNT(*) AS cantidad_usuarios FROM usuario WHERE año_nac < YEAR(CURDATE()) - 65" . $condicionFecha;
        $cantidades['jubilados'] = $this->database->query($sql)[0]['cantidad_usuarios'];

        return $cantidades;
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM usuario WHERE id = $id";
        return $this->database->query($sql)[0];
    }

}
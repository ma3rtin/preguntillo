<?php
class Sesion{

    static $preguntas = [];

    public static function setPreguntas(array $pregunta): void
    {
        array_push(self::$preguntas, $pregunta);
    }

    public static function getPreguntas(): array
    {
        return self::$preguntas;
    }

    public static function parseUserLevel($level){
        return is_numeric($level) ? (floatval($level) <= 0.33 ? 'Facil' : (floatval($level) <= 0.66 ? 'Medio' : 'Dificil')) : 'No válido';
    }

}
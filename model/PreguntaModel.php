<?php

class PreguntaModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function run($sql)
    {
        return $this->database->query($sql);
    }

    public function all()
    {
        $sql = "SELECT 
                    p.*, 
                    GROUP_CONCAT(o.opcion SEPARATOR ';') AS opcion, 
                    GROUP_CONCAT(CASE WHEN o.opcion_correcta = 'SI' THEN o.opcion END SEPARATOR ';') AS opcion_correcta 
                FROM pregunta AS p
                LEFT JOIN opcion AS o ON p.id = o.pregunta_id
                GROUP BY p.id";

        $resultado = $this->database->select($sql);

        foreach ($resultado as &$row) {
            $row['opciones'] = explode(';', $row['opciones']);
            $row['opciones_correctas'] = explode(';', $row['opciones_correctas']);
        }

        return $resultado;
    }

    public function getCategoria($preguntaId)
    {
        $sql = "
        SELECT c.* 
        FROM categoria c
        JOIN pregunta p ON p.categoria_id = c.id
        WHERE p.id = $preguntaId";
        return $this->database->query($sql, ['preguntaId' => $preguntaId]);
    }


    public function getPreguntaById($id)
    {
        $sql = "SELECT * FROM pregunta WHERE id = " . $id;
        $preguntaObtenida = $this->database->query($sql);
        return $preguntaObtenida[0];
    }

    public function getOpcionesByPreguntaId($preguntaId)
    {
        $sql = "SELECT * FROM opcion WHERE pregunta_id = " . $preguntaId;
        return $this->database->query($sql);
    }

    public function getNivelPreguntaById($id, $forUser = false)
    {
        $sql = "SELECT AVG(p.contestada * 1.0 / p.entregada) AS promedio,
                    CASE
                        WHEN AVG(p.contestada * 1.0 / p.entregada) <= 0.33 THEN 'Dificil'
                        WHEN AVG(p.contestada * 1.0 / p.entregada) <= 0.66 THEN 'Medio'
                        ELSE 'Facil'
                    END AS nivel
                FROM pregunta p
                WHERE p.id = '$id'";

        $preguntaPorId = $this->database->query($sql);

        if ($forUser) {
            $this->database->execute("UPDATE pregunta SET entregada = entregada + 1 WHERE id = $id");
        }

        return $preguntaPorId[0]['nivel'];
    }

    public function getIdUltimaPreguntaNoRespondida($usuarioId) {
        $sql = "SELECT id 
            FROM pregunta p
            WHERE p.id IN (
                SELECT pregunta_id 
                FROM usuario_pregunta 
                WHERE usuario_id = $usuarioId AND respondida = FALSE
            ) LIMIT 1";

        $result = $this->database->query($sql);

        if (is_array($result) && !empty($result)) {
            return $result[0]['id'];
        } else {
            return $this->getPreguntaRandom($usuarioId);
        }
    }

    public function preguntaContestada($pregunta_id, $usuario_id) {
        $sql = "SELECT 1 FROM usuario_pregunta WHERE usuario_id = $usuario_id AND pregunta_id = $pregunta_id LIMIT 1";

        $result = $this->database->query($sql);

        if (!empty($result)) {
            $sqlUpdate = "
            UPDATE usuario_pregunta SET respondida = TRUE WHERE usuario_id = $usuario_id AND pregunta_id = $pregunta_id";

            $this->database->execute($sqlUpdate);
        } else {
            $sqlInsert = " INSERT INTO usuario_pregunta (usuario_id, pregunta_id, respondida) VALUES ($usuario_id, $pregunta_id, TRUE)";

            $this->database->execute($sqlInsert);
        }
    }


    public function preguntaMostrada($usuarioId, $pregunta_id) {
        $sqlCheck = "SELECT 1 FROM usuario_pregunta WHERE usuario_id = $usuarioId AND pregunta_id = $pregunta_id LIMIT 1";
        $result = $this->database->query($sqlCheck);

        if (empty($result)) {
            $sql = "INSERT INTO usuario_pregunta (usuario_id, pregunta_id, respondida) 
                VALUES ($usuarioId, $pregunta_id, FALSE)";
            $this->database->execute($sql);
        }
    }



    public function getPreguntaRandom($usuarioId)
    {
        $sqlNivelUsuario = "SELECT nivel FROM usuario WHERE id = $usuarioId";
        $nivelUsuario = $this->database->query($sqlNivelUsuario)[0]['nivel'];

        $sqlPreguntasNoContestadas = "
        SELECT p.id 
        FROM pregunta p
        WHERE NOT EXISTS (
            SELECT 1 
            FROM usuario_pregunta up
            WHERE up.pregunta_id = p.id AND up.usuario_id = $usuarioId
        )";

        $sqlNivelExacto = $sqlPreguntasNoContestadas . "
        AND p.dificultad BETWEEN ($nivelUsuario - 0.1) AND ($nivelUsuario + 0.1)
        ORDER BY RAND()
        LIMIT 1;";
        $preguntas = $this->database->query($sqlNivelExacto);

        if (!empty($preguntas)) {
            $this->actualizarDificultad($preguntas[0]['id'], false);
            return $preguntas[0]['id'];
        } else {
            $sqlNivelAlto = $sqlPreguntasNoContestadas . "
            AND p.dificultad > ($nivelUsuario)
            ORDER BY RAND()
            LIMIT 1;";
            $preguntas = $this->database->query($sqlNivelAlto);

            if (!empty($preguntas)) {
                $this->actualizarDificultad($preguntas[0]['id'], false);
                return $preguntas[0]['id'];
            } else {
                $sqlNivelBajo = $sqlPreguntasNoContestadas . "
                AND p.dificultad < $nivelUsuario
                ORDER BY RAND()
                LIMIT 1;";
                $preguntas = $this->database->query($sqlNivelBajo);

                if (!empty($preguntas)) {
                    $this->actualizarDificultad($preguntas[0]['id'], false);
                    return $preguntas[0]['id'];
                }

                $this->database->execute("DELETE FROM usuario_pregunta WHERE usuario_id = $usuarioId");

                return $this->getPreguntaRandom($usuarioId);
            }
        }
    }


    public function update($data)
    {
        try {
            $accesible = $data->accesible ?? null;
            $sql = "UPDATE pregunta SET 
                        pregunta = '$data->pregunta',
                        estado = '$data->estado',
                        accesible = '$accesible',
                        categoria_id = '$data->categoria_id'
                    WHERE id = '$data->pregunta_id'";
            return $this->database->query($sql);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function create($data)
    {
        try {
            $accesible = $data->accesible ?? null;
            $sql = "INSERT INTO pregunta (pregunta, estado, accesible, categoria_id, dificultad_id)
                    VALUES ('$data->pregunta', '$data->estado', '$accesible', '$data->categoria_id', '$data->dificultad_id')";
            return $this->database->query($sql);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function sugerirPregunta($pregunta, $opciones, $respuestaCorrecta, $categoria_id)
    {
        try {
            $sql = "INSERT INTO pregunta_sugerida (pregunta, categoria)
                    VALUES ('$pregunta', $categoria_id)";
            $this->database->execute($sql);

            $preguntaId = $this->database->lastInsertId();

            $sqlOpciones = "INSERT INTO opcion_sugerida (pregunta_id, opcion, opcion_correcta) VALUES ";
            $values = [];

            foreach ($opciones as $key => $opcion) {
                $esCorrecta = ($key === $respuestaCorrecta) ? 'SI' : 'NO';
                $values[] = "($preguntaId, '$opcion', '$esCorrecta')";
            }

            $sqlOpciones .= implode(', ', $values);

            return $this->database->execute($sqlOpciones);

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function reportar($data)
    {
        try {
            $sql = "INSERT INTO reporte_pregunta (user_id, pregunta_id, caso, mensaje)
                    VALUES ('$data->usuario_id', '$data->pregunta_id', '$data->caso', '$data->comentario')";
            return $this->database->query($sql);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function crearReporte($user, $pregunta, $caso, $mensaje)
    {
        $sql = "INSERT INTO reporte_pregunta (usuario_id, pregunta_id, caso, mensaje)
                VALUES ($user, $pregunta, '$caso', '$mensaje')";
        return $this->database->execute($sql);
    }

    public function preguntasSugeridas($sql)
    {
        $resultado = $this->database->query($sql);

        foreach ($resultado as &$row) {
            if ($row['opciones']) {
                $row['opciones'] = explode(';', $row['opciones']);
            }
        }

        return $resultado;
    }

    public function actualizarDificultad($id, $esCorrecta)
    {
        $sql = "SELECT * FROM pregunta WHERE id = $id";
        $pregunta = $this->database->query($sql)[0];

        $vecesEntregada = $esCorrecta ? $pregunta['veces_entregada'] : $pregunta['veces_entregada'] + 1;
        $vecesAcertada = $esCorrecta ? $pregunta['veces_acertada'] + 1 : $pregunta['veces_acertada'];

        if ($vecesEntregada < 10) {
            $dificultad = 0.5;
        } else {
            $dificultad = 1 - ($vecesAcertada / ($vecesEntregada));
        }

        $this->database->execute("UPDATE pregunta SET dificultad = $dificultad, veces_entregada = $vecesEntregada, veces_acertada = $vecesAcertada WHERE id = $id");
    }

    public function getPreguntasReportadas()
    {
        $sql = "SELECT DISTINCT(r.pregunta_id) AS id, p.pregunta AS pregunta, COUNT(r.pregunta_id) AS cant_reportes FROM reporte_pregunta r JOIN pregunta p ON p.id = r.pregunta_id WHERE p.estado = 'ACTIVA' GROUP BY r.pregunta_id, p.pregunta ORDER BY cant_reportes DESC";

        return $this->database->query($sql);
    }

    public function getPreguntasSugeridas()
    {
        $sql = "SELECT ps.id AS id, ps.pregunta AS pregunta, c.nombre AS categoria, GROUP_CONCAT(os.opcion ORDER BY os.opcion ASC) AS opciones FROM pregunta_sugerida ps JOIN opcion_sugerida os ON os.pregunta_id = ps.id JOIN categoria c ON c.id = ps.categoria GROUP BY ps.id, ps.pregunta, c.nombre;";
        return $this->database->query($sql);
    }

    public function aceptarPregunta($id)
    {
        $sqlObtenerPregunta = "SELECT pregunta, categoria 
                           FROM pregunta_sugerida 
                           WHERE id = $id";
        $preguntaAceptada = $this->database->query($sqlObtenerPregunta)[0];
        $pregunta = $preguntaAceptada['pregunta'];
        $categoria = $preguntaAceptada['categoria'];

        $sqlInsertPregunta = "INSERT INTO pregunta (pregunta, categoria_id, estado) 
                              VALUES ('$pregunta ', $categoria, 'ACTIVA')";
        $this->database->execute($sqlInsertPregunta);

        $ultimoId = $this->database->lastInsertId();

        $sqlObtenerOpciones = "SELECT opcion, opcion_correcta 
                           FROM opcion_sugerida 
                           WHERE pregunta_id = $id";
        $opciones = $this->database->query($sqlObtenerOpciones);

        foreach ($opciones as $opcion) {
            $sqlInsertOpcion = "INSERT INTO opcion (pregunta_id, opcion, opcion_correcta) 
                            VALUES ($ultimoId, '{$opcion['opcion']}', '{$opcion['opcion_correcta']}')";
            $this->database->execute($sqlInsertOpcion);
        }

        $sqlEliminarOpcionesSugeridas = "DELETE FROM opcion_sugerida WHERE pregunta_id = $id";
        $this->database->execute($sqlEliminarOpcionesSugeridas);

        $sqlEliminarPreguntaSugerida = "DELETE FROM pregunta_sugerida WHERE id = $id";
        return $this->database->execute($sqlEliminarPreguntaSugerida);
    }

    public function rechazarPregunta($id)
    {
        $sql = "DELETE FROM opcion_sugerida WHERE pregunta_id = $id";
        $this->database->execute($sql);
        $sql = "DELETE FROM pregunta_sugerida WHERE id = $id";
        return $this->database->execute($sql);
    }

    public function crearPregunta($pregunta, $opciones, $respuestaCorrecta, $categoria_id)
    {
        $sqlPregunta = "INSERT INTO pregunta (pregunta, estado, categoria_id) VALUES ('$pregunta', 'ACTIVA', $categoria_id)";
        $this->database->execute($sqlPregunta);

        $preguntaId = $this->obtenerIdDePregunta($pregunta);

        $sqlOpciones = "INSERT INTO opcion (pregunta_id, opcion, opcion_correcta) VALUES ";
        $values = [];

        foreach ($opciones as $key => $opcion) {
            $esCorrecta = ($key === $respuestaCorrecta) ? 'SI' : 'NO';
            $values[] = "($preguntaId, '$opcion', '$esCorrecta')";
        }

        $sqlOpciones .= implode(', ', $values);

        return $this->database->execute($sqlOpciones);
    }


    public function obtenerIdDePregunta($pregunta)
    {
        $sql = "SELECT id FROM pregunta WHERE pregunta = '$pregunta'";
        return $this->database->query($sql)[0]['id'];
    }

    public function deshabilitarPregunta($id)
    {
        $sql = "UPDATE pregunta SET estado = 'INACTIVA' WHERE id = $id";
        return $this->database->execute($sql);
    }

    public function habilitarPregunta($id)
    {
        $sql = "UPDATE pregunta SET estado = 'ACTIVA' WHERE id = $id";
        return $this->database->execute($sql);
    }

    public function getAllCategorias()
    {
        $sql = "SELECT id, nombre FROM categoria";
        return $this->database->query($sql);
    }

    public function getPreguntas()
    {
        $sql = "SELECT p.id AS id, p.pregunta AS pregunta, p.estado AS estado, c.nombre AS categoria, GROUP_CONCAT(o.opcion ORDER BY o.opcion ASC) AS opciones FROM pregunta p JOIN opcion o ON o.pregunta_id = p.id JOIN categoria c ON c.id = p.categoria_id GROUP BY p.id, p.pregunta, c.nombre;";
        return $this->database->query($sql);
    }

    public function editarPregunta($preguntaId, $texto, $categoria_id)
    {
        $sql = "UPDATE pregunta SET pregunta = '$texto', categoria_id = $categoria_id WHERE id = $preguntaId";
        $this->database->execute($sql);
    }

    public function editarOpcion($opcionId, $texto)
    {
        $sql = "UPDATE opcion SET opcion = '$texto' WHERE id = '$opcionId'";
        $this->database->execute($sql);
    }
}
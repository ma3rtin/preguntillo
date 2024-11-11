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

    public function getModulos()
    {
        $sql = "SELECT DISTINCT id_modulo FROM pregunta";
        $resultado = $this->database->query($sql);

        return $resultado;
    }

    public function getAllBy($moduleName)
    {
        $sql = "SELECT p.pregunta, GROUP_CONCAT(o.opcion SEPARATOR ';') AS opcion, MAX(CASE WHEN o.opcion_correcta = 'SI' THEN o.opcion END) AS opcion_correcta 
                FROM pregunta AS p
                LEFT JOIN opcion AS o ON p.id = o.pregunta_id
                WHERE p.modulo = '$moduleName'
                GROUP BY p.id";

        $resultado = $this->database->query($sql);

        foreach ($resultado as &$row) {
            $row['opciones'] = explode(';', $row['opciones']);
        }

        return $resultado;
    }

    public function getPreguntaById($id)
    {
        $sql = "SELECT * FROM pregunta WHERE id = ".$id;
        $preguntaObtenida = $this->database->query($sql);
        return $preguntaObtenida[0];
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

    public function getPreguntaRandom($usuarioId) {
        $sql = "SELECT p.id 
            FROM pregunta p
            WHERE NOT EXISTS (
                SELECT 1 
                FROM usuario_pregunta up
                WHERE up.pregunta_id = p.id AND up.usuario_id = $usuarioId
            )
            ORDER BY RAND() 
            LIMIT 1;";

        $preguntas = $this->database->query($sql);

        if (!empty($preguntas)) {
            return $preguntas[0]['id'];
        }
        else{
           $sql = "DELETE FROM usuario_pregunta WHERE usuario_id = $usuarioId";
           $this->database->execute($sql);
           $this->getPreguntaRandom($usuarioId);
        }

        return null;
    }

    public function update($data)
    {
        try {
            $accesible = $data->accesible ?? null;
            $sql = "UPDATE pregunta SET 
                        pregunta = '$data->pregunta',
                        estado = '$data->estado',
                        accesible = '$accesible',
                        id_modulo = '$data->id_modulo',
                        id_tipo = '$data->id_tipo'
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
            $sql = "INSERT INTO pregunta (pregunta, estado, accesible, id_modulo, id_tipo, dificultad_id)
                    VALUES ('$data->pregunta', '$data->estado', '$accesible', '$data->id_modulo', '$data->id_tipo', '$data->dificultad_id')";
            return $this->database->query($sql);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function sugerir($data)
    {
        try {
            $sql = "INSERT INTO pregunta_sugerida (pregunta, modulo, id_tipo)
                    VALUES ('$data->pregunta', '$data->modulo', '$data->id_tipo')";
            return $this->database->query($sql);
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
}
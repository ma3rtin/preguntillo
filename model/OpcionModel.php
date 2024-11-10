<?php

class OpcionModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getOpciones($idPregunta)
    {
        $sql = "SELECT * FROM opcion WHERE pregunta_id = ".$idPregunta;
        return $this->database->select($sql);
    }

    public function deleteOpciones($idPregunta)
    {
        $sql = "DELETE FROM opcion WHERE pregunta_id = '$idPregunta'";
        return $this->database->query($sql);
    }

    public function updateOpciones($data, $isSugerir = false)
    {
        $jsonData = is_string($data) ? json_decode($data) : $data;

        $dataToUpdate = new stdClass();
        $dataToUpdate->opcion = $jsonData->respuestas;
        $dataToUpdate->respuesta_correcta = $jsonData->repuesta_correcta;
        $dataToUpdate->id_pregunta = $jsonData->pregunta_id;

        $result = $this->getOpciones($dataToUpdate->id_pregunta);
        $sqlStatus = true;
        $table = $isSugerir ? "opciones_sugeridas" : "opciones";

        foreach ($result as $key => $row) {
            $opcionCorrecta = $key === (intval($dataToUpdate->respuesta_correcta) - 1) ? 'SI' : 'NO';
            $opcionTexto = $dataToUpdate->opciones[$key];
            $sql = "UPDATE ".$table." SET opcion = '$opcionTexto', opcion_correcta = '$opcionCorrecta' WHERE id = " . $row['id'];
            $sqlStatus = $this->database->query($sql) && $sqlStatus;
        }

        $_SESSION[$sqlStatus ? 'success' : 'error'] = $sqlStatus ? 'Opciones actualizadas!' : 'Opciones no actualizadas!';
        return $sqlStatus;
    }

    public function createOpciones($data, $isSugerir = false)
    {
        $dataToCreate = new stdClass();
        $dataToCreate->opcion = $data->respuestas;
        $dataToCreate->respuesta_correcta = $data->repuesta_correcta;
        $dataToCreate->id_pregunta = $data->pregunta_id;

        $sqlStatus = true;
        $table = $isSugerir ? 'opciones_sugeridas' : 'opciones';

        foreach ($dataToCreate->opcion as $key => $row) {
            $opcionCorrecta = $key === (intval($dataToCreate->respuesta_correcta) - 1) ? 'SI' : 'NO';
            $opcionTexto = $dataToCreate->opciones[$key];
            $sql = "INSERT INTO " . $table . " (opcion, opcion_correcta, pregunta_id) VALUES ('$opcionTexto', '$opcionCorrecta', '$dataToCreate->id_pregunta')";
            $sqlStatus = $this->database->query($sql) && $sqlStatus;
        }

        $_SESSION[$sqlStatus ? 'success' : 'error'] = $sqlStatus ? 'Pregunta y Opciones creadas!' : 'Pregunta y Opciones no creadas!';
        return $sqlStatus;
    }
}
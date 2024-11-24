<?php

class EditorController
{
    private $preguntaModel;
    private $presenter;
    private $usuarioModel;

    public function __construct($preguntaModel, $usuarioModel, $presenter)
    {
        $this->preguntaModel = $preguntaModel;
        $this->usuarioModel = $usuarioModel;
        $this->presenter = $presenter;
    }

    public function verPreguntasReportadas()
    {
        if($this->verificarRol()){
            $data['preguntasReportadas'] = $this->preguntaModel->getPreguntasReportadas();
            $data['reportes'] = true;
            $data['css'] = '/public/css/listaPreguntas.css';
            $data['js'] = '/public/js/preguntas.js';
           $this->presenter->show('listaPreguntas', $data);
        }
    }

    public function deshabilitar()
    {
        if($this->verificarRol()){
            $preguntaId = $_GET['pregunta'] ?? null;
            if($preguntaId) $this->preguntaModel->deshabilitarPregunta($preguntaId);
        }
    }

    public function verPreguntasSugeridas(){
        if($this->verificarRol()){

            $data['preguntasSugeridas']  = $this->preguntaModel->getPreguntasSugeridas();
            $data['sugerencias'] = true;
            foreach ($data['preguntasSugeridas'] as &$pregunta) {
                $pregunta['opciones'] = explode(',', $pregunta['opciones']);
            }

            $data['css'] = '/public/css/listaPreguntas.css';
            $data['js'] = '/public/js/preguntas.js';
            $this->presenter->show('listaPreguntas', $data);
        }
    }

    public function aceptarPregunta(){
        if($this->verificarRol()){
            $preguntaId = $_GET['pregunta'] ?? null;
            if($preguntaId) $this->preguntaModel->aceptarPregunta($preguntaId);
        }
    }

    public function rechazarPregunta(){
        if($this->verificarRol()){
            $preguntaId = $_GET['pregunta'] ?? null;
            if($preguntaId) $this->preguntaModel->rechazarPregunta($preguntaId);
        }
    }

    public function crearPreguntas(){
        if($this->verificarRol()){
            $this->presenter->show('crearPreguntas');
        }
    }

    public function crearPregunta(){
        if($this->verificarRol()){
            $pregunta = $_POST['pregunta'] ?? null;
            $opcion1 = $_POST['opcion1'] ?? null;
            $opcion2 = $_POST['opcion2'] ?? null;
            $opcion3 = $_POST['opcion3'] ?? null;
            $modulo = $_POST['modulo'] ?? null;
            $tipo = $_POST['tipo'] ?? null;
            if($pregunta && $opcion1 && $opcion2 && $opcion3 && $modulo && $tipo){
                $this->preguntaModel->crearPregunta($pregunta, $opcion1, $opcion2, $opcion3, $modulo, $tipo);
                $data['exito'] = "Se ha creado la pregunta";
            }else{
                $data['error'] = "Todos los campos son obligatorios";
            }
            $this->presenter->show('crearPreguntas', $data);
        }
    }

    public function editarPreguntaForm(){
        if($this->verificarRol()){
            $preguntaId = $_POST['pregunta'] ?? null;

            if($preguntaId){
                $pregunta = $this->preguntaModel->getPreguntaById($preguntaId);
                $data['pregunta'] = $pregunta;

                //$data['css'] = '/public/css/listaPreguntas.css';
                $this->presenter->show('crearPreguntas', $data);
            }else{
                $data['error'] = "Se requiere el id de la pregunta";
                $this->presenter->show('crearPreguntas', $data);
            }
        }
    }

    public function editarPregunta()
    {
        if($this->verificarRol()){
            $pregunta = $_POST['pregunta'] ?? null;
            $opcion1 = $_POST['opcion1'] ?? null;
            $opcion2 = $_POST['opcion2'] ?? null;
            $opcion3 = $_POST['opcion3'] ?? null;
            $modulo = $_POST['modulo'] ?? null;
            $tipo = $_POST['tipo'] ?? null;
            if($pregunta && $opcion1 && $opcion2 && $opcion3 && $modulo && $tipo){
                $this->model->editarPregunta($pregunta, $opcion1, $opcion2, $opcion3, $modulo, $tipo);
                $data['exito'] = "Se ha editado la pregunta";
            }else{
                $data['error'] = "Todos los campos son obligatorios";
            }
            $this->presenter->show('crearPreguntas', $data);
        }

    }

    public function verificarRol()
    {
        if(isset($_SESSION['id'])){
            $user = $this->usuarioModel->getUserById($_SESSION['id']);
            if($user['rol'] == 'EDITOR'){
                return true;
            }
        }else{
            $this->redirectLoginForm();
        }
    }

    public function redirectLoginForm()
    {
        header('location: /loginForm');
        exit();
    }
}
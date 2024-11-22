<?php

class EditorController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function verPreguntasReportadas()
    {
        if($this->verificarRol()){
           $preguntas = $this->model->getPreguntasReportadas();
           $data['preguntas'] = $preguntas;

           //$data['css'] = '/public/css/preguntas.css';
           $this->presenter->show('listaPreguntas', $data);
        }
    }

    public function verPreguntasSugeridas(){
        if($this->verificarRol()){
            $preguntas = $this->model->getPreguntasSugeridas();
            $data['preguntas'] = $preguntas;

            //$data['css'] = '/public/css/preguntas.css';
            $this->presenter->show('listaPreguntas', $data);
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
                $this->model->crearPregunta($pregunta, $opcion1, $opcion2, $opcion3, $modulo, $tipo);
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
                $pregunta = $this->model->getPreguntaById($preguntaId);
                $data['pregunta'] = $pregunta;

                //$data['css'] = '/public/css/preguntas.css';
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
            $user = $this->model->getUserById($_SESSION['id']);
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
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
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
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

    public function habilitar()
    {
        if($this->verificarRol()){
            $preguntaId = $_GET['pregunta'] ?? null;
            if($preguntaId) $this->preguntaModel->habilitarPregunta($preguntaId);
        }
    }

    public function verPreguntasSugeridas(){
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
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
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        if($this->verificarRol()){
            $data['categorias'] = $this->preguntaModel->getAllCategorias();
            $data['js'] = '/public/js/crearPreguntas.js';
            $data['css'] = '/public/css/crearPreguntas.css';
            $this->presenter->show('crearPreguntas', $data);
        }
    }

    public function agregarPregunta()
    {
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);

        if ($this->verificarRol()) {
            $pregunta = $_POST['pregunta'] ?? null;
            $opciones = $_POST['opciones'] ?? [];
            $respuestaCorrecta = $_POST['respuesta_correcta'] ?? null;
            $categoria = $_POST['categoria_id'] ?? null;

            if ($pregunta && $opciones && $respuestaCorrecta && count($opciones) === 4 && $categoria) {
                $opcionCorrecta = $opciones[$respuestaCorrecta] ?? null;

                if ($opcionCorrecta) {
                    $this->preguntaModel->crearPregunta($pregunta, $opciones, $respuestaCorrecta, $categoria);
                } else {
                    $data['error'] = "La opción correcta no es válida.";
                    $this->presenter->show('crearPreguntas', $data);
                }
            } else {
                $data['error'] = "Todos los campos son obligatorios.";
                $this->presenter->show('crearPreguntas', $data);
            }
        }
    }


    public function verPreguntasActivas(){
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        if($this->verificarRol()){
            $data['preguntasEditables'] = $this->preguntaModel->getPreguntas();
            $data['editar'] = true;

            foreach ($data['preguntasEditables'] as &$pregunta) {
                $pregunta['opciones'] = explode(',', $pregunta['opciones']);
                $pregunta['estado'] = $pregunta['estado'] == "ACTIVA";
            }

            $data['js'] = '/public/js/preguntas.js';
            $data['css'] = '/public/css/listaPreguntas.css';
            $this->presenter->show('listaPreguntas', $data);
        }
    }

    public function editarPreguntaForm() {
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
        if ($this->verificarRol()) {
            $preguntaId = $_GET['id'] ?? null;

            if ($preguntaId) {
                $pregunta = $this->preguntaModel->getPreguntaById($preguntaId);
                $opciones = $this->preguntaModel->getOpcionesByPreguntaId($preguntaId);

                foreach($opciones as &$opcion) {
                    $opcion['opcion_correcta'] = $opcion['opcion_correcta'] == "SI";
                }

                $categorias = $this->preguntaModel->getAllCategorias();
                foreach ($categorias as &$categoria) {
                    $categoria['categoria_elegida'] = $categoria['id'] == $pregunta['categoria_id'];
                }

                $data = [
                    'pregunta' => $pregunta,
                    'categorias' => $categorias,
                    'opciones' => $opciones,
                    'js' => '/public/js/crearPreguntas.js',
                    'css' => '/public/css/crearPreguntas.css'
                ];

                $this->presenter->show('crearPreguntas', $data);
            } else {
                $this->verPreguntasActivas();
            }
        }
    }

    public function editarPregunta()
    {
        if ($this->verificarRol()) {

            $preguntaId = $_POST['id'] ?? null;
            $preguntaTexto = $_POST['pregunta'] ?? null;
            $categoria = $_POST['categoria_id'] ?? null;
            $opciones = $_POST['opciones'] ?? [];

            //echo json_encode($_POST);

            if ($preguntaId && $preguntaTexto && $categoria && !empty($opciones)) {
                $this->preguntaModel->editarPregunta($preguntaId, $preguntaTexto, $categoria);

                foreach ($opciones as $id => $texto) {
                    if (!empty($texto)) {
                        $this->preguntaModel->editarOpcion($id, $texto);
                    }
                }
            }
        }
    }

    public function verificarRol()
    {
        $data['user'] = $this->usuarioModel->getUserData($_SESSION['username']);
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
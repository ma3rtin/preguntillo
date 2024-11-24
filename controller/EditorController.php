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

    public function habilitar()
    {
        if($this->verificarRol()){
            $preguntaId = $_GET['pregunta'] ?? null;
            if($preguntaId) $this->preguntaModel->habilitarPregunta($preguntaId);
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
            $data['tipos'] = $this->preguntaModel->getTipos();
            $data['modulos'] = $this->preguntaModel->getAllModulos();
            $data['js'] = '/public/js/crearPreguntas.js';
            $data['css'] = '/public/css/crearPreguntas.css';
            $this->presenter->show('crearPreguntas', $data);
        }
    }

    public function agregarPregunta(){
        if($this->verificarRol()){
            $pregunta = $_POST['pregunta'] ?? null;
            $opcion1 = $_POST['opcion1'] ?? null;
            $opcion2 = $_POST['opcion2'] ?? null;
            $opcion3 = $_POST['opcion3'] ?? null;
            $opcion4 = $_POST['opcion4'] ?? null;
            $modulo = $_POST['id_modulo'] ?? null;
            $tipo = $_POST['id_tipo'] ?? null;

            if($pregunta && $opcion1 && $opcion2 && $opcion3 && $opcion4 && $modulo && $tipo) {
                $this->preguntaModel->crearPregunta($pregunta, $opcion1, $opcion2, $opcion3, $opcion4, $modulo, $tipo);
            }else{
                $data['error'] = "Todos los campos son obligatorios";
                $this->presenter->show('crearPreguntas', $data);
            }
        }
    }

    public function verPreguntasActivas(){
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
        if ($this->verificarRol()) {
            $preguntaId = $_GET['id'] ?? null;

            if ($preguntaId) {
                $pregunta = $this->preguntaModel->getPreguntaById($preguntaId);
                $opciones = $this->preguntaModel->getOpcionesByPreguntaId($preguntaId);

                foreach($opciones as &$opcion) {
                    $opcion['opcion_correcta'] = $opcion['opcion_correcta'] == "SI";
                }

                $tipos = $this->preguntaModel->getTipos();
                foreach ($tipos as &$tipo) {
                    $tipo['tipo_elegido'] = $tipo['id'] == $pregunta['id_tipo'];
                }

                $modulos = $this->preguntaModel->getAllModulos();
                foreach ($modulos as &$modulo) {
                    $modulo['modulo_elegido'] = $modulo['id'] == $pregunta['id_modulo'];
                }

                $data = [
                    'pregunta' => $pregunta,
                    'tipos' => $tipos,
                    'modulos' => $modulos,
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
            $modulo = $_POST['id_modulo'] ?? null;
            $tipo = $_POST['id_tipo'] ?? null;
            $opciones = $_POST['opciones'] ?? [];

            //echo json_encode($_POST);

            if ($preguntaId && $preguntaTexto && $modulo && $tipo && !empty($opciones)) {
                $this->preguntaModel->editarPregunta($preguntaId, $preguntaTexto, $modulo, $tipo);

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
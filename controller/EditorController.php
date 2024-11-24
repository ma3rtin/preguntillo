<?php
//-EL ADMIN Y EL EDITOR NO PUEDE JUGAR PARTIDAS.
//-EL JUGADOR REPORTA LA PREGUNTA DESPUES DE RESPONDERLA. NO CAMBIA EL ESTADO DE LA PREGUNTA.
//-LOS DATOS NO SE BORRAN, SON BORRADOS LOGICOS
//   +CASO PREGUNTA, TIENE UN CAMPO ACTIVO, SOLO SE ENTREGAN LAS ACTIVAS
//   +DEFINIMOS CAMPO ESTADO: PENDIENTE, APROBADA, REPORTADA, RECHAZADA.
//
//-EL PROFE QUIERE UN GRÁFICO DE LA CANTIDAD DE PREGUNTAS RECHAZADAS
//   +ESTADO: ID: 1 - DESC: APROBADA
//            ID: 2 - DESC: RECHAZADAS
//            ID: 3 - DESC: REPORTADA
//			  ID: 4 - DESC: PENDIENTE
//			  ID: 5 - DESC: BORRADO/DESACTIVADO
class EditorController{

    private $opcionModel;
    private $preguntaModel;
    private $presenter;

    public function __construct($opcionModel,$preguntaModel ,$presenter){
        $this->opcionModel = $opcionModel;
        $this->preguntaModel = $preguntaModel;
        $this->presenter = $presenter;
    }

    public function showReports(){
        if($_SESSION['editor']){
          $data['pregunta_reporte'] = $this->preguntaModel->obtenerPreguntasReportadasNoResueltas();
          $data['css'] = '/public/css/reporte.css';
          $this->presenter->show("reportes",$data);
        }else{
            Redirect::to('login');
        }
    }

    public function editarPregunta() {
        if ($_SESSION['editor']) {
            $preguntaId = $_POST['id'] ?? null;

            if ($preguntaId) {
                $pregunta = $this->preguntaModel->getPreguntaById($preguntaId);
                $opciones = $this->opcionModel->getOpciones($preguntaId);

                $data['preguntas'] = $pregunta;
                $data['opciones'] = $opciones;
                $data['css'] = '/public/css/reporte.css';

                $this->presenter->show("editar", $data);
            }

        } else {
            Redirect::to('login');
        }
    }

    public function guardarCambios() {

    }


}
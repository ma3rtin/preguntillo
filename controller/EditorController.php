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
    private $preguntaModel;
    private $presenter;

    public function __construct($preguntaModel ,$presenter){
        $this->preguntaModel = $preguntaModel;
        $this->presenter = $presenter;
    }
    public function showReports(){
        if($_SESSION['editor']){
          $data['pregunta_reporte'] = $this->preguntaModel->obtenerPreguntasReportadasNoResueltas();
          $this->presenter->show("reportes",$data);
        }else{
            Redirect::to('login');
        }
    }

}
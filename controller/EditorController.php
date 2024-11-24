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

    private $usuarioModel;
    private $preguntaModel;
    private $presenter;

    public function __construct($usuarioModel,$preguntaModel ,$presenter){
        $this->usuarioModel = $usuarioModel;
        $this->preguntaModel = $preguntaModel;
        $this->presenter = $presenter;
    }
    public function showReports(){
        if($_SESSION['editor']){
          $usuarios = $this->usuarioModel->getUsuarios();
          $reportes = $this->preguntaModel->obtenerPreguntasReportadasNoResueltas($usuarios['id']);
          $preguntas = $this->preguntaModel->all();

          $data['usuario'] = $usuarios['usuario'];
          $data['pregunta'] = $preguntas['pregunta'];
          $data['caso'] = $reportes['caso'];
          $data['mensaje'] = $reportes['mensaje'];

          $data['css'] = '/public/css/reportes.css';

          $this->presenter->show('reportes',$data);
        }else{
            Redirect::to('login');
        }
    }

}
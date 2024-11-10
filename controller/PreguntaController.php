<?php
class PreguntaController{

    private $usuarioModel;
    private $preguntaModel;
    private $partidaModel;
    private $presenter;

    public function __construct($usuarioModel, $preguntaModel, $partidaModel, $presenter) {
        $this->usuarioModel = $usuarioModel;
        $this->preguntaModel = $preguntaModel;
        $this->partidaModel = $partidaModel;
        $this->presenter = $presenter;
    }

    public function list() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function modulo() {
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['preguntasByModule'] = $this->preguntaModel->getAllBy($_GET['name']);

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function show(){
        $data['nivel'] = $_SESSION['nivel'] ?? 0;
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['id'] = $_SESSION['id'];
        $data['puntaje'] = $this->partidaModel->getPartidaPuntaje($data['id']);

        $idPregunta = $_GET['params'] ?? $this->preguntaModel->getRandomId();
        $_SESSION['tiempo_inicio'] = time();
        $data['pregunta'] = $this->preguntaModel->getPreguntaByNivel($data['nivel'] ,true);
        $data['dificultad'] = $this->preguntaModel->getNivelPreguntaById($idPregunta ,true);

        Sesion::setPreguntas($data['pregunta']);

        $this->presenter->authView('pregunta',$data);
    }

    public function validarOpcion(){
        $data['userSession'] = $this->usuarioModel->getCurrentSession();
        $data['pregunta'] = $this->preguntaModel->getPregunta($_POST['id']);

        $idPreguntaActual = $_POST['id'];



        $data['pregunta'] = $this->preguntaModel->getPregunta($idPreguntaActual, true);

        $opcionSeleccionada = $_POST['opcion'];

        $opcionCorrecta = $data['pregunta']['opcion_correcta'];

        $tiempoInicio = $_SESSION['tiempo_inicio'];

        $tiempoTranscurrido = time() - $tiempoInicio;

        $duracionMaxima = 30;
        //logger::info(print_r([ 'opcioncorrecta' => $opcionCorrecta, 'opcionseleccionada' => $opcionSeleccionada,'data' => $data ],true));
        if($tiempoTranscurrido > $duracionMaxima){
            $data['opcionEsCorrecta']= "fin ";
            $_SESSION['error'] = 'Expiro el tiempo';

            $this->partidaModel->actualizarPartida($data['userSession']['user']['id'],$_POST['puntaje']);

            Redirect::to('/juego/perdido');
        }
        if ($opcionSeleccionada == $opcionCorrecta){

            $data['opcionEsCorrecta']= "La es opcion correcta ";
            $this->partidaModel->preguntaContestada($_POST['id']);
            $data['puntaje'] =  intval($_POST['puntaje']) + 1;

            $this->partidaModel->actualizarPartida($data['userSession']['user']['id'],  $data['puntaje']);
            if ( $data['puntaje'] >= 10) {
                Redirect::to('/juego/ganado');
            }

            $preguntas = Sesion::getPreguntas();
            $preguntasIds = array_column($preguntas, 'id');
            $siguientePreguntaId = $this->preguntaModel->getRandomIdNotInArray($preguntasIds);

            Redirect::to("/pregunta/show/$siguientePreguntaId");

        }else{
            $data['opcionEsCorrecta']= "fin ";
            $this->partidaModel->actualizarPartida($data['userSession']['user']['id'],$_POST['puntaje']);

            Redirect::to('/juego/perdido');
        }

        $this->presenter->authView($data['userSession'],'pregunta',$data);
    }

    public function sugerir(){
        $data['modulos'] = $this->preguntaModel->getAllModules();
        $data['tipos'] = $this->preguntaModel->getAllTypes();

        $this->presenter->authView('sugerirPregunta', $data);
    }
}
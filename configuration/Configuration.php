<?php

include_once("helper/MysqlObjectDatabase.php");
include_once("helper/FileEmailSender.php");
include_once("helper/Router.php");
include_once("helper/MustachePresenter.php");

include_once("controller/UsuarioController.php");
include_once("controller/PartidaController.php");
include_once("controller/JuegoController.php");
include_once("controller/PreguntaController.php");
include_once("controller/AdminController.php");
include_once("controller/EditorController.php");

include_once("model/PartidaModel.php");
include_once("model/UsuarioModel.php");
include_once("model/JuegoModel.php");
include_once("model/PreguntaModel.php");
include_once("model/OpcionModel.php");

include_once('vendor/PHPMailer/src/PHPMailer.php');
include_once('vendor/PHPMailer/src/SMTP.php');
include_once('vendor/PHPMailer/src/Exception.php');

include_once('vendor/PHPMailer/src/PHPMailer.php');
include_once('vendor/mustache/src/Mustache/Autoloader.php');
include_once('vendor/phpqrcode/qrlib.php');
include_once('helper/QRMaker.php');
require_once 'helper/Redirect.php';
require_once 'helper/Logger.php';
require_once 'helper/Sesion.php';

class Configuration
{
    public function __construct(){}

    public function getUsuarioController()
    {
        return new UsuarioController($this->getUsuarioModel(), $this->getQRMaker(), $this->getEmailSender(), $this->getPartidaModel(), $this->getPresenter());
    }

    private function getQRMaker()
    {
        return new QRMaker();
    }
    public function getPartidaController(){
        return new PartidaController($this->getUsuarioModel(),$this->getPartidaModel(), $this->getPresenter());
    }

    public function getAdminController(){
        return new AdminController($this->getUsuarioModel(), $this->getPresenter());
    }

    public function getEditorController(){
        return new EditorController($this->getPreguntaModel(), $this->getUsuarioModel(), $this->getPresenter());
    }

    public function getJuegoController(){
        return new JuegoController($this->getUsuarioModel(),$this->getPreguntaModel(),$this->getPartidaModel(), $this->getPresenter());
    }

    public function getPreguntaController(){
        return new PreguntaController($this->getUsuarioModel(),$this->getPreguntaModel(),$this->getPartidaModel(), $this->getOpcionModel(), $this->getPresenter());
    }

    private function getEmailSender()
    {
        return new FileEmailSender();
    }

    private function getPresenter()
    {
        return new MustachePresenter("./view");
    }

    private function getDatabase()
    {
        $config = parse_ini_file('configuration/config.ini');
        return new MysqlObjectDatabase(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config["database"]
        );
    }

    public function getRouter()
    {
        return new Router($this, "getUsuarioController", "loginForm");
    }

    private function getUsuarioModel()
    {
        return new UsuarioModel($this->getDatabase(), $this->getPartidaModel());
    }

    private function getPartidaModel()
    {
            return new PartidaModel($this->getDatabase());
    }

    private function getPreguntaModel()
    {
        return new PreguntaModel($this->getDatabase());
    }

    private function getOpcionModel()
    {
        return new OpcionModel($this->getDatabase());
    }
}
<?php

include_once("helper/MysqlObjectDatabase.php");
include_once("helper/FileEmailSender.php");
include_once("helper/Router.php");
include_once("helper/MustachePresenter.php");
include_once("controller/UsuarioController.php");
include_once("model/UsuarioModel.php");

include_once('vendor/PHPMailer/src/PHPMailer.php');
include_once('vendor/PHPMailer/src/SMTP.php');
include_once('vendor/PHPMailer/src/Exception.php');

include_once('vendor/PHPMailer/src/PHPMailer.php');
include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{
    public function __construct()
    {
    }

    public function getUsuarioController()
    {
        return new UsuarioController($this->getUsuarioModel(),$this->getEmailSender(), $this->getPresenter());
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
        return new UsuarioModel($this->getDatabase());
    }
}
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class FileEmailSender
{
    private $mail;
    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port = 587;
        $this->setCredentials();
    }


    public function sendValidationMail($email, $id, $token)
    {
        try {
            $this->mail->addAddress($email); // Destinatario

            $this->mail->Subject = "Registro exitoso";
            $this->mail->Body = "<p>Valida tu email <a href='http://localhost/usuario/validateEmail?id=" . $id . "&token=" . $token . "'>acá</a></p>";
            $this->mail->isHTML(true);

            $this->mail->send();
        }
        catch (Exception $e){
            error_log('Se produjo un error, vuelva a intentarlo'.$e->getMessage());
        }
    }

    public function setCredentials()
    {
        $this->mail->setFrom('', '');
        $this->mail->Username = "";
        $this->mail->Password = "";
    }
}
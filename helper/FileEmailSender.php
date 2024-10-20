<?php

class FileEmailSender
{
    public function validateMail($to, $subject, $message)
    {
        $message = wordwrap($message, 70);
        $headers = "From: email@ejemplo.com\r\n";
        mail($to, $subject, $message, $headers);
    }
}
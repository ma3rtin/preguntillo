<?php

namespace controller;

class UsuarioController
{

    private $model;
    private $presenter;

    public function  __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function loginForm()
    {
        $this->presenter->show('login', []);
    }
}
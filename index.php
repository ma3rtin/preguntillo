<?php
session_start();
include_once("configuration/Configuration.php");
$configuration = new Configuration();
$router = $configuration->getRouter();

$action = isset($_GET['action']) ? $_GET['action'] : "loginForm";
$page = isset($_GET['page']) ? $_GET['page'] : "";

$router->route($page, $action);
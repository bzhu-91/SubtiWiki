<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

require_once "includes/Application.php";

Application::start();
Application::stop();
?>
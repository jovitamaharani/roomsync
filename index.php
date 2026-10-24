<?php
session_start();
require_once "Config/Database.php";
require_once "Config/Router.php";

$router = new Router();
$router->run();

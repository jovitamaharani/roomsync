<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once "Config/Database.php";
require_once "Config/Router.php";

$router = new Router();
$router->run();

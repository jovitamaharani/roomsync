<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
//bypass login
define('DEV_BYPASS_LOGIN', true);

date_default_timezone_set('Asia/Jakarta');

if (DEV_BYPASS_LOGIN && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    if (empty($_SESSION['user'])) {
        $_SESSION['user'] = [
            'id'       => '00000000-0000-0000-0000-000000000000',
            'name'     => 'Dev Admin',
            'email'    => 'dev@local',
            'role_id'  => '00000000-0000-0000-0000-000000000000',
            'role'     => 'Admin',
            'class_id' => null,
        ];
    }
}
require_once "Config/Database.php";
require_once "Config/Router.php";

$router = new Router();
$router->run();

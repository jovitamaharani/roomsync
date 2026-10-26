<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../../config/database.php");
require_once __DIR__ . "/../../controllers/classController.php";

$database = new Database();
$db = $database->getConnection();
$controllerClass = 'ClassController';
$classController = new $controllerClass($db);
$stmt = $classController->read();
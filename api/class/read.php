<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once("../../config/database.php");
include_once("../../controllers/classController.php");

$database = new Database();
$db = $database->getConnection();
$classController = new ClassController($db);
$stmt = $classController.read();
?>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../../config/database.php");
include_once("../../controllers/classController.php");

$database = new Database();
$db = $database->getConnection();
$classController = new ClassController($db);
$stmt = $classController->read();

$num = $stmt->rowCount();
if ($num> 0) {
    $classes_arr = array();
    $classes_arr["records"] = array();

    while($row=$smt-> fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $class_item = array(
            "id" => $id,
            "name" => $name,
            "class_year_id" => $class_year_id
        );
        array_push($classes_arr["records"], $class_item);
    }
    http_response_code(200);
    echo json_encode($classes_arr);
}
?>
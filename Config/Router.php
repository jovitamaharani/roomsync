<?php
// Load file Database otomatis di sini agar bisa digunakan oleh SEMUA Controller
require_once __DIR__ . '/Database.php';

class Router {
    public function run() {
        $controllerParam = $_GET['controller'] ?? 'home';
        $action = $_GET['action'] ?? 'index';
        $id = $_GET['id'] ?? null;

        $controllerName = ucfirst($controllerParam) . "Controller";
        
        // Gunakan path absolut dari ROOT project agar tidak kena error path lokasi
        $controllerFile = __DIR__ . "/../app/Controllers/" . $controllerName . ".php";

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controller = new $controllerName();

            if (method_exists($controller, $action)) {
                $controller->$action($id);
            } else {
                echo "404 - Action '$action' tidak ditemukan pada $controllerName!";
            }
        } else {
            echo "404 - Controller '$controllerName' tidak ditemukan!";
        }
    }
}
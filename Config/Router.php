<?php
// Load file Database otomatis di sini agar bisa digunakan oleh SEMUA Controller
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

class Router {
    public function run() {
        $controllerParam = preg_replace('/[^a-z_]/i', '', $_GET['controller'] ?? 'home');        
        $action = $_GET['action'] ?? 'index';
        $id = $_GET['id'] ?? null;

        $controllerName  = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerParam))) . "Controller";
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
            // FALLBACK: Bila Controller belum dibuat, langsung buka View admin-nya jika ada
            $adminViewFile = __DIR__ . "/../app/Views/admin/" . $controllerParam . "/index.php";
            if (file_exists($adminViewFile)) {
                require_once $adminViewFile;
            } else {
                echo "404 - Controller '$controllerName' maupun View admin '$controllerParam' tidak ditemukan!";
            }
        }
    }
}

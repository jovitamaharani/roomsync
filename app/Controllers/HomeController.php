<?php
class HomeController {
    public function index() {
        $user = Auth::user();
        require_once "app/Views/landing.php";
    }

    public function __construct(){
        Auth::requireRole(['Admin', 'Mahasiswa']);
        $database = new Database();
        $bd = $database->getConnection();
    }
    
}
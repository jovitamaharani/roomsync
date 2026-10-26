<?php
class HomeController {
    public function index() {
        $user = Auth::user();
        require_once "app/Views/landing.php";
    }
}
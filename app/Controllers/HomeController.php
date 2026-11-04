<?php
class HomeController
{
    public function index()
    {
        $user = Auth::user();
        if ($user['role'] == 'Mahasiswa') {
            require_once "app/Views/user/dashboard/index.php";
        } else {
            require_once "app/Views/admin/overview/index.php";
        }
    }

    public function __construct()
    {
        Auth::requireRole(['Admin', 'Mahasiswa']);
        $database = new Database();
        $db = $database->getConnection();
    }

}
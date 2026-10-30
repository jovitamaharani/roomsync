<?php
require_once "config/Database.php";
require_once "app/Models/Role.php";

class RoleController
{
    private $coursesModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->coursesModel = new Role($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=courses&action=index");
        exit;
    }

    public function index()
    {
        $roles = $this->coursesModel->getAll();
        require_once "app/Views/courses/index.php";
    }

    public function create()
    {
        require_once "app/Views/courses/create.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            if (!empty($name)) {
                $this->coursesModel->create($name);
            }
            header("Location: index.php?controller=course&action=index");
            $this->redirect('success', 'Berhasil Ditambahkan');
        }
    }

    public function edit($id)
    {
        $role = $this->coursesModel->getById($id);
        if (!$role) {
            $this->redirect('error', 'Data tidak ditemukan');
        }
        require_once "app/Views/user/role/edit.php";
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            if (!empty($id) && !empty($name)) {
                $this->coursesModel->update($id, $name);
            }
            header("Location: index.php?controller=role&action=index");
            exit;
        }
    }

    public function delete($id)
    {
        if (!empty($id)) {
            $this->coursesModel->delete($id);
        }
        header("Location: index.php?controller=role&action=index");
        exit;
    }
}
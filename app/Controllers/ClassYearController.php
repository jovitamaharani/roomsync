<?php
require_once "Config/Database.php";
require_once "app/Models/ClassYear.php";

class ClassYearController
{
    private $classYearModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->classYearModel = new ClassYear($db);
    }

    private function validate($academicYear)
    {
        if ($academicYear === '') {
            return "Tahun ajaran wajib diisi";
        }
        if (!preg_match('/^\d{4}\/\d{4}$/', $academicYear)) {
            return "Format tahun ajaran harus seperti 2025/2026";
        }
        return null;
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=class_year&action=index");
        exit;
    }

    public function index()
    {
        $classYears = $this->classYearModel->getAll();
        require_once "app/Views/class_year/index.php";
    }

    public function create()
    {
        require_once "app/Views/class_year/create.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $academicYear = trim($_POST['academic_year'] ?? '');
            $isActive = isset($_POST['is_active']);

            $error = $this->validate($academicYear);
            if ($error) {
                $this->redirect('error', $error);
            }

            $this->classYearModel->create($academicYear, $isActive);
            $this->redirect('success', 'Tahun ajaran berhasil ditambahkan.');
        }
        $this->redirect();
    }

    public function edit($id)
    {
        $classYear = $this->classYearModel->getById($id);
        if (!$classYear) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }
        require_once "app/Views/class_year/edit.php";
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $academicYear = trim($_POST['academic_year'] ?? '');
            $isActive = isset($_POST['is_active']);

            if ($id === '') {
                $this->redirect('error', 'ID tidak valid.');
            }

            $error = $this->validate($academicYear);
            if ($error) {
                $this->redirect('error', $error);
            }

            $this->classYearModel->update($id, $academicYear, $isActive);
            $this->redirect('success', 'Tahun ajaran berhasil diperbarui.');
        }
        $this->redirect();
    }

    public function delete($id)
    {
        if (empty($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            $this->classYearModel->delete($id);
            $this->redirect('success', 'Tahun ajaran berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Tahun ajaran tidak bisa dihapus karena masih dipakai oleh kelas.');
            }
            throw $e;
        }
    }
}

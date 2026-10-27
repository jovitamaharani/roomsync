<?php
require_once "Config/Database.php";
require_once "app/Models/AcademicYear.php";

class AcademicYearController
{
    private $academicYearModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->academicYearModel = new AcademicYear($db);
    }

    private function validate($name)
    {
        if ($name === '') {
            return "Tahun ajaran wajib diisi";
        }
        if (!preg_match('/^\d{4}\/\d{4}$/', $name)) {
            return "Format tahun ajaran harus seperti 2025/2026";
        }
        return null;
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=academic_year&action=index");
        exit;
    }

    public function index()
    {
        $academicYear = $this->academicYearModel->getAll();
        require_once "app/Views/admin/academic_year/index.php";
    }

    public function create()
    {
        require_once "app/Views/admin/academic_year/create.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $isActive = isset($_POST['is_active']);

            $error = $this->validate($name);
            if ($error) {
                $this->redirect('error', $error);
            }

            $this->academicYearModel->create($name, $isActive);
            $this->redirect('success', 'Tahun ajaran berhasil ditambahkan.');
        }
        $this->redirect();
    }

    public function edit($id)
    {
        $academicYear = $this->academicYearModel->getById($id);
        if (!$academicYear) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }
        require_once "app/Views/admin/academic_year/edit.php";
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $isActive = isset($_POST['is_active']);

            if ($id === '') {
                $this->redirect('error', 'ID tidak valid.');
            }

            $error = $this->validate($name);
            if ($error) {
                $this->redirect('error', $error);
            }

            $this->academicYearModel->update($id, $name, $isActive);
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
            $this->academicYearModel->delete($id);
            $this->redirect('success', 'Tahun ajaran berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Tahun ajaran tidak bisa dihapus karena masih dipakai oleh kelas.');
            }
            throw $e;
        }
    }
}

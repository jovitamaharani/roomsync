<?php
require_once "Config/Database.php";
require_once "app/Models/Lecturer.php";

class LecturerController
{
    private $lecturerModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->lecturerModel = new Lecturer($db);
    }

    private function validate($kode, $name)
    {
        if ($kode === '') {
            return "Kode dosen wajib diisi";
        }
        if ($name === '') {
            return "Nama dosen wajib diisi";
        }
        if (strlen($kode) > 9) {
            return "Kode dosen maksimal 9 huruf.";
        }
        if (strlen($name) > 100) {
            return "Nama dosen maksimal 100 huruf.";
        }
        return null;
    }

    private function redirect($type = null, $message = null, $target = null, $oldData = [])
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message, 'target' => $target, 'old' => $oldData];
        }
        header("Location: index.php?controller=lecturer&action=index");
        exit;
    }

    public function index()
    {
        $lecturers = $this->lecturerModel->getAll();
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function create()
    {
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode = trim($_POST['kode'] ?? '');
            $name = trim($_POST['name'] ?? '');

            $error = $this->validate($kode, $name);
            if ($error) {
                $this->redirect('error', $error);
            }

            $oldData = [
                'kode' => $_POST['kode'],
                'name' => $_POST['name']
            ];

            try {
                $this->lecturerModel->create($kode, $name);
                $this->redirect('success', 'Dosen berhasil ditambahkan.');
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Kode dosen sudah dipakai.', 'add', $oldData);
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function edit($id)
    {
        $lecturers = $this->lecturerModel->getById($id);
        if (!$lecturers) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }
        require_once "app/Views/admin/lecturer/index.php";
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $kode = trim($_POST['kode'] ?? '');
            $name = trim($_POST['name'] ?? '');

            if ($id === '') {
                $this->redirect('error', 'ID tidak valid.');
            }

            $error = $this->validate($kode, $name);
            if ($error) {
                $this->redirect('error', $error);
            }

            $oldData = [
                'id' => $id,
                'kode' => $kode,
                'name' => $name
            ];

            try {
                $this->lecturerModel->update($id, $kode, $name);
                $this->redirect('success', 'Dosen berhasil diperbarui.');
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Kode dosen sudah dipakai.', 'edit', $oldData);
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function delete($id = null)
    {
        $id = $_POST['id'] ?? $id ?? $_GET['id'] ?? null;

        if (empty($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            $this->lecturerModel->delete($id);
            $this->redirect('success', 'Dosen berhasil dihapus.');
        } catch (\PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Dosen tidak bisa dihapus karena masih dipakai oleh data lain.');
            }
            throw $e;
        }
    }
}

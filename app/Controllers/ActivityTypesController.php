<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/ActivityType.php";

class ActivityTypesController
{
    private $activityTypeModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->activityTypeModel = new ActivityType($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=activity_types&action=index");
        exit;
    }

    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id
        ) === 1;
    }

    private function validate($name)
    {
        if ($name === '') {
            return "Nama jenis aktivitas wajib diisi.";
        }
        if (mb_strlen($name) > 50) {
            return "Nama jenis aktivitas maksimal 50 karakter.";
        }
        return null;
    }

    public function index()
    {
        $activityTypes = $this->activityTypeModel->getAll();
        require_once __DIR__ . "/../Views/activityTypes/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $name = trim($_POST['name'] ?? '');
        if ($error = $this->validate($name)) {
            $this->redirect('error', $error);
        }

        try {
            $this->activityTypeModel->create($name);
            $this->redirect('success', 'Jenis aktivitas berhasil ditambahkan.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->redirect('error', 'Jenis aktivitas dengan nama itu sudah ada.');
            }
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id   = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');

        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }
        if ($error = $this->validate($name)) {
            $this->redirect('error', $error);
        }

        try {
            if (!$this->activityTypeModel->update($id, $name)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Jenis aktivitas berhasil diperbarui.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->redirect('error', 'Jenis aktivitas dengan nama itu sudah ada.');
            }
            throw $e;
        }
    }

    public function delete($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('error', 'Permintaan tidak valid.');
        }

        // modal hapus mengirim id lewat POST
        $id = $_POST['id'] ?? $id;
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            if (!$this->activityTypeModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Jenis aktivitas berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Jenis aktivitas tidak bisa dihapus karena sudah dipakai pada laporan.');
            }
            throw $e;
        }
    }
}
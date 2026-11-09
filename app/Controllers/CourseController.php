<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Course.php";

class CourseController
{
    private $courseModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->courseModel = new Course($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=course&action=index");
        exit;
    }

    // Gagal: kembali ke index, modal dibuka lagi dengan isian lama
    private function fail($message, array $data, $modal)
    {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $_SESSION['old']   = ['modal' => $modal, 'data' => $data];
        header("Location: index.php?controller=course&action=index");
        exit;
    }

    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id
        ) === 1;
    }

    private function collect()
    {
        return [
            'kode_mk' => strtoupper(trim($_POST['kode_mk'] ?? '')),
            'name'    => trim($_POST['name'] ?? ''),
        ];
    }

    private function validate(array $d)
    {
        if ($d['kode_mk'] === '' || mb_strlen($d['kode_mk']) > 20) {
            return "Kode mata kuliah wajib diisi (maksimal 20 karakter).";
        }
        if ($d['name'] === '' || mb_strlen($d['name']) > 150) {
            return "Nama mata kuliah wajib diisi (maksimal 150 karakter).";
        }
        return null;
    }

    private function dbErrorMessage(PDOException $e)
    {
        $messages = [
            '23505' => 'Kode mata kuliah sudah terdaftar.',
            '23502' => 'Ada kolom wajib yang belum diisi.',
            '22P02' => 'Format data tidak valid.',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index()
    {
        $courses = $this->courseModel->getAll();
        require_once __DIR__ . "/../Views/admin/courses/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $data = $this->collect();
        if ($error = $this->validate($data)) {
            $this->fail($error, $data, 'add');
        }

        try {
            $this->courseModel->create($data['kode_mk'], $data['name']);
            $this->redirect('success', 'Mata kuliah berhasil ditambahkan.');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->fail($message, $data, 'add');
            }
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = $_POST['id'] ?? '';
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        $data = $this->collect();
        $old  = ['id' => $id] + $data;

        if ($error = $this->validate($data)) {
            $this->fail($error, $old, 'edit');
        }

        try {
            if (!$this->courseModel->update($id, $data['kode_mk'], $data['name'])) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Mata kuliah berhasil diperbarui.');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->fail($message, $old, 'edit');
            }
            throw $e;
        }
    }

    public function delete($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('error', 'Permintaan tidak valid.');
        }

        $id = $_POST['id'] ?? $id;
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            if (!$this->courseModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Mata kuliah berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Mata kuliah tidak bisa dihapus karena sudah dipakai pada jadwal.');
            }
            throw $e;
        }
    }
}
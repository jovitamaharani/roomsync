<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/StudentClass.php";

class StudentClassController
{
    private $classModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->classModel = new StudentClass($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=student_class&action=index");
        exit;
    }

    // Gagal: kembali ke index, modal dibuka lagi dengan isian lama
    private function fail($message, array $data, $modal)
    {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $_SESSION['old']   = ['modal' => $modal, 'data' => $data];
        header("Location: index.php?controller=student_class&action=index");
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
            'academic_year_id' => $_POST['academic_year_id'] ?? '',
            'name'             => trim($_POST['name'] ?? ''),
        ];
    }

    private function validate(array $d)
    {
        if (!$this->isUuid($d['academic_year_id'])) {
            return "Tahun ajaran wajib dipilih.";
        }
        if ($d['name'] === '' || mb_strlen($d['name']) > 50) {
            return "Nama kelas wajib diisi (maksimal 50 karakter).";
        }
        return null;
    }

    private function dbErrorMessage(PDOException $e)
    {
        $messages = [
            '23505' => 'Kelas dengan nama itu sudah ada pada tahun ajaran tersebut.',
            '23503' => 'Tahun ajaran yang dipilih tidak ditemukan.',
            '23502' => 'Ada kolom wajib yang belum diisi.',
            '22P02' => 'Format data tidak valid.',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index()
    {
        $classes       = $this->classModel->getAll();
        $academicYears = $this->classModel->getAcademicYears();
        $canAdd        = !empty($academicYears);
        require_once __DIR__ . "/../Views/admin/student_class/index.php";
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
            $this->classModel->create($data['academic_year_id'], $data['name']);
            $this->redirect('success', 'Kelas berhasil ditambahkan.');
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
            if (!$this->classModel->update($id, $data['academic_year_id'], $data['name'])) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Kelas berhasil diperbarui.');
        } catch (DomainException $e) {
            $this->fail($e->getMessage(), $old, 'edit');
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
            if (!$this->classModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Kelas berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Kelas tidak bisa dihapus karena masih dipakai oleh user atau jadwal.');
            }
            throw $e;
        }
    }
}
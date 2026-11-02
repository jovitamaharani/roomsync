<?php
require_once "Config/Database.php";
require_once "app/Models/AcademicYear.php";

class AcademicYearController
{
    private $academicYearModel;

    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->academicYearModel = new AcademicYear($db);
    }

    private function post($key)
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    private function isUuid($value)
    {
        return preg_match(self::UUID_REGEX, $value) === 1;
    }

    private function validate($name, $status)
    {
        if ($name === '') {
            return "Tahun ajaran wajib diisi";
        }
        if (!preg_match('/^(\d{4})\/(\d{4})$/', $name, $m)) {
            return "Format tahun ajaran harus seperti 2025/2026";
        }
        if ((int) $m[2] !== (int) $m[1] + 1) {
            return "Tahun kedua harus satu tahun setelah tahun pertama, contoh 2025/2026";
        }
        if ($status !== '0' && $status !== '1') {
            return "Status wajib dipilih";
        }
        return null;
    }

    private function redirect($type = null, $message = null, $target = null, $oldData = [])
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message, 'target' => $target, 'old' => $oldData];
        }
        header("Location: index.php?controller=academic_year&action=index");
        exit;
    }

    public function index()
    {
        $academicYears = $this->academicYearModel->getAll();
        require_once "app/Views/admin/academic_year/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $name = $this->post('name');
        $status = $this->post('is_active');
        $oldData = ['name' => $name, 'is_active' => $status];

        $error = $this->validate($name, $status);
        if ($error) {
            $this->redirect('error', $error, 'add', $oldData);
        }

        try {
            // BUG LAMA: isset($_POST['is_active']) selalu true karena <select> selalu terkirim.
            $this->academicYearModel->create($name, $status === '1');
            $this->redirect('success', 'Tahun ajaran berhasil ditambahkan.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->redirect('error', 'Tahun ajaran sudah ada, atau sudah ada tahun ajaran aktif lain.', 'add', $oldData);
            }
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = $this->post('id');
        $name = $this->post('name');
        $status = $this->post('is_active');

        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }
        if (!$this->academicYearModel->getById($id)) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }

        $oldData = ['id' => $id, 'name' => $name, 'is_active' => $status];

        $error = $this->validate($name, $status);
        if ($error) {
            $this->redirect('error', $error, 'edit', $oldData);
        }

        try {
            $this->academicYearModel->update($id, $name, $status === '1');
            $this->redirect('success', 'Tahun ajaran berhasil diperbarui.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->redirect('error', 'Tahun ajaran sudah ada, atau sudah ada tahun ajaran aktif lain.', 'edit', $oldData);
            }
            throw $e;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = $this->post('id');
        if (!$this->isUuid($id)) {
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
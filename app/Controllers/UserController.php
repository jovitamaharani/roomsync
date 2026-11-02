<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/User.php";

class UserController
{
    private $userModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->userModel = new User($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=user&action=index");
        exit;
    }

    // Gagal: kembali ke index, modal dibuka lagi dengan isian lama (TANPA password)
    private function fail($message, array $data, $modal)
    {
        unset($data['password'], $data['password_confirm']);
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $_SESSION['old']   = ['modal' => $modal, 'data' => $data];
        header("Location: index.php?controller=user&action=index");
        exit;
    }

    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id
        ) === 1;
    }

    private function isSelf($id)
    {
        return strtolower((string) (Auth::user()['id'] ?? '')) === strtolower((string) $id);
    }

    private function collect()
    {
        $classId = trim($_POST['student_class_id'] ?? '');
        return [
            'name'             => trim($_POST['name'] ?? ''),
            'email'            => strtolower(trim($_POST['email'] ?? '')),
            'role_id'          => $_POST['role_id'] ?? '',
            'student_class_id' => $classId === '' ? null : $classId,
            'password'         => $_POST['password'] ?? '',          // jangan di-trim
            'password_confirm' => $_POST['password_confirm'] ?? '',
        ];
    }

    private function validate(array $d, $passwordRequired)
    {
        if ($d['name'] === '' || mb_strlen($d['name']) > 100) {
            return "Nama wajib diisi (maksimal 100 karakter).";
        }
        if ($d['email'] === '' || mb_strlen($d['email']) > 150
            || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return "Format email tidak valid.";
        }
        if (!$this->isUuid($d['role_id'])) {
            return "Role wajib dipilih.";
        }
        if ($d['student_class_id'] !== null && !$this->isUuid($d['student_class_id'])) {
            return "Kelas tidak valid.";
        }

        if ($passwordRequired || $d['password'] !== '') {
            if ($d['password'] === '') {
                return "Password wajib diisi.";
            }
            if (mb_strlen($d['password']) < 8) {
                return "Password minimal 8 karakter.";
            }
            if (strlen($d['password']) > 72) {
                return "Password maksimal 72 karakter.";
            }
            if ($d['password'] !== $d['password_confirm']) {
                return "Konfirmasi password tidak cocok.";
            }
        }
        return null;
    }

    private function dbErrorMessage(PDOException $e)
    {
        $messages = [
            '23505' => 'Email sudah terdaftar.',
            '23503' => 'Role atau kelas yang dipilih tidak ditemukan.',
            '23502' => 'Ada kolom wajib yang belum diisi.',
            '22P02' => 'Format data tidak valid.',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index()
    {
        $users   = $this->userModel->getAll();
        $options = $this->userModel->getFormOptions();
        $canAdd  = !empty($options['roles']);
        require_once __DIR__ . "/../Views/user/user/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $data = $this->collect();
        if ($error = $this->validate($data, true)) {
            $this->fail($error, $data, 'add');
        }

        try {
            $this->userModel->create($data);
            $this->redirect('success', 'User berhasil ditambahkan.');
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

        if ($error = $this->validate($data, false)) {
            $this->fail($error, $old, 'edit');
        }

        // Admin tidak boleh mengubah role akunnya sendiri (supaya tidak terkunci)
        if ($this->isSelf($id)) {
            $current = $this->userModel->getById($id);
            if ($current && strtolower($current['role_id']) !== strtolower($data['role_id'])) {
                $this->fail('Anda tidak bisa mengubah role akun Anda sendiri.', $old, 'edit');
            }
        }

        try {
            if (!$this->userModel->update($id, $data)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'User berhasil diperbarui.');
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
        if ($this->isSelf($id)) {
            $this->redirect('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            if (!$this->userModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'User berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'User tidak bisa dihapus karena sudah punya data terkait (peminjaman, laporan, atau log penggunaan ruangan).');
            }
            throw $e;
        }
    }
}
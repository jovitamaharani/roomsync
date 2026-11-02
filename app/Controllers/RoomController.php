<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Room.php";

class RoomController{
    private $roomModel;

    public function __construct(){
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->roomModel = new Room($db);
    }

    private function redirect($type = null, $message = null) {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=room&action=index");
        exit;
    }
    private function backToForm($action, $message, $id = null){
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $url = "index.php?controller=room&action=" . $action;
        if ($id) {
            $url .= "&id=" . urlencode($id);
        }
        header("Location: " . $url);
        exit;
    }

    private function isUuid($id) {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id
        ) === 1;
    }

    private function collect() {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'building' => trim($_POST['building'] ?? ''),
            'floor' => trim($_POST['floor'] ?? ''),
            'capacity' => trim($_POST['capacity'] ?? ''),
            'chairs' => trim($_POST['chairs'] ?? '0'),
            'tables' => trim($_POST['tables'] ?? '0'),
            'facilities' => trim($_POST['facilities'] ?? ''),
            'is_active' => isset($_POST['is_active']),
        ];
    }

    private function validate(array $d) {
        if ($d['name'] === '' || mb_strlen($d['name']) > 100) {
            return "Nama ruangan wajib diisi";
        }
        if ($d['building'] === '' || mb_strlen($d['building']) > 100) {
            return "Gedung wajib diisi";
        }

        $range = fn($min, $max) => ['options' => ['min_range' => $min, 'max_range' => $max]];

        if (filter_var($d['floor'], FILTER_VALIDATE_INT, $range(-5, 100)) === false) {
            return "Lantai harus angka bulat antara -5 sampai 100.";
        }
        if (filter_var($d['capacity'], FILTER_VALIDATE_INT, $range(1, 2000)) === false) {
            return "Kapasitas harus angka bulat antara 1 sampai 2000.";
        }
        if (filter_var($d['chairs'], FILTER_VALIDATE_INT, $range(0, 5000)) === false) {
            return "Jumlah kursi harus angka bulat (0 atau lebih).";
        }
        if (filter_var($d['tables'], FILTER_VALIDATE_INT, $range(0, 5000)) === false) {
            return "Jumlah meja harus angka bulat (0 atau lebih).";
        }
        return null;
    }

    private function dbErrorMessage(PDOException $e) {
        $messages = [
            '23505' => 'ruangan dengan nama itu sudah ada',
            '23514' => 'kapasitas, kursi, dan meja tidak boleh negatif',
            '22P02' => 'format tidak valid',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index() {
        $rooms = $this->roomModel->getAll();
        require_once __DIR__ . "/../Views/user/room/index.php";
    }

    public function create() {
        $values = $_SESSION['old'] ?? ['is_active' => true, 'chairs' => 0, 'tables' => 0];
        unset($_SESSION['old']);

        $title = "Tambah Ruangan";
        $action = "index.php?controller=room&action=store";
        $submitLabel = "Simpan";
        $formId = null;
        require_once __DIR__ . "/../Views/user/room/create.php";
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $data = $this->collect();
        $_SESSION['old'] = $data;

        if ($error = $this->validate($data)) {
            $this->backToForm('create', $error);
        }

        try {
            $this->roomModel->create($data);
            unset($_SESSION['old']);
            $this->redirect('success', 'berhasil ditambahkan');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->backToForm('create', $message);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $room = $this->isUuid($id) ? $this->roomModel->getById($id) : false;
        if (!$room) {
            $this->redirect('error', 'data tidak ditemukan');
        }

        $values = $_SESSION['old'] ?? $room;
        unset($_SESSION['old']);

        $title = "Edit Ruangan";
        $action = "index.php?controller=room&action=update";
        $submitLabel = "Update";
        $formId = $room['id'];
        require_once __DIR__ . "/../Views/user/room/edit.php";
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
        $_SESSION['old'] = $data;

        if ($error = $this->validate($data)) {
            $this->backToForm('edit', $error, $id);
        }

        try {
            if (!$this->roomModel->update($id, $data)) {
                unset($_SESSION['old']);
                $this->redirect('error', 'data tidak ditemukan');
            }
            unset($_SESSION['old']);
            $this->redirect('success', 'berhasil diperbarui');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->backToForm('edit', $message, $id);
            }
            throw $e;
        }
    }

    public function delete($id) {
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }
        try {
            if (!$this->roomModel->delete($id)) {
                $this->redirect('error', 'data tidak ditemukan');
            }
            $this->redirect('success', 'berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Ruangan sudah dipakai oleh jadwal atau peminjaman dan tidak bisa dihapus');
            }
            throw $e;
        }
    }
}
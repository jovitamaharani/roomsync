<?php
require_once "Config/Database.php";
require_once "app/Models/Schedule.php";

class ScheduleController
{
    private $scheduleModel;

    private const FIELDS = [
        'semester',
        'day_of_week',
        'start_time',
        'end_time',
        'course_id',
        'lecturer_id',
        'student_class_id',
        'room_id',
    ];

    private const REFS = [
        'course_id' => ['courses', 'Mata kuliah'],
        'lecturer_id' => ['lecturers', 'Dosen'],
        'student_class_id' => ['student_class', 'Kelas'],
        'room_id' => ['rooms', 'Ruang'],
    ];

    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    private const TIME_REGEX = '/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/';

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->scheduleModel = new Schedule($db);
    }

    private function redirect($type = null, $message = null, $target = null, $oldData = [])
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message, 'target' => $target, 'old' => $oldData];
        }
        header("Location: index.php?controller=schedule&action=index");
        exit;
    }

    private function isUuid($value)
    {
        return is_string($value) && preg_match(self::UUID_REGEX, $value) === 1;
    }

    private function collect()
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = trim($_POST[$field] ?? '');
        }
        return $data;
    }

    private function validate(array $d, $excludeId = null)
    {
        foreach (self::FIELDS as $field) {
            if ($d[$field] === '') {
                return "Semua kolom wajib diisi.";
            }
        }

        foreach (self::REFS as $field => [$table, $label]) {
            if (!$this->isUuid($d[$field])) {
                return "$label tidak valid.";
            }
        }
        if (!in_array($d['semester'], Schedule::SEMESTERS, true)) {
            return "Semester tidak valid.";
        }
        if (!ctype_digit($d['day_of_week']) || (int) $d['day_of_week'] < 1 || (int) $d['day_of_week'] > 5) {
            return "Hari harus Senin sampai Jumat.";
        }
        if (!preg_match(self::TIME_REGEX, $d['start_time']) || !preg_match(self::TIME_REGEX, $d['end_time'])) {
            return "Format jam tidak valid.";
        }
        if (substr($d['start_time'], 0, 5) >= substr($d['end_time'], 0, 5)) {
            return "Jam selesai harus lebih besar dari jam mulai.";
        }

        foreach (self::REFS as $field => [$table, $label]) {
            if (!$this->scheduleModel->refExists($table, $d[$field])) {
                return "$label tidak ditemukan" . ($table === 'rooms' ? " atau tidak aktif." : ".");
            }
        }

        $conflicts = $this->scheduleModel->findConflicts($d, $excludeId);
        if ($conflicts) {
            return implode(' ', $conflicts);
        }

        return null;
    }

    public function index()
    {
        $schedules = $this->scheduleModel->getAll();
        $options = $this->scheduleModel->getOptions();
        require_once "app/Views/admin/schedule/index.php";
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $data = $this->collect();

        $error = $this->validate($data);
        if ($error) {
            $this->redirect('error', $error, 'add', $data);
        }

        try {
            $this->scheduleModel->create($data);
            $this->redirect('success', 'Jadwal berhasil ditambahkan.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503' || $e->getCode() === '22P02') {
                $this->redirect('error', 'Data referensi tidak valid.', 'add', $data);
            }
            throw $e;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = trim($_POST['id'] ?? '');
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }
        if (!$this->scheduleModel->getById($id)) {
            $this->redirect('error', 'Data tidak ditemukan.');
        }

        $data = $this->collect();
        $oldData = ['id' => $id] + $data;

        $error = $this->validate($data, $id);
        if ($error) {
            $this->redirect('error', $error, 'edit', $oldData);
        }

        try {
            $this->scheduleModel->update($id, $data);
            $this->redirect('success', 'Jadwal berhasil diperbarui.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503' || $e->getCode() === '22P02') {
                $this->redirect('error', 'Data referensi tidak valid.', 'edit', $oldData);
            }
            throw $e;
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }

        $id = trim($_POST['id'] ?? '');
        if (!$this->isUuid($id)) {
            $this->redirect('error', 'ID tidak valid.');
        }

        try {
            if (!$this->scheduleModel->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Jadwal berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Jadwal tidak bisa dihapus karena masih dipakai oleh data lain (booking, laporan, log, atau pengecualian).');
            }
            throw $e;
        }
    }
}
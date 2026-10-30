<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Schedule.php";

class ScheduleController
{
    private $schedule;

    private const DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu'
    ];
    private const SEMESTERS = ['ganjil', 'genap'];   // enum semester_gangen

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->schedule = new Schedule($db);
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=schedule&action=index");
        exit;
    }

    // Gagal: kembali ke index, modal dibuka lagi dengan isian lama
    private function fail($message, array $data, $modal)
    {
        $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
        $_SESSION['old'] = ['modal' => $modal, 'data' => $data];
        header("Location: index.php?controller=schedule&action=index");
        exit;
    }

    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id
        ) === 1;
    }

    private function time($value)
    {
        $t = trim($value ?? '');
        return preg_match('/^\d{2}:\d{2}:\d{2}$/', $t) ? substr($t, 0, 5) : $t;
    }

    private function collect()
    {
        return [
            'room_id' => $_POST['room_id'] ?? '',
            'classroom_id' => $_POST['classroom_id'] ?? '',
            'lecturer_id' => $_POST['lecturer_id'] ?? '',
            'course_id' => $_POST['course_id'] ?? '',
            'day_of_week' => (int) ($_POST['day_of_week'] ?? 0),
            'start_time' => $this->time($_POST['start_time'] ?? ''),
            'end_time' => $this->time($_POST['end_time'] ?? ''),
            'semester_id' => $_POST['semester_id'] ?? '',
        ];
    }

    // Hanya memeriksa FORMAT input. Aturan yang butuh database ada di model.
    private function validate(array $d)
    {
        $labels = ['room_id' => 'Ruangan', 'student_class_id' => 'Kelas',
                    'lecturer_id' => 'Dosen', 'course_id' => 'Mata kuliah'];
        foreach ($labels as $field => $label) {
            if (!$this->isUuid($d[$field])) {
                return "$label wajib dipilih.";
            }
        }
        if (!isset(self::DAYS[$d['day_of_week']])) {
            return "Hari tidak valid.";
        }
        if (!in_array($d['semester'], self::SEMESTERS, true)) {
            return "Semester tidak valid.";
        }

        $start = DateTime::createFromFormat('H:i', $d['start_time']);
        $end = DateTime::createFromFormat('H:i', $d['end_time']);
        if (
            !$start || !$end
            || $start->format('H:i') !== $d['start_time']
            || $end->format('H:i') !== $d['end_time']
        ) {
            return "Format jam tidak valid.";
        }
        if ($end <= $start) {
            return "Jam selesai harus setelah jam mulai.";
        }
        return null;
    }

    private function dbErrorMessage(PDOException $e)
    {
        $messages = [
            '23503' => 'Salah satu data yang dipilih (ruangan/kelas/dosen/mata kuliah) tidak ditemukan.',
            '23502' => 'Ada kolom wajib yang belum diisi.',
            '23514' => 'Data melanggar aturan database (cek hari dan jam).',
            '22P02' => 'Format data tidak valid.',
        ];
        return $messages[$e->getCode()] ?? null;
    }

    public function index()
    {
        $schedules = $this->schedule->getAll();
        $options = $this->schedule->getFormOptions();

        // Tombol tambah hanya aktif jika semua dropdown punya pilihan
        $activeRooms = array_filter($options['rooms'], fn($r) => !empty($r['is_active']));
        $canAdd = !empty($activeRooms) && !empty($options['classes'])
            && !empty($options['lecturers']) && !empty($options['courses']);

        $days = self::DAYS;
        $semesters = self::SEMESTERS;
        require_once __DIR__ . "/../Views/user/schedule/index.php";
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
            $this->schedule->createIfFree($data);
            $this->redirect('success', 'Jadwal berhasil ditambahkan.');
        } catch (DomainException $e) {
            $this->fail($e->getMessage(), $data, 'add');       // bentrok, kelas/ruangan tidak valid
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
        $dataWithId = ['id' => $id] + $data;     // untuk membuka lagi modal edit

        if ($error = $this->validate($data)) {
            $this->fail($error, $dataWithId, 'edit');
        }

        try {
            $this->schedule->updateIfFree($id, $data);
            $this->redirect('success', 'Jadwal berhasil diperbarui.');
        } catch (DomainException $e) {
            $this->fail($e->getMessage(), $dataWithId, 'edit');
        } catch (PDOException $e) {
            if ($message = $this->dbErrorMessage($e)) {
                $this->fail($message, $dataWithId, 'edit');
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
            if (!$this->schedule->delete($id)) {
                $this->redirect('error', 'Data tidak ditemukan.');
            }
            $this->redirect('success', 'Jadwal berhasil dihapus.');
        } catch (PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Jadwal tidak bisa dihapus karena sudah punya data terkait (peminjaman, laporan, atau pengecualian jadwal).');
            }
            throw $e;
        }
    }
}
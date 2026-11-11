<?php
require_once "Config/Database.php";
require_once "app/Models/Booking.php";

date_default_timezone_set('Asia/Jakarta');

class BookingController
{
    private $bookingModel;

    public function __construct()
    {
        Auth::requireRole(['Mahasiswa']);
        $database = new Database();
        $db = $database->getConnection();
        $this->bookingModel = new Booking($db);
    }

    private function redirect($type = null, $message = null, $action = 'index', $oldData = [], $id = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message, 'old' => $oldData];
        }

        $returnTo = trim($_POST['return_to'] ?? $_GET['return_to'] ?? '');

        if ($returnTo !== '' && str_starts_with($returnTo, 'index.php?')) {
            $url = $returnTo;
        } else {
            $url = 'index.php?controller=booking&action=' . $action;
            if ($id) {
                $url .= '&id=' . urlencode((string) $id);
            }
        }

        header('Location: ' . $url);
        exit;
    }

    private function requirePost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect();
        }
    }

    private function readInput(): array
    {
        return [
            'room_id' => trim($_POST['room_id'] ?? ''),
            'date' => trim($_POST['date'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? ''),
            'end_time' => trim($_POST['end_time'] ?? ''),
            'purpose' => trim($_POST['purpose'] ?? ''),
        ];
    }

    private function validateInput(array $in): ?string
    {
        foreach (['room_id', 'date', 'start_time', 'end_time', 'purpose'] as $field) {
            if ($in[$field] === '') {
                return 'Semua kolom wajib diisi.';
            }
        }
        $start = strtotime($in['date'] . ' ' . $in['start_time']);
        $end = strtotime($in['date'] . ' ' . $in['end_time']);
        if ($start === false || $end === false) {
            return 'Format tanggal atau jam tidak valid.';
        }
        if ($end <= $start) {
            return 'Jam selesai harus lebih besar dari jam mulai.';
        }
        if ($start < time()) {
            return 'Waktu booking yang dipilih sudah lewat.';
        }
        return null;
    }

    private function dbError(PDOException $e): string
    {
        $message = match ($e->getCode()) {
            'RB001' => 'Ruangan tidak ditemukan atau sedang nonaktif.',
            'RB002' => 'Ruangan sudah dibooking pengguna lain pada jam tersebut.',
            'RB003' => 'Ruangan sedang dipakai jadwal perkuliahan pada jam tersebut.',
            'RB004' => 'Waktu booking yang dipilih sudah lewat.',
            'RB005' => 'Keperluan peminjaman ruangan wajib diisi.',
            'RB010' => 'Data booking tidak ditemukan.',
            'RB011' => 'Anda tidak berhak membatalkan booking ini.',
            'RB012' => 'Booking ini sudah dibatalkan sebelumnya.',
            'RB013' => 'Booking sudah selesai atau terlewat sehingga tidak bisa dibatalkan.',
            '22P02' => 'Data tidak valid. Pilih ruangan dari daftar yang tersedia.',
            '23503' => 'Jam booking harus sesuai jam kuliah (jam ke-1 sampai ke-12).',
            default => null,
        };
        if ($message === null) {
            error_log('[BookingController] ' . $e->getMessage());
            $message = 'Terjadi kesalahan pada server. Silakan coba lagi.';
        }
        return $message;
    }

    private function findOwnConfirmed(string $id): array
    {
        $booking = false;
        if ($id !== '') {
            try {
                $booking = $this->bookingModel->getById($id);
            } catch (PDOException $e) {
                $booking = false;
            }
        }
        if (!$booking) {
            $this->redirect('error', 'Data booking tidak ditemukan.');
        }
        if ((string) $booking['user_id'] !== (string) $_SESSION['user']['id']) {
            $this->redirect('error', 'Anda tidak berhak mengubah booking ini.');
        }
        if (($booking['status'] ?? '') !== 'confirmed') {
            $this->redirect('error', 'Booking ini sudah tidak dapat diubah.');
        }
        return $booking;
    }

    public function index()
    {
        $bookings = $this->bookingModel->getAll();
        $rooms = $this->bookingModel->getActiveRooms();
        require_once "app/Views/user/booking/index.php";
    }

    public function availableRooms()
    {
        header('Content-Type: application/json; charset=utf-8');

        $date = trim($_GET['date'] ?? '');
        $startTime = trim($_GET['start_time'] ?? '');
        $endTime = trim($_GET['end_time'] ?? '');
        $excludeId = trim($_GET['exclude_id'] ?? '') ?: null;

        if ($date === '' || $startTime === '' || $endTime === '' || strtotime($startTime) >= strtotime($endTime)) {
            echo json_encode([]);
            exit;
        }

        try {
            $rooms = $this->bookingModel->getAvailableRooms($date, $startTime, $endTime, $excludeId);
            echo json_encode($rooms);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal memuat ketersediaan ruangan']);
        }
        exit;
    }

    public function create()
    {
        $this->index();
    }

    public function store()
    {
        $this->requirePost();

        $in = $this->readInput();
        $userId = $_SESSION['user']['id'];

        $error = $this->validateInput($in);
        if ($error) {
            $this->redirect('error', $error, 'index', $in);
        }

        try {
            $this->bookingModel->create(
                $in['room_id'],
                $userId,
                $in['date'],
                $in['start_time'],
                $in['end_time'],
                $in['purpose']
            );
            $this->redirect('success', 'Ruangan berhasil dibooking.');
        } catch (PDOException $e) {
            $this->redirect('error', $this->dbError($e), 'index', $in);
        }
    }

    public function edit($id = null)
    {
        $this->redirect(null, null, 'riwayat');
    }

    public function update()
    {
        $this->requirePost();

        $id = trim($_POST['id'] ?? '');
        $in = $this->readInput();
        $userId = $_SESSION['user']['id'];

        $this->findOwnConfirmed($id);

        $error = $this->validateInput($in);
        if ($error) {
            $this->redirect('error', $error, 'riwayat', $in);
        }

        try {
            if (!$this->bookingModel->isRoomAvailable($in['room_id'], $in['date'], $in['start_time'], $in['end_time'], $id)) {
                $this->redirect('error', 'Ruangan sudah dibooking atau sedang dipakai jadwal perkuliahan pada jam tersebut.', 'riwayat', $in);
            }

            $updated = $this->bookingModel->updateByUser(
                $id,
                $userId,
                $in['room_id'],
                $in['date'],
                $in['start_time'],
                $in['end_time'],
                $in['purpose']
            );
            if (!$updated) {
                $this->redirect('error', 'Booking tidak dapat diubah.', 'riwayat', $in);
            }

            $this->redirect('success', 'Booking berhasil diperbarui.', 'riwayat');
        } catch (PDOException $e) {
            $this->redirect('error', $this->dbError($e), 'riwayat', $in);
        }
    }

    public function cancel()
    {
        $this->requirePost();

        $bookingId = trim($_POST['booking_id'] ?? '');
        $userId = $_SESSION['user']['id'];

        if ($bookingId === '') {
            $this->redirect('error', 'Data booking tidak ditemukan.');
        }

        try {
            $this->bookingModel->cancel($bookingId, $userId, false);
            $this->redirect('success', 'Booking berhasil dibatalkan.');
        } catch (PDOException $e) {
            $this->redirect('error', $this->dbError($e));
        }
    }

    public function slots()
    {
        header('Content-Type: application/json; charset=utf-8');

        $roomId = trim($_GET['room_id'] ?? '');
        $date = trim($_GET['date'] ?? '');

        // Validasi parameter dasar & format UUID PostgreSQL
        if ($roomId === '' || $date === '' || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $roomId)) {
            echo json_encode([]);
            exit;
        }

        try {
            $slots = $this->bookingModel->getRoomSlots($roomId, $date);
            echo json_encode($slots);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Gagal memuat slot jam']);
        }
        exit;
    }
}
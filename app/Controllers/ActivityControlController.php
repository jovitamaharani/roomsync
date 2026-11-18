<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Report.php";

/**
 * Aksi POST untuk halaman Activity Control (Ketua Kelas).
 * Halaman formnya sendiri tetap ditampilkan hub: index.php?controller=user&view=activity
 */
class ActivityControlController
{
    private const BACK_URL = "index.php?controller=user&view=activity";

    private $reportModel;

    public function __construct()
    {
        Auth::requireLogin();
        $db = (new Database())->getConnection();
        $this->reportModel = new Report($db);
    }

    // Kembali ke halaman form dengan pesan; isian lama disimpan agar tidak perlu mengetik ulang
    private function back($type = null, $message = null, array $old = [])
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        if ($old) {
            unset($old['csrf_token']);
            $_SESSION['activity_old'] = $old;
        }
        header("Location: " . self::BACK_URL);
        exit;
    }

    // Hanya POST dengan token CSRF yang valid
    private function requirePost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->back('error', 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
        }
    }

    private function isUuid($id)
    {
        return is_string($id) && preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id
        ) === 1;
    }

    private function validTime($t)
    {
        $d = DateTime::createFromFormat('H:i', $t);
        return $d && $d->format('H:i') === $t;
    }

    private function collect()
    {
        return [
            'activity_type_id'    => $_POST['activity_type_id'] ?? '',
            'mode'                => $_POST['mode'] ?? '',
            'description'         => trim($_POST['description'] ?? ''),
            'student_attendance'  => trim($_POST['student_attendance'] ?? ''),
            'lecturer_attendance' => $_POST['lecturer_attendance'] ?? '',
            'lecturer_enter'      => trim($_POST['lecturer_enter'] ?? ''),
            'lecturer_leave'      => trim($_POST['lecturer_leave'] ?? ''),
        ];
    }

    // Memeriksa FORMAT dan nilai yang diizinkan; hak akses sesi sudah dicek lewat findSession()
    private function validate(array $d)
    {
        $typeIds = array_map('strtolower', array_column($this->reportModel->getActivityTypes(), 'id'));
        if (!$this->isUuid($d['activity_type_id']) || !in_array(strtolower($d['activity_type_id']), $typeIds, true)) {
            return "Jenis aktivitas tidak valid.";
        }
        if (!array_key_exists($d['mode'], Report::MODES)) {
            return "Metode perkuliahan tidak valid.";
        }
        if (!array_key_exists($d['lecturer_attendance'], Report::LECTURER_STATUS)) {
            return "Status kehadiran dosen tidak valid.";
        }

        $range = ['options' => ['min_range' => 0, 'max_range' => 500]];
        if (filter_var($d['student_attendance'], FILTER_VALIDATE_INT, $range) === false) {
            return "Kehadiran mahasiswa harus angka bulat antara 0 sampai 500.";
        }

        if (!$this->validTime($d['lecturer_enter']) || !$this->validTime($d['lecturer_leave'])) {
            return "Format jam masuk atau jam keluar dosen tidak valid.";
        }
        if ($d['lecturer_leave'] <= $d['lecturer_enter']) {
            return "Jam keluar dosen harus setelah jam masuk.";
        }

        if ($d['description'] === '') {
            return "Materi perkuliahan dan catatan jurnal wajib diisi.";
        }
        if (mb_strlen($d['description']) > 2000) {
            return "Catatan maksimal 2000 karakter.";
        }
        return null;
    }

    // POST: simpan jurnal laporan
    public function store()
    {
        $this->requirePost();
        $user = Auth::user();

        $session = $this->reportModel->findSession($user, $_POST['session'] ?? '');
        if (!$session) {
            $this->back('error', 'Sesi tidak ditemukan atau Anda tidak berhak melaporkannya.');
        }

        $data = $this->collect();
        if ($error = $this->validate($data)) {
            $this->back('error', $error, $_POST);
        }

        try {
            $this->reportModel->saveReport($session, $user, $data);
            $this->back('success', 'Laporan berhasil disimpan.');
        } catch (DomainException $e) {
            $this->back('error', $e->getMessage(), $_POST);
        } catch (PDOException $e) {
            $messages = [
                '23505' => 'Sesi ini sudah dilaporkan.',
                '23503' => 'Data terkait (jadwal atau jenis aktivitas) tidak ditemukan.',
                '23514' => 'Data melanggar aturan database (cek kehadiran dan jam).',
                '22P02' => 'Format data tidak valid.',
            ];
            if (isset($messages[$e->getCode()])) {
                $this->back('error', $messages[$e->getCode()], $_POST);
            }
            throw $e;
        }
    }

    // POST: konfirmasi mahasiswa bubar (ruangan langsung kosong)
    public function dismiss()
    {
        $this->requirePost();
        $user = Auth::user();

        $session = $this->reportModel->findSession($user, $_POST['session'] ?? '');
        if (!$session) {
            $this->back('error', 'Sesi tidak ditemukan atau Anda tidak berhak mengonfirmasinya.');
        }

        try {
            $time = $this->reportModel->dismiss($session, $user);
            $this->back('success', "Mahasiswa bubar dikonfirmasi pukul $time. Ruangan sekarang berstatus Kosong. Jangan lupa mengisi jurnal laporan.", $_POST);
        } catch (DomainException $e) {
            $this->back('error', $e->getMessage(), $_POST);
        }
    }
}
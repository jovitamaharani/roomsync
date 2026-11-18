<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/SupabaseStorage.php";
require_once __DIR__ . "/../Models/User.php";

/**
 * Pengguna mengedit profilnya sendiri: info pribadi, kata sandi, dan foto.
 * Halamannya ditampilkan oleh hub (index.php?controller=user&view=profile);
 * controller ini hanya memproses aksi POST lalu kembali ke halaman itu.
 */
class ProfileController
{
    private const PROFILE_URL = 'index.php?controller=user&view=profile';
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_PHOTO_BYTES = 3 * 1024 * 1024;   // 3 MB, sesuai teks di halaman

    private $userModel;
    private $storage = null;

    public function __construct()
    {
        Auth::requireLogin();
        $db = (new Database())->getConnection();
        $this->userModel = new User($db);
    }

    // ---------- Fungsi bantu ----------

    private function back($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header('Location: ' . self::PROFILE_URL);
        exit;
    }

    // Semua aksi tulis harus POST dengan token CSRF yang valid
    private function guardPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->back();
        }
        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->back('error', 'Sesi form tidak valid, silakan muat ulang halaman lalu coba lagi.');
        }
    }

    // Selalu akun yang sedang login. ID tidak pernah diambil dari input form.
    private function currentId()
    {
        return Auth::user()['id'];
    }

    // Penyimpanan foto dibuat saat dibutuhkan, supaya edit info dan kata sandi
    // tetap jalan walaupun konfigurasi Supabase belum diisi.
    private function storage()
    {
        if ($this->storage === null) {
            $this->storage = new SupabaseStorage('profile_bucket');
        }
        return $this->storage;
    }

    // Dipakai sebelum upload/hapus foto: kalau config/supabase.php belum ada atau salah, tampilkan pesan yang jelas
    private function storageOrFail()
    {
        try {
            return $this->storage();
        } catch (Throwable $e) {
            error_log('[profile] penyimpanan foto belum siap: ' . $e->getMessage());
            $this->back('error', 'Penyimpanan foto belum dikonfigurasi. Hubungi admin.');
        }
    }

    // Kolom users.no_hp bertipe bigint (angka 0 di depan hilang), jadi disimpan dalam format 62xxxxxxxxxx.
    // Mengembalikan [valid, nilai]; nilai null berarti nomor dikosongkan.
    private function normalizePhone($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [true, null];
        }
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return [false, null];   // diisi, tetapi tidak ada angka sama sekali
        }
        if (strpos($digits, '0') === 0) {
            $digits = '62' . substr($digits, 1);
        } elseif (strpos($digits, '62') !== 0) {
            $digits = '62' . $digits;
        }
        if (!preg_match('/^62[0-9]{9,12}$/', $digits)) {
            return [false, null];
        }
        return [true, (int) $digits];
    }

    // ---------- Aksi ----------

    public function index()
    {
        $this->back();
    }

    public function updateInfo()
    {
        $this->guardPost();

        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        [$phoneOk, $phone] = $this->normalizePhone($_POST['no_hp'] ?? '');

        if ($name === '' || mb_strlen($name) > 100) {
            $this->back('error', 'Nama wajib diisi (maksimal 100 karakter).');
        }
        if ($email === '' || mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->back('error', 'Format email tidak valid.');
        }
        if (!$phoneOk) {
            $this->back('error', 'Nomor HP tidak valid. Contoh: 081234567890.');
        }

        try {
            $this->userModel->updateProfile($this->currentId(), [
                'name' => $name,
                'email' => $email,
                'no_hp' => $phone,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->back('error', 'Email sudah digunakan akun lain.');
            }
            throw $e;
        }

        // Samakan data di session agar navbar langsung menampilkan nama baru
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        $this->back('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $this->guardPost();

        $current = $_POST['current_password'] ?? '';   // jangan di-trim
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $hash = $this->userModel->getPasswordHash($this->currentId());
        if (!$hash || !password_verify($current, $hash)) {
            $this->back('error', 'Kata sandi saat ini salah.');
        }
        if (mb_strlen($new) < 8) {
            $this->back('error', 'Kata sandi baru minimal 8 karakter.');
        }
        if (strlen($new) > 72) {
            $this->back('error', 'Kata sandi baru maksimal 72 karakter.');
        }
        if ($new !== $confirm) {
            $this->back('error', 'Konfirmasi kata sandi baru tidak cocok.');
        }
        if (password_verify($new, $hash)) {
            $this->back('error', 'Kata sandi baru tidak boleh sama dengan yang lama.');
        }

        $this->userModel->updatePassword($this->currentId(), password_hash($new, PASSWORD_DEFAULT));
        session_regenerate_id(true);   // sesi baru setelah kredensial berubah

        $this->back('success', 'Kata sandi berhasil diperbarui.');
    }

    public function uploadPhoto()
    {
        // File melebihi post_max_size: PHP mengosongkan $_POST dan $_FILES sehingga token CSRF ikut hilang
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
            && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->back('error', 'Ukuran foto melebihi batas server. Gunakan foto maksimal 3 MB.');
        }
        $this->guardPost();

        $file = $_FILES['photo'] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->back('error', 'Pilih foto terlebih dahulu.');
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $this->back('error', 'Ukuran foto melebihi batas server. Gunakan foto maksimal 3 MB.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->back('error', 'Foto gagal diunggah, silakan coba lagi.');
        }
        if ($file['size'] > self::MAX_PHOTO_BYTES) {
            $this->back('error', 'Ukuran foto maksimal 3 MB.');
        }

        // Jenis file dicek dari isinya, bukan dari nama atau tipe yang dikirim browser
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::IMAGE_TYPES[$mime])) {
            $this->back('error', 'Format foto harus JPG, PNG, atau WEBP.');
        }
        if (@getimagesize($file['tmp_name']) === false) {
            $this->back('error', 'File bukan gambar yang valid.');
        }

        $storage = $this->storageOrFail();

        $name = 'profile-' . bin2hex(random_bytes(16)) . '.' . self::IMAGE_TYPES[$mime];
        $url = $storage->upload($file['tmp_name'], $name, $mime);
        if (!$url) {
            $this->back('error', 'Gagal mengunggah foto ke penyimpanan.');
        }

        $old = ($this->userModel->getProfile($this->currentId()) ?: [])['photo_path'] ?? null;
        try {
            $this->userModel->updatePhoto($this->currentId(), $url);
        } catch (Throwable $e) {
            $storage->deleteByUrl($url);   // batalkan upload kalau database gagal
            throw $e;
        }
        $storage->deleteByUrl($old);       // buang foto lama
        $_SESSION['user']['photo'] = $url;

        $this->back('success', 'Foto profil berhasil diperbarui.');
    }

    public function resetPhoto()
    {
        $this->guardPost();

        $old = ($this->userModel->getProfile($this->currentId()) ?: [])['photo_path'] ?? null;
        $this->userModel->updatePhoto($this->currentId(), null);
        $_SESSION['user']['photo'] = null;

        if ($old) {
            try {
                $this->storage()->deleteByUrl($old);
            } catch (Throwable $e) {
                error_log('[profile] hapus foto lama: ' . $e->getMessage());   // foto di database sudah dilepas
            }
        }
        $this->back('success', 'Foto profil dikembalikan ke default.');
    }
}

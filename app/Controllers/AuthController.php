<?php
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Config/Auth.php';
require_once __DIR__ . '/../../Config/ResetToken.php';
require_once __DIR__ . '/../../Config/Mailer.php';
require_once __DIR__ . '/../../Config/RememberToken.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private $userModel;
    private const LOGIN_URL = "index.php?controller=auth&action=login";
    private const FORGOT_URL = "index.php?controller=auth&action=forgotPassword";
    private const SENT_URL = "index.php?controller=auth&action=resetSent";
    private const RESET_URL = "index.php?controller=auth&action=resetPassword&token=";
    private const RESET_COOLDOWN = 60;
    private const MIN_PASSWORD = 8;
    private const MAX_PASSWORD = 72;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->userModel = new User($db);
    }

    private function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    private function redirectToLogin($type = null, $message = null, $oldEmail = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        if ($oldEmail !== null) {
            $_SESSION['old_email'] = $oldEmail;
        }
        $this->redirect(self::LOGIN_URL);
    }

    private function redirectWithFlash($url, $type, $message)
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        $this->redirect($url);
    }

    private function appUrl()
    {
        $url = rtrim((string) getenv('APP_URL'), '/');
        return $url !== '' ? $url : 'http://localhost:8000';
    }

    private function userFromToken(string $token)
    {
        try {
            $id = ResetToken::userIdOf($token);
            if ($id === null) {
                return null;
            }
            $user = $this->userModel->findForReset($id);
            if (!$user || !ResetToken::verify($token, (string) $user['password'])) {
                return null;
            }
            return $user;
        } catch (Throwable $e) {
            error_log('[AuthController::userFromToken] ' . $e->getMessage());
            return null;
        }
    }

    public function login()
    {
        if (Auth::check()) {
            $this->redirect("index.php?controller=home&action=index");
        }

        $quickAccount = null;

        if (!empty($_COOKIE[RememberToken::COOKIE_NAME])) {
            $parsed = RememberToken::parsePayload($_COOKIE[RememberToken::COOKIE_NAME]);
            if ($parsed !== null) {
                $user = $this->userModel->findForReset($parsed['uid']);
                if ($user && RememberToken::verify($_COOKIE[RememberToken::COOKIE_NAME], (string) $user['password'])) {
                    $quickAccount = [
                        'name' => (string) $user['name'],
                        'email' => (string) $user['email'],
                    ];
                } else {
                    RememberToken::clearCookie();
                }
            } else {
                RememberToken::clearCookie();
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }
    public function quickLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
        }

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirectToLogin('error', 'Sesi form tidak valid, silahkan coba lagi.');
        }

        $cookieValue = $_COOKIE[RememberToken::COOKIE_NAME] ?? '';
        if ($cookieValue === '') {
            $this->redirectToLogin('error', 'Sesi masuk cepat tidak ditemukan. Silakan masuk manual.');
        }

        $parsed = RememberToken::parsePayload($cookieValue);
        if ($parsed === null) {
            RememberToken::clearCookie();
            $this->redirectToLogin('error', 'Sesi masuk cepat sudah kedaluwarsa. Silakan masuk manual.');
        }

        $user = $this->userModel->findForReset($parsed['uid']);
        if (!$user || !RememberToken::verify($cookieValue, (string) $user['password'])) {
            RememberToken::clearCookie();
            $this->redirectToLogin('error', 'Sesi masuk cepat tidak valid. Silakan masuk manual.');
        }

        $fullUser = $this->userModel->getById($user['id']);
        if (!$fullUser) {
            RememberToken::clearCookie();
            $this->redirectToLogin('error', 'Akun tidak ditemukan.');
        }

        Auth::login($fullUser);

        $refreshedToken = RememberToken::create($user['id'], (string) $user['password']);
        RememberToken::setCookie($refreshedToken);

        $currentUser = Auth::user();
        if (isset($currentUser['role']) && $currentUser['role'] === 'Mahasiswa') {
            $this->redirect("index.php?controller=user&action=landing");
        } else {
            $this->redirect("index.php?controller=home&action=index");
        }
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
        }

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirectToLogin('error', 'Sesi form tidak valid, silahkan coba lagi.');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->redirectToLogin('error', 'Email dan kata sandi wajib diisi.', $email);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->redirectToLogin('error', 'Email atau kata sandi salah.', $email);
        }

        Auth::login($user);

        if (!empty($_POST['remember'])) {
            $rememberToken = RememberToken::create($user['id'], $user['password']);
            RememberToken::setCookie($rememberToken);
        } else {
            RememberToken::clearCookie();
        }

        $currentUser = Auth::user();

        if (isset($currentUser['role']) && $currentUser['role'] === 'Mahasiswa') {
            $this->redirect("index.php?controller=user&action=landing");
        } else {
            $this->redirect("index.php?controller=home&action=index");
        }
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirect("index.php?controller=home&action=index");
        }

        RememberToken::clearCookie();
        Auth::logout();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anda telah keluar.'];
        $this->redirect("index.php?controller=home&action=index");
    }

    public function forgotPassword()
    {
        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function sendResetLink()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(self::FORGOT_URL);
        }

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirectWithFlash(self::FORGOT_URL, 'error', 'Sesi form tidak valid, silahkan coba lagi.');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        if ($email === '' || mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithFlash(self::FORGOT_URL, 'error', 'Format email tidak valid.');
        }

        $last = (int) ($_SESSION['reset_last_sent'] ?? 0);
        if (time() - $last < self::RESET_COOLDOWN) {
            $_SESSION['reset_sent_email'] = $email;
            $this->redirectWithFlash(self::SENT_URL, 'error', 'Mohon tunggu 1 menit sebelum meminta tautan lagi.');
        }
        $_SESSION['reset_last_sent'] = time();

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token = ResetToken::create((string) $user['id'], (string) $user['password']);
                $link = $this->appUrl() . '/index.php?controller=auth&action=resetPassword&token=' . $token;

                [$html, $text] = Mailer::resetPasswordEmail((string) $user['name'], $link, intdiv(ResetToken::TTL, 60));
                Mailer::send((string) $user['email'], (string) $user['name'], 'Atur Ulang Kata Sandi RIMORA', $html, $text);
            }
        } catch (Throwable $e) {
            error_log('[AuthController::sendResetLink] ' . $e->getMessage());
        }

        $_SESSION['reset_sent_email'] = $email;
        $this->redirect(self::SENT_URL);
    }

    public function resetSent()
    {
        $email = (string) ($_SESSION['reset_sent_email'] ?? '');
        if ($email === '') {
            $this->redirect(self::FORGOT_URL);
        }
        require __DIR__ . '/../Views/auth/reset_sent.php';
    }

    public function resetPassword()
    {
        header('Referrer-Policy: no-referrer');

        $token = (string) ($_GET['token'] ?? '');
        $user = $this->userFromToken($token);

        if (!$user) {
            $this->redirectWithFlash(self::FORGOT_URL, 'error', 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.');
        }

        $email = $user['email'];
        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(self::FORGOT_URL);
        }

        if (!Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirectWithFlash(self::FORGOT_URL, 'error', 'Sesi form tidak valid, silahkan coba lagi.');
        }

        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $user = $this->userFromToken($token);
        if (!$user) {
            $this->redirectWithFlash(self::FORGOT_URL, 'error', 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.');
        }

        $backUrl = self::RESET_URL . urlencode($token);

        if (mb_strlen($password) < self::MIN_PASSWORD) {
            $this->redirectWithFlash($backUrl, 'error', 'Kata sandi minimal ' . self::MIN_PASSWORD . ' karakter.');
        }
        if (strlen($password) > self::MAX_PASSWORD) {
            $this->redirectWithFlash($backUrl, 'error', 'Kata sandi maksimal ' . self::MAX_PASSWORD . ' karakter.');
        }
        if ($password !== $confirm) {
            $this->redirectWithFlash($backUrl, 'error', 'Konfirmasi kata sandi tidak cocok.');
        }

        try {
            $this->userModel->updatePasswordById((string) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        } catch (Throwable $e) {
            error_log('[AuthController::updatePassword] ' . $e->getMessage());
            $this->redirectWithFlash($backUrl, 'error', 'Terjadi kesalahan pada server. Silakan coba lagi.');
        }

        unset($_SESSION['reset_sent_email'], $_SESSION['reset_last_sent']);
        $this->redirectToLogin('success', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
    }
}
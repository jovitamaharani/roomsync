<?php
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Config/Auth.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private $userModel;

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
        $this->redirect("index.php?controller=auth&action=login");
    }

    public function login()
    {
        if (Auth::check()) {
            $this->redirect("index.php?controller=home&action=index");
        }
        require __DIR__ . '/../Views/auth/login.php';
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
        $this->redirect("index.php?controller=home&action=index");
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Auth::verifyCsrf($_POST['csrf_token'] ?? '')) {
            $this->redirect("index.php?controller=home&action=index");
        }

        Auth::logout();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Anda telah keluar.'];
        $this->redirect("index.php?controller=home&action=index");
    }

}
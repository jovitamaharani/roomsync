<?php
class Auth
{
    private static function startSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function check()
    {
        self::startSession();
        return !empty($_SESSION['user']['id']);
    }

    public static function user()
    {
        return self::check() ? $_SESSION['user'] : null;
    }

    public static function login(array $user)
    {
        self::startSession();

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role' => $user['role_name'],
            'class_id' => $user['class_id'],
        ];
    }

    public static function logout()
    {
        self::startSession();
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Silahkan login terlebih dahulu.',
            ];
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }

    public static function requireRole(array $roles)
    {
        self::requireLogin();

        $current = strtolower($_SESSION['user']['role']);
        $allowed = array_map('strtolower', $roles);

        if (!in_array($current, $allowed, true)) {
            http_response_code(403);
            echo "403 - Anda tidak memiliki akses ke halaman ini";
            exit;
        }
    }

    public static function csrfToken()
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf($token)
    {
        self::startSession();
        return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
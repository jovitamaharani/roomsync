<?php

class RememberToken
{
    public const COOKIE_NAME = 'rimora_remember';
    public const TTL_SECONDS = 2592000;

    public static function create(int|string $userId, string $passwordHash, int $ttl = self::TTL_SECONDS): string
    {
        $exp = time() + $ttl;
        $payload = base64_encode(json_encode([
            'uid' => (string) $userId,
            'exp' => $exp,
        ], JSON_UNESCAPED_SLASHES));

        $sig = hash_hmac('sha256', $payload . '|' . $passwordHash, self::secret());
        return $payload . '.' . $sig;
    }

    public static function parsePayload(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $raw = base64_decode($parts[0], true);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['uid']) || empty($data['exp'])) {
            return null;
        }

        if ((int) $data['exp'] < time()) {
            return null;
        }

        return [
            'uid' => (string) $data['uid'],
            'exp' => (int) $data['exp'],
            'payload' => $parts[0],
            'sig' => $parts[1],
        ];
    }

    public static function verify(string $token, string $passwordHash): bool
    {
        $parsed = self::parsePayload($token);
        if ($parsed === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $parsed['payload'] . '|' . $passwordHash, self::secret());
        return hash_equals($expected, $parsed['sig']);
    }

    public static function setCookie(string $token, int $ttl = self::TTL_SECONDS): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(self::COOKIE_NAME, $token, [
            'expires' => time() + $ttl,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function clearCookie(): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(self::COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    private static function secret(): string
    {
        $key = getenv('APP_KEY');
        if ($key === false || strlen(trim($key)) < 32) {
            throw new RuntimeException('APP_KEY belum diatur atau kurang dari 32 karakter.');
        }
        return $key;
    }
}
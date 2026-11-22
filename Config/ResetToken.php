<?php

class ResetToken
{
    public const TTL = 1800;

    private static function key()
    {
        $key = (string) getenv('APP_KEY');
        if (strlen($key) < 32) {
            throw new RuntimeException('APP_KEY belum diatur (minimal 32 karakter).');
        }
        return $key;
    }

    private static function encode(string $data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function decode(string $data)
    {
        return base64_decode(strtr($data, '-_', '+/'), true);
    }

    private static function sign(string $payload, string $passwordHash)
    {
        return hash_hmac('sha256', $payload . '|' . $passwordHash, self::key(), true);
    }

    public static function create(string $userId, string $passwordHash)
    {
        $payload = self::encode(json_encode(['u' => $userId, 'e' => time() + self::TTL]));
        return $payload . '.' . self::encode(self::sign($payload, $passwordHash));
    }

    public static function userIdOf(string $token)
    {
        if (strlen($token) > 512 || substr_count($token, '.') !== 1) {
            return null;
        }
        [$payload] = explode('.', $token, 2);
        $json = self::decode($payload);
        $data = $json === false ? null : json_decode($json, true);

        if (!is_array($data) || !isset($data['u']) || !is_string($data['u'])) {
            return null;
        }
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data['u']) === 1;

        return $isUuid ? $data['u'] : null;
    }

    public static function verify(string $token, string $passwordHash)
    {
        if (strlen($token) > 512 || substr_count($token, '.') !== 1) {
            return false;
        }
        [$payload, $signature] = explode('.', $token, 2);

        $json = self::decode($payload);
        $data = $json === false ? null : json_decode($json, true);
        if (!is_array($data) || !isset($data['e']) || !is_int($data['e']) || $data['e'] < time()) {
            return false;
        }

        $given = self::decode($signature);
        if ($given === false) {
            return false;
        }

        return hash_equals(self::sign($payload, $passwordHash), $given);
    }
}
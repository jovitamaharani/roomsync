<?php

class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $html, string $text)
    {
        $driver = getenv('MAIL_DRIVER') ?: 'brevo';

        if ($driver === 'log') {
            error_log('[Mailer:log] Ke: ' . $toEmail . ' | ' . $subject . "\n" . $text);
            return true;
        }

        $apiKey = (string) getenv('BREVO_API_KEY');
        $from = (string) getenv('MAIL_FROM');
        $fromName = (string) (getenv('MAIL_FROM_NAME') ?: 'RIMORA');

        if ($apiKey === '' || $from === '' || !function_exists('curl_init')) {
            error_log('[Mailer] Konfigurasi belum lengkap (BREVO_API_KEY, MAIL_FROM, ekstensi curl).');
            return false;
        }

        $payload = json_encode([
            'sender' => ['name' => $fromName, 'email' => $from],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'subject' => $subject,
            'htmlContent' => $html,
            'textContent' => $text,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: ' . $apiKey,
            ],
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        unset($ch);

        if ($status >= 200 && $status < 300) {
            return true;
        }

        error_log('[Mailer] Gagal kirim (HTTP ' . $status . '): ' . $error . ' ' . $response);
        return false;
    }

    public static function resetPasswordEmail(string $name, string $link, int $minutes)
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

        $html = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;color:#111827">'
            . '<h2 style="color:#0E2C6A">Atur Ulang Kata Sandi RIMORA</h2>'
            . '<p>Halo ' . $safeName . ',</p>'
            . '<p>Kami menerima permintaan untuk mengganti kata sandi akun Anda. Klik tombol di bawah untuk membuat kata sandi baru.</p>'
            . '<p style="margin:24px 0"><a href="' . $safeLink . '" style="background:#0E2C6A;color:#ffffff;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:bold">Ganti Kata Sandi</a></p>'
            . '<p>Tautan berlaku selama ' . $minutes . ' menit dan hanya dapat dipakai satu kali.</p>'
            . '<p>Jika Anda tidak meminta ini, abaikan email ini. Kata sandi Anda tidak akan berubah.</p>'
            . '<p style="font-size:12px;color:#6B7280">Jika tombol tidak berfungsi, salin tautan ini ke peramban:<br>' . $safeLink . '</p>'
            . '</div>';

        $text = "Halo {$name},\n\n"
            . "Kami menerima permintaan untuk mengganti kata sandi akun RIMORA Anda.\n"
            . "Buka tautan berikut untuk membuat kata sandi baru (berlaku {$minutes} menit, sekali pakai):\n\n"
            . $link . "\n\n"
            . "Jika Anda tidak meminta ini, abaikan email ini.";

        return [$html, $text];
    }
}
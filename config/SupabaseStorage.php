<?php
class SupabaseStorage
{
    private $url;
    private $key;
    private $bucket;

    public function __construct()
    {
        $c = require __DIR__ . '/supabase.php';
        $this->url    = rtrim($c['url'], '/');
        $this->key    = $c['key'];
        $this->bucket = $c['bucket'];
    }

    private function headers(array $extra = [])
    {
        $h = ['apikey: ' . $this->key];
        // Key lama berformat JWT butuh Authorization; key baru (sb_secret_...) cukup apikey
        if (strpos($this->key, 'sb_') !== 0) {
            $h[] = 'Authorization: Bearer ' . $this->key;
        }
        return array_merge($h, $extra);
    }

    /** Upload file dan kembalikan URL publik, atau null kalau gagal. */
    public function upload($tmpPath, $objectName, $mime)
    {
        $ch = curl_init($this->url . '/storage/v1/object/' . $this->bucket . '/' . rawurlencode($objectName));
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => file_get_contents($tmpPath),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => $this->headers(['Content-Type: ' . $mime, 'x-upsert: false']),
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $code < 200 || $code >= 300) {
            error_log("Supabase upload gagal ($code): $err $body");
            return null;
        }
        return $this->publicUrl($objectName);
    }

    public function publicUrl($objectName)
    {
        return $this->url . '/storage/v1/object/public/' . $this->bucket . '/' . rawurlencode($objectName);
    }

    /** Hapus file berdasarkan URL publik yang tersimpan di database. */
    public function deleteByUrl($publicUrl)
    {
        $prefix = $this->url . '/storage/v1/object/public/' . $this->bucket . '/';
        if (!$publicUrl || strpos($publicUrl, $prefix) !== 0) {
            return; // bukan file milik bucket ini (misal path lokal lama)
        }
        $objectName = rawurldecode(substr($publicUrl, strlen($prefix)));

        $ch = curl_init($this->url . '/storage/v1/object/' . $this->bucket . '/' . rawurlencode($objectName));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $this->headers(),
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
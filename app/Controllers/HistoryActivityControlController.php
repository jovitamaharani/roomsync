<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../Models/Report.php";

/**
 * Jurnal Activity Control untuk admin: HANYA MELIHAT.
 * Dibuka dari sidebar: index.php?controller=history_activity_control&action=index
 *
 * Rentang tanggal bawaan 30 hari terakhir. Dapat diubah lewat URL:
 *   ...&from=2026-11-01&to=2026-11-30
 */
class HistoryActivityControlController
{
    private const DEFAULT_DAYS = 30;
    private const MAX_DAYS = 366;

    private $reportModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $db = (new Database())->getConnection();
        $this->reportModel = new Report($db);
    }

    // Parameter tanggal Y-m-d yang valid, jika tidak pakai nilai bawaan
    private function dateParam($key, $default)
    {
        $v = $_GET[$key] ?? '';
        $d = DateTime::createFromFormat('Y-m-d', $v);
        return ($d && $d->format('Y-m-d') === $v) ? $v : $default;
    }

    public function index()
    {
        $today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        $to = $this->dateParam('to', $today->format('Y-m-d'));
        $from = $this->dateParam('from', (clone $today)->modify('-' . (self::DEFAULT_DAYS - 1) . ' days')->format('Y-m-d'));

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        // Batasi rentang agar halaman tidak berat
        $earliest = (new DateTime($to))->modify('-' . (self::MAX_DAYS - 1) . ' days')->format('Y-m-d');
        if ($from < $earliest) {
            $from = $earliest;
        }

        $reports = [];
        $loadError = null;
        try {
            $reports = $this->reportModel->getAdminList($from, $to);
        } catch (Throwable $e) {
            error_log('[history_activity_control] ' . $e->getMessage());
            $loadError = 'Data jurnal tidak dapat dimuat saat ini.';
        }

        require_once __DIR__ . "/../Views/admin/history_activity_control/index.php";
    }
}
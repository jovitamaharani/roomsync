<?php
require_once "Config/Database.php";
require_once "app/Models/OccupancyLog.php";

date_default_timezone_set('Asia/Jakarta');

class OccupancyLogController
{
    private const ROW_LIMIT = 5000;
    private const DEFAULT_DAYS = 30;
    private const MAX_DAYS = 366;

    private $logModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $this->logModel = new OccupancyLog($database->getConnection());
    }

    private function validDate($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $date = DateTime::createFromFormat('Y-m-d', $value);
        return ($date && $date->format('Y-m-d') === $value) ? $value : null;
    }

    public function index()
    {
        $to = $this->validDate($_GET['to'] ?? null) ?? date('Y-m-d', strtotime('+30 days'));
        $from = $this->validDate($_GET['from'] ?? null) ?? date('Y-m-d', strtotime('-' . self::DEFAULT_DAYS . ' days'));

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $earliest = date('Y-m-d', strtotime($to . ' -' . self::MAX_DAYS . ' days'));
        if ($from < $earliest) {
            $from = $earliest;
        }

        $filters = ['from' => $from, 'to' => $to];
        $rowLimit = self::ROW_LIMIT;
        $logs = $this->logModel->getHistory($from, $to, null, $rowLimit);
        $roomNames = $this->logModel->getRoomNames();

        require_once "app/Views/admin/occupancy_log/index.php";
    }
}
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../../Config/Database.php';
require_once __DIR__ . '/../../Models/Room.php';
require_once __DIR__ . '/../../Models/Schedule.php';
require_once __DIR__ . '/../../Models/OccupancyLog.php';

$database = new Database();
$db = $database->getConnection();
$roomModel = new Room($db);
$scheduleModel = new Schedule($db);
$logModel = new OccupancyLog($db);

$view = $_GET['view'] ?? $_GET['action'] ?? 'rooms';
if ($view === 'index') {
    $view = 'rooms';
}
$basePath = '';

switch ($view) {
    case 'landing':
    case 'beranda':
        $stats = $roomModel->getStats();
        $previewRooms = $roomModel->getPreview(3);
        require_once __DIR__ . '/dashboard/index.php';
        break;

    case 'schedule':
    case 'jadwal':
        $bulan = (int) date('n');
        $semester = ($bulan >= 9 || $bulan <= 2) ? 'Ganjil' : 'Genap';
        $schedules = $scheduleModel->getToday($semester);
        $filterOptions = $scheduleModel->getFilterOptions();
        require_once __DIR__ . '/schedule/index.php';
        break;

    case 'riwayat':
    case 'history':
        $from = date('Y-m-d', strtotime('-30 days'));
        $to = date('Y-m-d', strtotime('+30 days'));

        $logs = $logModel->getHistory($from, $to, null);
        $rooms = $logModel->getRoomNames();

        require_once __DIR__ . '/riwayat/index.php';
        break;

    case 'room_detail':
    case 'room-detail':
        require_once __DIR__ . '/room_detail/index.php';
        break;

    case 'login':
        require_once __DIR__ . '/login/index.php';
        break;

    case 'activity':
    case 'activity_control':
        Auth::requireRole(['Admin', 'Mahasiswa']);
        require_once __DIR__ . '/activity_control/index.php';
        break;

    case 'profile':
        Auth::requireRole(['Admin', 'Mahasiswa']);
        require_once __DIR__ . '/profile/index.php';
        break;

    case 'laporan_bubar':
    case 'laporan-bubar':
        Auth::requireRole(['Admin', 'Mahasiswa']);
        require_once __DIR__ . '/laporan_bubar/index.php';
        break;

    case 'rooms':
    case 'dashboard':
    default:
        $keyword = trim($_GET['keyword'] ?? '');
        $rooms = $keyword !== '' ? $roomModel->search($keyword) : $roomModel->getAll();
        $stats = $roomModel->getStats();
        require_once __DIR__ . '/rooms/index.php';
        break;
}
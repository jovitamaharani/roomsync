<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../../Config/Database.php';
require_once __DIR__ . '/../../Models/Room.php';

$database = new Database();
$db = $database->getConnection();
$roomModel = new Room($db);

$view = $_GET['view'] ?? $_GET['action'] ?? 'rooms';
if ($view === 'index') {
    $view = 'rooms';
}
$basePath = '';

switch ($view) {
    case 'schedule':
    case 'jadwal':
        require_once __DIR__ . '/schedule/index.php';
        break;
    case 'riwayat':
    case 'history':
        require_once __DIR__ . '/riwayat/index.php';
        break;
    case 'activity':
    case 'activity_control':
        require_once __DIR__ . '/activity_control/index.php';
        break;
    case 'profile':
        require_once __DIR__ . '/profile/index.php';
        break;
    case 'login':
        require_once __DIR__ . '/login/index.php';
        break;
    case 'room_detail':
    case 'room-detail':
        require_once __DIR__ . '/room_detail/index.php';
        break;
    case 'laporan_bubar':
    case 'laporan-bubar':
        require_once __DIR__ . '/laporan_bubar/index.php';
        break;
    case 'landing':
    case 'beranda':
        $stats = $roomModel->getStats();
        $previewRooms = $roomModel->getPreview(3);
        require_once __DIR__ . '/dashboard/index.php';
        break;
    case 'rooms':
    case 'dashboard':
        $keyword = trim($_GET['keyword'] ?? '');
        $rooms = !empty($keyword) ? $roomModel->search($keyword) : $roomModel->getAll();
        $stats = $roomModel->getStats();
        require_once __DIR__ . '/rooms/index.php';
        break;
    default:
        require_once __DIR__ . '/rooms/index.php';
        break;
}

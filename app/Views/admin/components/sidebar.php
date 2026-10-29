<?php
$activeAdminPage = $activeAdminPage ?? 'overview';
$isRouted = isset($_GET['url']) || isset($_GET['route']) || isset($_GET['controller']);

// Fungsi pembantu URL agar kompatibel di mode router MVC maupun akses file langsung
if (!function_exists('adminNavUrl')) {
    function adminNavUrl($module)
    {
        if (isset($_GET['controller'])) {
            $map = [
                'role' => 'role',
                'lecturer' => 'lecturer',
                'academic_year' => 'academic_year',
                'years' => 'academic_year',
                'overview' => 'home'
            ];
            $ctrl = $map[$module] ?? $module;
            return "index.php?controller=" . $ctrl . "&action=index";
        }
        if (isset($_GET['url']) || isset($_GET['route'])) {
            return "index.php?url=admin/" . $module;
        }

        // Tentukan base path admin secara dinamis
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (preg_match('#^(.*?/admin)(/|$)#i', $scriptName, $m)) {
            $adminBase = rtrim($m[1], '/');
        } else {
            $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            $adminBase = ($dir === '' || $dir === '/') ? '/admin' : $dir . '/admin';
        }

        return $adminBase . '/' . $module . '/index.php';
    }
}

$userHomeUrl = '#';
if (isset($_GET['controller'])) {
    $userHomeUrl = 'index.php?controller=home';
} elseif (isset($_GET['url']) || isset($_GET['route'])) {
    $userHomeUrl = 'index.php?url=dashboard';
} elseif (file_exists('../../app/Views/landing.php') || file_exists('../app/Views/landing.php')) {
    $userHomeUrl = '../../index.php';
} elseif (file_exists('../../user/dashboard/dashboard.php')) {
    $userHomeUrl = '../../user/dashboard/dashboard.php';
} else {
    $userHomeUrl = '../../index.php';
}
?>

<aside class="sidebar" id="adminSidebar">

    <a class="brand" href="<?= adminNavUrl('overview') ?>">
        <span class="brand-mark">
            <i class="bi bi-buildings-fill"></i>
        </span>
        <span>
            <b>SIKARA</b>
            <small>Admin JTI</small>
        </span>
    </a>

    <div class="sidebar-label">Pengelolaan Akademik</div>

    <nav class="side-nav" id="adminMenu">
        <a href="<?= adminNavUrl('overview') ?>"
            class="side-link <?= $activeAdminPage === 'overview' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2"></i> Ringkasan
        </a>
        <a href="<?= adminNavUrl('lecturer') ?>"
            class="side-link <?= $activeAdminPage === 'lecturers' ? 'active' : '' ?>">
            <i class="bi bi-person-vcard"></i> Daftar Dosen
        </a>
        <a href="<?= adminNavUrl('student_class') ?>"
            class="side-link <?= $activeAdminPage === 'classes' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Daftar Kelas
        </a>
        <a href="<?= adminNavUrl('schedule') ?>"
            class="side-link <?= $activeAdminPage === 'schedules' ? 'active' : '' ?>">
            <i class="bi bi-calendar3"></i> Jadwal Mata Kuliah
        </a>
        <a href="<?= adminNavUrl('ocupacy_log') ?>"
            class="side-link <?= $activeAdminPage === 'bookings' ? 'active' : '' ?>">
            <i class="bi bi-journal-check"></i> Riwayat Peminjaman
        </a>
        <a href="<?= adminNavUrl('history_activity_control') ?>"
            class="side-link <?= $activeAdminPage === 'activity' ? 'active' : '' ?>">
            <i class="bi bi-clipboard2-data"></i> Jurnal Activity Control
        </a>
        <a href="<?= adminNavUrl('academic_year') ?>"
            class="side-link <?= $activeAdminPage === 'years' ? 'active' : '' ?>">
            <i class="bi bi-calendar-range"></i> Academic Year
        </a>
        <a href="<?= adminNavUrl('rooms') ?>" class="side-link <?= $activeAdminPage === 'rooms' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i> Daftar Ruang Kelas
        </a>
        <a href="<?= adminNavUrl('courses') ?>" class="side-link <?= $activeAdminPage === 'courses' ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Daftar Mata Kuliah
        </a>
        <a href="<?= adminNavUrl('users') ?>" class="side-link <?= $activeAdminPage === 'users' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i> Kelola Pengguna
        </a>
        <a href="<?= adminNavUrl('role') ?>" class="side-link <?= $activeAdminPage === 'roles' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock"></i> Kelola Role
        </a>
    </nav>

    <div class="sidebar-bottom">
        <a href="<?= $userHomeUrl ?>" class="btn btn-sm btn-outline-info w-100 mb-3 text-start"
            style="color: #b3d7ff !important; border-color: rgba(255,255,255,0.2);">
            <i class="bi bi-box-arrow-up-right me-2"></i>
            Tampilan User
        </a>

        <div class="admin-identity">
            <span class="avatar">A</span>
            <div class="admin-identity-info">
                <span class="name" id="adminName">Administrator JTI</span>
                <span class="role">Admin JTI</span>
            </div>
        </div>

        <a href="index.php?controller=auth&action=logout" class="btn btn-sm btn-outline-light w-100 mt-3" id="logoutBtn">
            <i class="bi bi-box-arrow-right me-1"></i> Keluar
        </a>
    </div>

</aside>

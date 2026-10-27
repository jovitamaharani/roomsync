<?php
$pageHeading = $pageHeading ?? 'Ringkasan';
$isRouted = isset($_GET['url']) || isset($_GET['route']) || (isset($_GET['controller']) && $_GET['controller'] === 'admin');
$userDashboardUrl = '#';
if (isset($_GET['controller'])) {
    $userDashboardUrl = 'index.php?controller=home';
} elseif (isset($_GET['url']) || isset($_GET['route'])) {
    $userDashboardUrl = 'index.php?url=dashboard';
} elseif (file_exists('../../app/Views/landing.php') || file_exists('../app/Views/landing.php')) {
    $userDashboardUrl = '../../index.php';
} elseif (file_exists('../../user/dashboard/dashboard.php')) {
    $userDashboardUrl = '../../user/dashboard/dashboard.php';
} else {
    $userDashboardUrl = '../../index.php';
}
?>
<header class="topbar">

    <button id="sidebarToggle" class="btn btn-light d-lg-none" aria-label="Buka menu" type="button">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div>
        <small class="text-secondary">
            Jurusan Teknologi Informasi - Politeknik Negeri Malang
        </small>
        <h1 id="pageTitle"><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <div class="d-flex align-items-center gap-3">
        <span class="today-label d-none d-md-inline-flex">
            <i class="bi bi-calendar-event me-2"></i>
            <span id="todayDate"></span>
        </span>

        <a href="<?= $userDashboardUrl ?>" class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard User</span>
        </a>
    </div>

</header>

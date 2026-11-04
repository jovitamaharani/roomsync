<?php
$activePage = $activePage ?? 'rooms';
$basePath = $basePath ?? '../';
$isRouter = isset($_GET['controller']) && $_GET['controller'] === 'user';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ambil user dari Auth jika tersedia, fallback ke session
$user = class_exists('Auth') ? Auth::user() : ($_SESSION['user'] ?? null);

$urlBeranda = $isRouter ? 'index.php?controller=user&view=landing' : $basePath . 'dashboard/index.php';
$urlRooms = $isRouter ? 'index.php?controller=user&view=rooms' : $basePath . 'rooms/index.php';
$urlSchedule = $isRouter ? 'index.php?controller=user&view=schedule' : $basePath . 'schedule/index.php';
$urlRiwayat = $isRouter ? 'index.php?controller=user&view=riwayat' : $basePath . 'riwayat/index.php';
$urlActivity = $isRouter ? 'index.php?controller=user&view=activity' : $basePath . 'activity_control/index.php';
$urlProfile = $isRouter ? 'index.php?controller=user&view=profile' : $basePath . 'profile/index.php';
$urlLogin = $isRouter ? 'index.php?controller=user&view=login' : $basePath . 'login/index.php';

$navActive = static fn(array $keys): string => in_array($activePage, $keys, true) ? 'nav-pill-active' : '';

$showActivity = !empty($user);

$userName = (string) ($user['name'] ?? 'Pengguna');
$userInitial = strtoupper(mb_substr($userName, 0, 1)) ?: 'A';
$userRole = ucwords(str_replace('_', ' ', (string) ($user['role'] ?? '')));
$csrfToken = ($user && class_exists('Auth') && method_exists('Auth', 'csrfToken')) ? Auth::csrfToken() : '';
?>
<!-- ================= TOP NAVIGATION BAR ================= -->
<header class="navbar" id="appNavbar">
  <div class="navbar-container">
    <a href="<?= htmlspecialchars($urlBeranda) ?>" class="brand-logo">
      <div class="brand-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M3 9.5L12 3L21 9.5V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9.5Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M9 15C9 13.3431 10.3431 12 12 12C13.6569 12 15 13.3431 15 15" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" />
        </svg>
      </div>
      <span class="brand-text">RIMORA</span>
    </a>

    <nav class="nav-links" aria-label="Navigasi utama">
      <a href="<?= htmlspecialchars($urlBeranda) ?>" class="nav-link <?= $navActive(['beranda', 'landing']) ?>"
        id="navBeranda">Beranda</a>

      <a href="<?= htmlspecialchars($urlRooms) ?>" class="nav-link <?= $navActive(['rooms', 'dashboard']) ?>"
        id="navRuangKelas">Ruang Kelas</a>

      <a href="<?= htmlspecialchars($urlSchedule) ?>" class="nav-link <?= $navActive(['schedule']) ?>"
        id="navJadwal">Jadwal Kelas</a>

      <a href="<?= htmlspecialchars($urlRiwayat) ?>" class="nav-link <?= $navActive(['riwayat']) ?>"
        id="navRiwayat">Riwayat</a>

      <?php if ($showActivity): ?>
        <a href="<?= htmlspecialchars($urlActivity) ?>"
          class="nav-link nav-activity-control <?= $navActive(['activity', 'activity_control']) ?>"
          id="navActivityControl">Activity Control</a>
      <?php endif; ?>
    </nav>

    <div class="nav-actions">
      <?php if ($user): ?>
        <!-- Avatar bulat; dropdown muncul saat diklik (toggle ditangani app.js) -->
        <div id="authNavContainer">
          <div class="profile-dropdown-wrapper" id="profileDropdownWrapper">
            <button type="button" class="profile-avatar-btn" id="userProfileBtn" aria-label="Menu Profil"
              aria-haspopup="true" aria-expanded="false" onclick="app.toggleProfileDropdown(event)">
              <span><?= htmlspecialchars($userInitial) ?></span>
            </button>
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="profile-dropdown-header">
                <div class="profile-header-avatar"><?= htmlspecialchars($userInitial) ?></div>
                <div class="profile-header-info">
                  <strong class="profile-header-name" id="profileDropdownName"><?= htmlspecialchars($userName) ?></strong>
                  <span class="profile-header-role"><?= htmlspecialchars($userRole) ?></span>
                </div>
              </div>
              <div class="profile-dropdown-divider"></div>
              <a href="<?= htmlspecialchars($urlProfile) ?>" class="profile-dropdown-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
              </a>
              <form action="index.php?controller=auth&action=logout" method="POST" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" id="logoutBtn" class="profile-dropdown-item text-danger">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                  </svg>
                  <span>Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= htmlspecialchars($urlLogin) ?>" class="btn-nav-login">Masuk</a>
      <?php endif; ?>
    </div>
  </div>
</header>
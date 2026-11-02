<?php
$activePage = $activePage ?? 'rooms';
$basePath = $basePath ?? '../';
$isRouter = isset($_GET['controller']) && $_GET['controller'] === 'user';

$urlBeranda  = $isRouter ? 'index.php?controller=user&view=landing'  : $basePath . 'dashboard/index.php';
$urlRooms    = $isRouter ? 'index.php?controller=user&view=rooms'    : $basePath . 'rooms/index.php';
$urlSchedule = $isRouter ? 'index.php?controller=user&view=schedule' : $basePath . 'schedule/index.php';
$urlRiwayat  = $isRouter ? 'index.php?controller=user&view=riwayat'  : $basePath . 'riwayat/index.php';
$urlActivity = $isRouter ? 'index.php?controller=user&view=activity' : $basePath . 'activity_control/index.php';
$urlProfile  = $isRouter ? 'index.php?controller=user&view=profile'  : $basePath . 'profile/index.php';
$urlLogin    = $isRouter ? 'index.php?controller=user&view=login'    : $basePath . 'login/index.php';
?>
  <!-- ================= TOP NAVIGATION BAR ================= -->
  <header class="navbar" id="appNavbar">
    <div class="navbar-container">
      <a href="<?= $urlBeranda ?>" class="brand-logo">
        <div class="brand-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 9.5L12 3L21 9.5V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 15C9 13.3431 10.3431 12 12 12C13.6569 12 15 13.3431 15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <span class="brand-text">SIKARA</span>
      </a>

      <nav class="nav-links">
        <a href="<?= $urlBeranda ?>" class="nav-link <?= ($activePage === 'beranda' || $activePage === 'landing') ? 'nav-pill-active' : '' ?>" id="navBeranda">
          Beranda
        </a>

        <a href="<?= $urlRooms ?>" class="nav-link <?= ($activePage === 'rooms' || $activePage === 'dashboard') ? 'nav-pill-active' : '' ?>" id="navRuangKelas">
          Ruang Kelas
        </a>

        <a href="<?= $urlSchedule ?>" class="nav-link <?= ($activePage === 'schedule') ? 'nav-pill-active' : '' ?>" id="navJadwal">
          Jadwal Kelas
        </a>
        <a href="<?= $urlRiwayat ?>" class="nav-link <?= ($activePage === 'riwayat') ? 'nav-pill-active' : '' ?>" id="navRiwayat">
          Riwayat
        </a>
        <a href="<?= $urlActivity ?>" class="nav-link nav-activity-control <?= ($activePage === 'activity') ? 'nav-pill-active' : '' ?>" id="navActivityControl">
          Activity Control
        </a>
      </nav>

      <div class="nav-actions">
        <!-- Auth Profile Dropdown (Avatar bulet saja, dropdown saat diklik) -->
        <div id="authNavContainer">
          <div class="profile-dropdown-wrapper" id="profileDropdownWrapper">
            <button type="button" class="profile-avatar-btn" id="userProfileBtn" aria-label="Menu Profil" aria-expanded="false" onclick="app.toggleProfileDropdown(event)">
              <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" alt="Foto Profil" class="profile-avatar-img">
            </button>
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
              <div class="profile-dropdown-header">
                <div class="profile-header-avatar">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" alt="Avatar">
                </div>
                <div class="profile-header-info">
                  <strong class="profile-header-name">Dafa Naufal Rabbani</strong>
                  <span class="profile-header-role">Ketua Kelas TI-2F</span>
                </div>
              </div>
              <div class="profile-dropdown-divider"></div>
              <a href="<?= $urlProfile ?>" class="profile-dropdown-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
              </a>
              <a href="<?= $urlLogin ?>" class="profile-dropdown-item text-danger" onclick="if (typeof app !== 'undefined') { app.logout(); }">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Logout</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

<?php
$basePath = $basePath ?? '../';
$isRouter = isset($_GET['controller']) && $_GET['controller'] === 'user';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user = $user ?? (class_exists('Auth') ? Auth::user() : ($_SESSION['user'] ?? null));

// Resolve JS path dynamically
$possibleJsPaths = [
  'assets/js/app.js',
  '../../assets/js/app.js',
  '../assets/js/app.js',
  $basePath . 'app.js',
  $basePath . 'assets/js/app.js',
  'app.js',
  '../app.js',
  '../../app.js',
  '../../../assets/js/app.js'
];
$appJsHref = 'assets/js/app.js';
foreach ($possibleJsPaths as $p) {
  if (file_exists($p)) {
    $appJsHref = $p . '?v=' . filemtime($p); // cache-busting saat app.js berubah
    break;
  }
}

$urlBeranda = $isRouter ? 'index.php?controller=user&view=landing' : $basePath . 'dashboard/index.php';
$urlRooms = $isRouter ? 'index.php?controller=user&view=rooms' : $basePath . 'rooms/index.php';
$urlSchedule = $isRouter ? 'index.php?controller=user&view=schedule' : $basePath . 'schedule/index.php';
$urlRiwayat = $isRouter ? 'index.php?controller=user&view=riwayat' : $basePath . 'riwayat/index.php';
$urlProfile = $isRouter ? 'index.php?controller=user&view=profile' : $basePath . 'profile/index.php';
$urlLogin = $isRouter ? 'index.php?controller=user&view=login' : $basePath . 'login/index.php';
$urlBubar = $isRouter ? 'index.php?controller=user&view=laporan_bubar' : $basePath . 'laporan_bubar/index.php';

// Flash message (dari $_SESSION['flash']) ditampilkan sebagai toast, lalu dihapus
$flash = null;
if (!empty($_SESSION['flash']['message'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
}
$flashType = in_array($flash['type'] ?? '', ['success', 'error', 'warning', 'info'], true) ? $flash['type'] : 'info';
$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
?>
<footer class="app-footer">
  <div class="footer-container">
    <div class="footer-main">
      <section class="footer-brand" aria-label="Tentang RIMORA">
        <a class="footer-brand-link" href="<?= htmlspecialchars($urlBeranda) ?>" aria-label="RIMORA - Halaman Utama">
          <span class="footer-brand-mark"><i class="bi bi-buildings-fill" aria-hidden="true"></i></span>
          <span><strong>RIMORA</strong><small>POLITEKNIK NEGERI MALANG</small></span>
        </a>
        <p>Sistem Informasi Ketersediaan<br>dan Alokasi Ruang</p>
        <a class="footer-email" href="mailto:polinema@polinema.ac.id"><i class="bi bi-envelope-fill"
            aria-hidden="true"></i> polinema@polinema.ac.id</a>
      </section>
      <nav class="footer-links" aria-label="Tautan RIMORA">
        <h2>Tautan RIMORA</h2>
        <a href="<?= htmlspecialchars($urlBeranda) ?>">Beranda</a>
        <a href="<?= htmlspecialchars($urlRooms) ?>">Daftar ruangan</a>
        <a href="<?= htmlspecialchars($urlSchedule) ?>">Jadwal kelas</a>
        <a href="<?= htmlspecialchars($urlRiwayat) ?>">Riwayat</a>
        <?php if ($user): ?>
          <a href="<?= htmlspecialchars($urlBubar) ?>">Laporan pembubaran</a>
          <a href="<?= htmlspecialchars($urlProfile) ?>">Profil saya</a>
        <?php else: ?>
          <a href="<?= htmlspecialchars($urlLogin) ?>">Masuk</a>
        <?php endif; ?>
      </nav>
      <section class="footer-social">
        <h2>Ikuti POLINEMA</h2>
        <a href="https://www.instagram.com/polinema_campus/" target="_blank" rel="noopener noreferrer"><i
            class="bi bi-instagram" aria-hidden="true"></i> Instagram</a>
        <a href="https://www.facebook.com/polinema" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"
            aria-hidden="true"></i> Facebook</a>
        <a href="https://www.youtube.com/@polinema" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"
            aria-hidden="true"></i> YouTube</a>
        <a href="https://www.polinema.ac.id/" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe2"
            aria-hidden="true"></i> Situs resmi</a>
      </section>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> RIMORA &bull; PBL Kelompok 4, JTI POLINEMA</span>
      <span>Teknologi Web Modern &bull; Desain &amp; Pemrograman Web 2F</span>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= htmlspecialchars($appJsHref) ?>"></script>
<?php if ($flash): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.app) {
        app.showToast(<?= json_encode((string) $flash['message'], $jsonFlags) ?>, <?= json_encode($flashType) ?>);
      }
    });
  </script>
<?php endif; ?>
</body>

</html>
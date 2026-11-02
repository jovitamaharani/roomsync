<?php
$basePath = $basePath ?? '../';

// Resolve JS path dynamically
$possibleJsPaths = [
    $basePath . 'app.js',
    $basePath . 'assets/js/app.js',
    'app.js',
    '../app.js',
    '../../app.js',
    'assets/js/app.js',
    '../assets/js/app.js',
    '../../assets/js/app.js',
    '../../../assets/js/app.js'
];
$appJsHref = $basePath . 'app.js';
foreach ($possibleJsPaths as $p) {
    if (file_exists($p)) {
        $appJsHref = $p;
        break;
    }
}
?>
  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-main">
        <section class="footer-brand" aria-label="Tentang SIKARA">
          <a class="footer-brand-link" href="<?= $basePath ?>dashboard/index.php" aria-label="SIKARA - Halaman Utama">
            <span class="footer-brand-mark"><i class="bi bi-buildings-fill" aria-hidden="true"></i></span>
            <span><strong>SIKARA</strong><small>POLITEKNIK NEGERI MALANG</small></span>
          </a>
          <p>Sistem Informasi Ketersediaan<br>dan Alokasi Ruang</p>
          <a class="footer-email" href="mailto:polinema@polinema.ac.id"><i class="bi bi-envelope-fill" aria-hidden="true"></i> polinema@polinema.ac.id</a>
        </section>
        <nav class="footer-links" aria-label="Tautan SIKARA">
          <h2>Tautan SIKARA</h2>
          <a href="<?= $basePath ?>dashboard/index.php">Beranda</a>
          <a href="<?= $basePath ?>rooms/index.php">Daftar ruangan</a>
          <a href="<?= $basePath ?>login/index.php">Masuk</a>
          <a href="<?= $basePath ?>laporan_bubar/index.php">Laporan pembubaran</a>
        </nav>
        <section class="footer-social">
          <h2>Ikuti POLINEMA</h2>
          <a href="https://www.instagram.com/polinema_campus/" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram" aria-hidden="true"></i> Instagram</a>
          <a href="https://www.facebook.com/polinema" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook" aria-hidden="true"></i> Facebook</a>
          <a href="https://www.youtube.com/@polinema" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube" aria-hidden="true"></i> YouTube</a>
          <a href="https://www.polinema.ac.id/" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe2" aria-hidden="true"></i> Situs resmi</a>
        </section>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2026 SIKARA &bull; PBL Kelompok RoomSync, JTI POLINEMA</span>
        <span>Teknologi Web Modern &bull; Desain &amp; Pemrograman Web 2F</span>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $appJsHref ?>"></script>
</body>
</html>

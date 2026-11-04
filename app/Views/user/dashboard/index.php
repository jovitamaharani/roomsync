<?php
$pageTitle = 'Beranda - RIMORA';
$activePage = 'beranda';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';


$stats = $stats ?? [];
$nTotal = (int) ($stats['total'] ?? 0);
$nKosong = (int) ($stats['kosong'] ?? 0);
$nTerpakai = (int) ($stats['terpakai'] ?? 0);
$nGedung = (int) ($stats['gedung'] ?? 1);
$pct = $nTotal > 0 ? (int) round($nTerpakai / $nTotal * 100) : 0;

// Donut: keliling lingkaran r=38 => 238.76
$donutCirc = 238.76;
$donutGreen = $nTotal > 0 ? round($nKosong / $nTotal * $donutCirc, 2) : 0;
$donutRed = $nTotal > 0 ? round($nTerpakai / $nTotal * $donutCirc, 2) : 0;

$urlRoomDetail = $isRouter
  ? 'index.php?controller=user&view=room_detail&id='
  : $basePath . 'room_detail/index.php?id=';
$urlBooking = 'index.php?controller=booking&action=index';
$urlBubarAction = $user ? $urlActivity : $urlLogin;
$urlPinjamAction = $user ? $urlBooking : $urlLogin;
?>

<main id="mainApp">
  <section id="viewLanding" class="page-view active">
    <div class="landing-page-wrapper">
      <div class="landing-page-canvas">

        <!-- 1. HERO SECTION (Rounded Big Card with Overlapping Mockups & Curved Arrow) -->
        <div class="hero-rounded-banner">
          <div class="hero-banner-inner">

            <!-- Left Column: Headline, Subtitle, CTA, and Curved Arrow -->
            <div class="hero-banner-left">
              <h1 class="hero-brand-headline">
                Pantau <span class="sparkle-star">✦</span><br>Ruang Kelas
              </h1>

              <p class="hero-banner-description">
                Pantau ketersediaan ruangan secara real-time, cek jadwal kuliah, dan kelola alokasi ruang kelas Jurusan
                Teknologi Informasi dengan akurat dan cepat bersama RIMORA!
              </p>

              <form class="landing-room-search" method="GET"
                action="<?= $isRouter ? 'index.php' : htmlspecialchars($urlRooms) ?>">
                <?php if ($isRouter): ?>
                  <input type="hidden" name="controller" value="user">
                  <input type="hidden" name="view" value="rooms">
                <?php endif; ?>

                <label class="sr-only" for="landingRoomSearch">Cari ruang kelas</label>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input id="landingRoomSearch" type="search" name="keyword" placeholder="Cari nama ruang (RT 01, Lab)..."
                  autocomplete="off" required>
                <button type="submit">Cari</button>
              </form>

              <div class="hero-banner-cta-group">
                <a class="btn-hero-dark-pill" href="<?= htmlspecialchars($urlRooms) ?>">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <path d="M3 9h18"></path>
                    <path d="M9 21V9"></path>
                  </svg>
                  <span>Jelajahi Ruang Kelas</span>
                </a>

                <a href="<?= htmlspecialchars($urlSchedule) ?>" class="hero-sub-link">
                  <span>Lihat Jadwal Kuliah</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <polyline points="19 12 12 19 5 12"></polyline>
                  </svg>
                </a>
              </div>

              <!-- Hand-drawn curved arrow doodle pointing to the mockup -->
              <div class="curved-arrow-doodle" aria-hidden="true">
                <svg width="140" height="90" viewBox="0 0 140 90" fill="none">
                  <path d="M 15 10 C 20 60, 60 75, 120 40" stroke="rgba(255, 255, 255, 0.75)" stroke-width="2.2"
                    stroke-linecap="round" stroke-dasharray="4 4" fill="none" />
                  <path d="M 110 32 L 122 40 L 114 52" stroke="rgba(255, 255, 255, 0.75)" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
              </div>
            </div>

            <!-- Right Column: Overlapping Mobile Phone Mockup Cards -->
            <div class="hero-banner-right">

              <!-- Back Dark Phone Mockup (Tilted Behind) -->
              <div class="mockup-phone back-phone">
                <div class="phone-screen-inner">
                  <div class="dark-phone-header">
                    <div class="phone-status-row">
                      <span class="phone-time"><?= date('H:i') ?></span>
                      <div class="phone-notch"></div>
                      <span class="phone-icons">&bull;&bull;&bull;</span>
                    </div>
                    <div class="phone-title-row">
                      <span class="phone-stock-name">Statistik Okupansi</span>
                      <span class="phone-stock-price"><?= $pct ?>% Terpakai</span>
                    </div>
                    <div class="phone-badge-gain"><?= $nTerpakai ?> dari <?= $nTotal ?> ruang terpakai &bull; WIB</div>
                  </div>

                  <!-- Glowing wave occupancy chart -->
                  <div class="dark-chart-container">
                    <svg class="dark-wave-chart" viewBox="0 0 200 80" preserveAspectRatio="none">
                      <defs>
                        <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="0%" stop-color="#10B981" stop-opacity="0.4" />
                          <stop offset="100%" stop-color="#10B981" stop-opacity="0.0" />
                        </linearGradient>
                      </defs>
                      <path d="M0,55 Q30,20 60,40 T120,25 T160,45 T200,15 L200,80 L0,80 Z" fill="url(#chartGrad)" />
                      <path d="M0,55 Q30,20 60,40 T120,25 T160,45 T200,15" fill="none" stroke="#10B981"
                        stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                    <div class="chart-time-tabs">
                      <span>1H</span>
                      <span class="active">1D</span>
                      <span>Day</span>
                      <span>Week</span>
                      <span>Month</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Front White Phone Mockup (Main Interactive View) -->
              <div class="mockup-phone front-phone">
                <div class="phone-screen-inner">
                  <div class="front-phone-header">
                    <div class="phone-status-row dark-text">
                      <span class="phone-time"><?= date('H:i') ?></span>
                      <div class="phone-notch dark"></div>
                      <span class="phone-icons">5G 100%</span>
                    </div>
                    <div class="front-phone-title">Ruang Kelas</div>

                    <!-- Mini Search Input -->
                    <a class="front-phone-search" href="<?= htmlspecialchars($urlRooms) ?>"
                      style="text-decoration:none;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                      </svg>
                      <span>Cari ruang (RT 01, Lab)...</span>
                    </a>

                    <!-- Mini Filter Pills -->
                    <div class="front-phone-chips">
                      <span class="chip-mini active">Semua</span>
                      <span class="chip-mini green">Kosong (<?= $nKosong ?>)</span>
                      <span class="chip-mini red">Terpakai (<?= $nTerpakai ?>)</span>
                    </div>
                  </div>

                  <!-- Mini Live Collection Card -->
                  <div class="front-phone-collection">
                    <div class="collection-inner">
                      <div class="col-text">
                        <span class="col-title">Okupansi Kampus</span>
                        <span class="col-sub"><?= $nTotal ?> Ruang Terdaftar JTI</span>
                      </div>
                      <span class="col-badge"><?= $pct ?>%</span>
                    </div>
                  </div>

                  <!-- Mini Room Items List -->
                  <div class="front-phone-rooms-list">
                    <?php foreach ($previewRooms as $r):
                      $isUsed = strtoupper($r['status'] ?? '') === 'TERPAKAI';
                      $tone = $isUsed ? 'red' : 'green';
                      $href = !empty($r['id']) ? $urlRoomDetail . urlencode((string) $r['id']) : $urlRooms;
                      ?>
                      <a class="mini-room-row" href="<?= htmlspecialchars($href) ?>"
                        style="text-decoration:none;color:inherit;">
                        <div class="mini-room-icon <?= $tone ?>">RT</div>
                        <div class="mini-room-info">
                          <strong><?= htmlspecialchars($r['name'] ?? '-') ?></strong>
                          <span>
                            <?php if ($isUsed): ?>
                              <?= htmlspecialchars(implode(' • ', array_filter([$r['subject'] ?? '', $r['time'] ?? '']))) ?>
                            <?php else: ?>
                              <?= (int) ($r['capacity'] ?? 0) ?> Kursi &bull; Siap Pakai
                            <?php endif; ?>
                          </span>
                        </div>
                        <span class="mini-status-badge <?= $tone ?>"><?= $isUsed ? 'Terpakai' : 'Kosong' ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>

                  <!-- Dual Bottom Actions (Sell/Buy in ref -> Bubar/Pinjam in RIMORA) -->
                  <div class="front-phone-bottom-actions">
                    <a class="btn-mini-action red" href="<?= htmlspecialchars($urlBubarAction) ?>"
                      style="text-decoration:none;">🔴 Bubar</a>
                    <a class="btn-mini-action green" href="<?= htmlspecialchars($urlPinjamAction) ?>"
                      style="text-decoration:none;">🟢 Pinjam</a>
                  </div>

                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- 2. OUR PARTNERS / TERINTEGRASI DENGAN SECTION -->
        <div class="partners-section">
          <h3 class="partners-title">Didukung & Terintegrasi</h3>
          <p class="partners-subtitle">Sistem informasi monitoring ruang kelas khusus Jurusan Teknologi Informasi
            POLINEMA</p>

          <div class="partners-logo-row">
            <div class="partner-badge">
              <span class="partner-icon-circle">JTI</span>
              <span>POLINEMA</span>
            </div>
            <div class="partner-badge">
              <span class="partner-icon-circle">JTI</span>
              <span>Jurusan Teknologi Informasi</span>
            </div>
            <div class="partner-badge">
              <span class="partner-icon-circle">TI</span>
              <span>Manajemen Ruang Kelas JTI</span>
            </div>
            <div class="partner-badge">
              <span class="partner-icon-circle">POLI</span>
              <span>Politeknik Negeri Malang</span>
            </div>
          </div>
        </div>

        <!-- 3. FEATURE CARDS: "OPTIMALKAN PEMANFAATAN RUANG KAMPUS" (Get the Most Out of Your Investments) -->
        <div class="two-feature-cards-section">
          <h2 class="section-large-title">
            Optimalkan Pemanfaatan<br>Ruang Kelas JTI
          </h2>

          <div class="two-feature-grid">

            <!-- Card 1: Monitoring Visual Instan -->
            <div class="card-feature-wide">
              <div class="card-feature-content">
                <h3 class="card-feature-title">Monitoring Visual Instan</h3>
                <p class="card-feature-desc">
                  Pantau ketersediaan seluruh ruang kelas secara langsung tanpa harus berkeliling mengecek pintu fisik
                  satu per satu.
                </p>
                <a href="<?= htmlspecialchars($urlRooms) ?>" class="card-feature-link">
                  <span>Lihat Katalog Ruang</span>
                  <span class="link-arrow">&rarr;</span>
                </a>
              </div>

              <!-- Bottom Right Graphic matching Reference -->
              <div class="card-graphic-container">
                <div class="graphic-shape-bg yellow-tint"></div>
                <?php
                $sampleRoom = !empty($previewRooms[0]) ? $previewRooms[0] : null;
                $sampleIsUsed = $sampleRoom && strtoupper($sampleRoom['status'] ?? '') === 'TERPAKAI';
                ?>
                <div class="graphic-floating-badge">
                  <span class="badge-dot <?= $sampleIsUsed ? 'red' : 'green' ?>"></span>
                  <strong>
                    <?= htmlspecialchars($sampleRoom['name'] ?? 'RT 01') ?>
                    <?= $sampleIsUsed ? 'Terpakai' : 'Kosong' ?>
                  </strong>
                  <span>
                    <?= (int) ($sampleRoom['capacity'] ?? 35) ?> Kursi
                  </span>
                </div>
              </div>

            </div>

            <!-- Card 2: Override Real-time: Mahasiswa Bubar -->
            <div class="card-feature-wide">
              <div class="card-feature-content">
                <h3 class="card-feature-title">Override Real-time: Mahasiswa Bubar</h3>
                <p class="card-feature-desc">
                  Ketika perkuliahan selesai lebih awal, ketua kelas langsung melepaskan status ruangan agar dapat
                  segera dipakai kelas lain.
                </p>
                <a href="<?= htmlspecialchars($urlBubarAction) ?>" class="card-feature-link">
                  <span>Coba Fitur Bubar</span>
                  <span class="link-arrow">&rarr;</span>
                </a>
              </div>

              <!-- Bottom Right Donut Chart Graphic matching Reference -->
              <div class="card-graphic-container">
                <div class="donut-chart-graphic">
                  <svg width="110" height="110" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#E2E8F0" stroke-width="12" />
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#10B981" stroke-width="12"
                      stroke-dasharray="<?= $donutGreen ?> <?= $donutCirc ?>" stroke-linecap="round"
                      transform="rotate(-90 50 50)" />
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#EF4444" stroke-width="12"
                      stroke-dasharray="<?= $donutRed ?> <?= $donutCirc ?>" stroke-dashoffset="-<?= $donutGreen ?>"
                      stroke-linecap="round" transform="rotate(-90 50 50)" />
                  </svg>
                  <div class="donut-center-label">
                    <strong><?= $pct ?>%</strong>
                    <span>Terpakai</span>
                  </div>
                </div>
                <!-- Decorative wavy line -->
                <svg class="decorative-wave-line" width="70" height="35" viewBox="0 0 70 35" fill="none">
                  <path d="M5 25 Q 20 5, 35 25 T 65 15" stroke="#1E293B" stroke-width="2.5" stroke-linecap="round" />
                </svg>
              </div>
            </div>

          </div>
        </div>

        <!-- 4. ADVANTAGES SECTION (3 Columns Layout) -->
        <div class="advantages-section">
          <div class="advantages-grid">

            <!-- Column 1: Main Title & Text -->
            <div class="advantage-col main-title-col">
              <h3 class="advantage-main-title">Keunggulan</h3>
              <p class="advantage-main-desc">
                Sistem dirancang khusus untuk membantu warga Jurusan Teknologi Informasi menemukan dan mengelola ruang
                kelas dengan lebih mudah.
              </p>
            </div>

            <!-- Column 2: Smooth Start / Sinkronisasi Cepat -->
            <div class="advantage-col">
              <div class="advantage-icon-circle yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0E2C6A" stroke-width="2.2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <h4 class="advantage-card-title">Status Ruang Selalu Terbaru (WIB)</h4>
              <p class="advantage-card-desc">
                Status ruangan yang diperbarui ketua kelas langsung tersimpan di sistem dan terlihat oleh seluruh
                pengguna setiap kali halaman dimuat.
              </p>
            </div>

            <!-- Column 3: 24/7 Support / Validasi Bebas Bentrok -->
            <div class="advantage-col">
              <div class="advantage-icon-circle yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0E2C6A" stroke-width="2.2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                  <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
              </div>
              <h4 class="advantage-card-title">Validasi Bebas Bentrok</h4>
              <p class="advantage-card-desc">
                Sistem secara otomatis mengecek ketersediaan jadwal reguler untuk mencegah bentrok jadwal saat mahasiswa
                mengajukan peminjaman ruang kelas.
              </p>
            </div>

          </div>
        </div>

        <!-- 5. MID-PAGE PULSE SECTION (Keep Your Finger on the Pulse) -->
        <div class="pulse-banner-section">
          <div class="pulse-banner-inner">
            <div class="pulse-banner-left">
              <h3 class="pulse-banner-title">Pantau Okupansi & Utilisasi Kampus Setiap Saat</h3>
              <a class="btn-pulse-action" href="<?= htmlspecialchars($urlRooms) ?>" style="text-decoration:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                  <path d="M3 9h18"></path>
                  <path d="M9 21V9"></path>
                </svg>
                <span>Buka Dashboard Monitoring</span>
              </a>
            </div>

            <div class="pulse-banner-right">
              <div class="floating-pulse-card">
                <div class="pulse-card-header">
                  <span class="pulse-card-label">Kapasitas Hari Ini</span>
                  <strong class="pulse-card-number"><?= $nTotal ?> Ruangan</strong>
                </div>
                <div class="pulse-card-breakdown">
                  <div class="breakdown-item">
                    <span class="breakdown-dot green"></span>
                    <span><?= $nKosong ?> Ruang Kosong (Siap Pakai)</span>
                  </div>
                  <div class="breakdown-item">
                    <span class="breakdown-dot red"></span>
                    <span><?= $nTerpakai ?> Ruang Terpakai Perkuliahan</span>
                  </div>
                  <div class="breakdown-item">
                    <span class="breakdown-dot blue"></span>
                    <span><?= $nGedung ?> Gedung Terhubung Sistem</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. REAL-TIME SECTION (Trade in Real Time -> Pengecekan Real-Time) -->
        <div class="realtime-split-section">
          <div class="realtime-left-chart">
            <div class="mini-live-chart-card">
              <div class="chart-badge-tag">
                <span class="badge-dot green"></span>
                <span>LIVE TRAFFIC RUANG</span>
              </div>
              <div class="chart-time-val">08:00 - 16:30 WIB</div>
              <svg class="realtime-chart-svg" viewBox="0 0 220 90">
                <path d="M0,70 Q 40,25 80,50 T 150,30 T 220,15" fill="none" stroke="#10B981" stroke-width="3"
                  stroke-linecap="round" />
                <circle cx="220" cy="15" r="5" fill="#10B981" />
              </svg>
            </div>
          </div>

          <div class="realtime-right-text">
            <h3 class="realtime-heading">Pengecekan Real-Time Tanpa Keliling Pintu</h3>
            <p class="realtime-paragraph">
              Tidak ada lagi waktu terbuang untuk berjalan dari lantai ke lantai hanya demi mencari ruang kelas yang
              kosong. RIMORA menyajikan data akurat yang diperbarui oleh ketua kelas dan dosen pengampu saat
              mahasiswa mulai bubar.
            </p>
          </div>
        </div>

        <!-- FAQ: Informasi penggunaan RIMORA -->
        <section class="landing-faq" aria-labelledby="landingFaqTitle">
          <div class="landing-faq-intro">
            <span class="landing-faq-kicker"><span aria-hidden="true">✦</span> Pusat bantuan RIMORA</span>
            <h2 id="landingFaqTitle">Punya pertanyaan?<br><span>Kami punya jawabannya.</span></h2>
            <p>Temukan jawaban seputar ketersediaan, jadwal, dan peminjaman ruang kelas.</p>
          </div>
          <div class="landing-faq-list">
            <details class="landing-faq-item">
              <summary>Bagaimana cara melihat ruang kelas yang tersedia?</summary>
              <p>Buka katalog Ruang Kelas untuk melihat status ruang. Gunakan pencarian atau filter untuk menemukan
                ruang berdasarkan nama dan kondisi.</p>
            </details>
            <details class="landing-faq-item">
              <summary>Bagaimana cara melihat jadwal perkuliahan?</summary>
              <p>Pilih menu Jadwal Kelas untuk melihat jadwal hari ini. Anda dapat mencari berdasarkan mata kuliah,
                dosen, kelas, atau ruang.</p>
            </details>
            <details class="landing-faq-item">
              <summary>Apakah saya perlu login untuk melihat detail ruang?</summary>
              <p>Tidak. Detail ruang dapat dilihat tanpa login. Login mahasiswa diperlukan untuk mengajukan peminjaman
                ruang.</p>
            </details>
            <details class="landing-faq-item">
              <summary>Bagaimana cara mengajukan peminjaman ruang?</summary>
              <p>Login sebagai mahasiswa, buka detail ruang yang tersedia, lalu pilih tombol peminjaman untuk mengisi
                pengajuan.</p>
            </details>
          </div>
        </section>

        <!-- 7. BOTTOM CTA (Get the App for Free and Start Now) -->
        <div class="bottom-cta-container">
          <div class="bottom-cta-box">
            <h2 class="bottom-cta-heading">Mulai Monitoring & Alokasikan Ruang Sekarang</h2>
            <p class="bottom-cta-subtext">Akses ketersediaan ruang kelas terbuka untuk seluruh Mahasiswa, Ketua Kelas,
              Dosen, dan Admin Jurusan.</p>

            <div class="bottom-cta-buttons">
              <a class="btn-bottom-primary" href="<?= htmlspecialchars($urlRooms) ?>" style="text-decoration:none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                  <path d="M3 9h18"></path>
                  <path d="M9 21V9"></path>
                </svg>
                <span>Buka Katalog Ruang RIMORA</span>
              </a>
              <?php if (!$user): ?>
                <a class="btn-bottom-secondary" href="<?= htmlspecialchars($urlLogin) ?>" style="text-decoration:none;">
                  <span>Login Ketua Kelas (Form Bubar)</span>
                  <span class="arrow-right">&rarr;</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
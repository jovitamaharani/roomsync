<?php
$pageTitle = 'Ruang Kelas - SIKARA';
$activePage = 'rooms';
$basePath = $basePath ?? '../';

// ---------- Data dari hub ----------
$rooms = $rooms ?? [];
$stats = $stats ?? ['total' => 0, 'kosong' => 0, 'terpakai' => 0];

// ---------- Jadwal mingguan untuk modal "Cek Jadwal" ----------
// Diambil lewat model Room. Sengaja tidak memakai model/controller/view Jadwal.
// Jika gagal, halaman ruangan tetap terbuka dan modal menampilkan peringatan.
$roomSchedules = [];
$scheduleError = null;
if (isset($roomModel) && method_exists($roomModel, 'getWeeklySchedules')) {
  try {
    $roomSchedules = $roomModel->getWeeklySchedules();
  } catch (Throwable $e) {
    error_log('[user/rooms] getWeeklySchedules gagal: ' . $e->getMessage());
    $scheduleError = 'Data jadwal tidak dapat dimuat saat ini.';
  }
} else {
  $scheduleError = 'Data jadwal belum tersedia';
}

// ---------- Pilihan lantai diambil dari data ----------
$floors = array_values(array_unique(array_map('intval', array_column($rooms, 'floor'))));
sort($floors);

// ---------- Fungsi bantu ----------
$esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$todayDow = (int) (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('N');   // 1 = Senin ... 7 = Minggu

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<style>
  .room-name a {
    color: inherit;
    text-decoration: none;
  }

  .room-name a:hover {
    text-decoration: underline;
  }
</style>

<main id="mainApp">
  <section id="viewDashboard" class="page-view active">
    <!-- HERO SECTION -->
    <div class="dashboard-hero">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">Ruang Kelas</h1>
        <p class="hero-subtitle">Pemantauan okupansi dan alokasi ruang kelas kampus secara langsung.</p>

        <!-- Pencarian -->
        <div class="hero-search-pill d-flex align-items-center bg-white rounded-pill px-4 py-2 mx-auto shadow-sm">
          <div class="search-input-group w-100 d-flex align-items-center">
            <i class="bi bi-search text-secondary fs-5 me-3 flex-shrink-0" aria-hidden="true"></i>
            <input type="search" id="quickSearchInput"
              class="form-control border-0 shadow-none bg-transparent p-0 fs-6 text-dark"
              placeholder="Cari berdasarkan nama ruang, gedung, atau fasilitas..."
              oninput="app.handleSearchInput(this.value)" autocomplete="off">
            <button type="button"
              class="btn btn-sm btn-light rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 ms-2 btn-search-clear"
              id="btnClearSearch" onclick="app.clearSearch()" style="display: none; width: 28px; height: 28px;"
              aria-label="Hapus pencarian">&times;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN CONTAINER: STATISTIK, FILTER, DAFTAR RUANG -->
    <div class="dashboard-main-container">

      <!-- Statistik -->
      <div class="stats-grid">
        <div class="stat-card stat-total">
          <div class="stat-icon-wrapper blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <rect x="4" y="3" width="16" height="18" rx="2"></rect>
              <path d="M9 3v18"></path>
              <circle cx="15" cy="12" r="1.5" fill="currentColor"></circle>
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">TOTAL RUANGAN</span>
            <div class="stat-number" id="statTotalRooms"><?= (int) ($stats['total'] ?? 0) ?></div>
            <span class="stat-sub">Terdaftar di Sistem</span>
          </div>
        </div>

        <div class="stat-card stat-terpakai">
          <div class="stat-icon-wrapper red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">TERPAKAI</span>
            <div class="stat-number text-red" id="statInUseRooms"><?= (int) ($stats['terpakai'] ?? 0) ?></div>
            <span class="stat-sub">Perkuliahan Aktif</span>
          </div>
        </div>

        <div class="stat-card stat-kosong">
          <div class="stat-icon-wrapper green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="8 12 11 15 16 9"></polyline>
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">KOSONG</span>
            <div class="stat-number text-green" id="statAvailableRooms"><?= (int) ($stats['kosong'] ?? 0) ?></div>
            <span class="stat-sub">Siap Digunakan</span>
          </div>
        </div>

        <div class="stat-card stat-clock">
          <div class="stat-icon-wrapper purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div class="stat-content">
            <span class="stat-label">WAKTU TERKINI (WIB)</span>
            <div class="stat-number time-display" id="currentTimeClock">--:--:--</div>
            <span class="stat-sub" id="currentDateDisplay"></span>
          </div>
        </div>
      </div>

      <!-- Filter -->
      <div class="filter-controls-panel">
        <div class="filter-header">
          <div class="filter-title-group">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <h3>Filter dan Penyaringan Ruang</h3>
          </div>
        </div>

        <div class="filter-inputs-grid">
          <div class="filter-group">
            <label for="filterStatus">Status Ruang</label>
            <select id="filterStatus" class="form-select" onchange="app.applyFilters()">
              <option value="ALL">Semua Status</option>
              <option value="KOSONG">Kosong</option>
              <option value="TERPAKAI">Terpakai</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="filterLantai">Lantai</label>
            <select id="filterLantai" class="form-select" onchange="app.applyFilters()">
              <option value="ALL">Semua Lantai</option>
              <?php foreach ($floors as $fl): ?>
                <option value="<?= $fl ?>">Lantai <?= $fl ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="filterKapasitas">Kapasitas Ruang</label>
            <select id="filterKapasitas" class="form-select" onchange="app.applyFilters()">
              <option value="ALL">Semua Kapasitas</option>
              <option value="LT30">&lt; 30 Mahasiswa</option>
              <option value="30-35">30 - 35 Mahasiswa</option>
              <option value="GT35">&gt; 35 Mahasiswa</option>
            </select>
          </div>
        </div>

        <!-- Tampilan cepat -->
        <div class="quick-status-pills">
          <span class="pills-label">Tampilan Cepat:</span>
          <button class="status-tab-btn active" data-filter="ALL"
            onclick="app.setQuickFilter('all', 'ALL', this)">Semua</button>
          <button class="status-tab-btn" data-filter="KOSONG"
            onclick="app.setQuickFilter('status', 'KOSONG', this)">Kosong</button>
          <button class="status-tab-btn" data-filter="TERPAKAI"
            onclick="app.setQuickFilter('status', 'TERPAKAI', this)">Terpakai</button>
          <?php foreach ($floors as $fl): ?>
            <button class="status-tab-btn" data-floor="<?= $fl ?>"
              onclick="app.setQuickFilter('floor', '<?= $fl ?>', this)">Lantai <?= $fl ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Daftar ruang -->
      <div class="rooms-section-header">
        <h2 class="section-title">Daftar Ruang Perkuliahan</h2>
        <span class="showing-count" id="showingCountText">
          Menampilkan <?= min(9, count($rooms)) ?> dari <?= count($rooms) ?> Ruangan
        </span>
      </div>

      <div class="room-cards-grid" id="roomCardsGrid">
        <?php foreach ($rooms as $r):
          $status = strtolower($r['status'] ?? 'kosong');
          $detailJson = htmlspecialchars(json_encode([
            'name' => $r['name'],
            'building' => $r['building'],
            'floor' => (int) $r['floor'],
            'capacity' => (int) $r['capacity'],
            'chairs' => isset($r['chairs']) ? (int) $r['chairs'] : null,
            'tables' => isset($r['tables']) ? (int) $r['tables'] : null,
            'facilities' => $r['facilities'] ?? '',
            'is_active' => (bool) ($r['is_active'] ?? true),
            'status' => $status,
            'image_path' => $r['image_path'] ?? null,
          ], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
          ?>
          <article class="room-card" data-room-id="<?= $esc($r['id']) ?>" data-room="<?= $detailJson ?>"
            data-name="<?= $esc(mb_strtolower($r['name'] . ' ' . $r['building'] . ' ' . ($r['facilities'] ?? ''))) ?>"
            data-floor="<?= (int) $r['floor'] ?>" data-capacity="<?= (int) $r['capacity'] ?>"
            data-status="<?= $esc(strtoupper($status)) ?>">
            <div class="card-image-wrap" data-bs-toggle="modal" data-bs-target="#modalRoomDetail">
              <?php if (!empty($r['image_path'])): ?>
                <img src="<?= $esc($r['image_path']) ?>" alt="<?= $esc($r['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                  <i class="bi bi-door-open fs-1"></i>
                </div>
              <?php endif; ?>
              <div class="card-top-bar">
                <span class="room-status-badge <?= $esc($status) ?>">
                  <span class="status-dot"></span><?= $esc(strtoupper($status)) ?>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div class="card-title-group">
                <h3 class="room-name">
                  <a href="#" data-bs-toggle="modal" data-bs-target="#modalRoomDetail"><?= $esc($r['name']) ?></a>
                </h3>
                <div class="room-location">
                  <?= $esc($r['building']) ?> &middot; Lantai <?= (int) $r['floor'] ?>
                </div>
              </div>
              <div class="card-occupancy-info <?= $esc($status) ?>">
                Kapasitas <?= (int) $r['capacity'] ?> orang

                <?php if ($status === 'terpakai'): ?>
                  <div class="mt-2 pt-2 border-top">
                    <?php if (!empty($r['current_course'])): ?>
                      <div class="fw-semibold"><?= $esc($r['current_course']) ?></div>
                      <div class="small"><?= $esc($r['current_lecturer']) ?></div>
                      <div class="small">
                        <?= $esc($r['current_class']) ?> ·
                        <?= $esc(substr($r['current_start'], 0, 5)) ?> - <?= $esc(substr($r['current_end'], 0, 5)) ?>
                      </div>
                    <?php elseif (!empty($r['current_purpose'])): ?>
                      <div class="small">Dipinjam: <?= $esc($r['current_purpose']) ?></div>
                    <?php else: ?>
                      <div class="small">Sedang digunakan</div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="card-actions-row">
                <a href="#" class="btn-card-detail" data-bs-toggle="modal" data-bs-target="#modalRoomDetail">
                  <span>Lihat Detail</span><span class="btn-card-arrow">&rsaquo;</span>
                </a>
                  <!-- Booking Ruangan -->
                <?php if ($status === 'kosong'): ?>
                  <button type="button" class="btn-card-action" data-action="borrow-room">
                    <span>Pinjam Ruangan</span><span class="btn-card-arrow">&rsaquo;</span>
                  </button>
                <?php else: ?>
                  <button type="button" class="btn-card-action" data-bs-toggle="modal" data-bs-target="#modalRoomSchedule">
                    <span>Cek Jadwal</span><span class="btn-card-arrow">&rsaquo;</span>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div id="roomPagination"></div>

      <!-- Tampilan jika tidak ada hasil -->
      <div id="noResultsState" class="empty-state-box" style="display: none;">
        <div class="empty-state-icon">
          <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            <line x1="8" y1="11" x2="14" y2="11"></line>
          </svg>
        </div>
        <h3>Ruangan Tidak Ditemukan</h3>
        <p>Tidak ada ruangan yang cocok dengan kriteria pencarian atau filter yang Anda terapkan.</p>
        <button class="btn-outline-primary" onclick="app.resetFilters()">Reset Semua Filter</button>
      </div>
    </div>
  </section>
</main>

<!-- MODAL DETAIL RUANG -->
<div class="modal fade" id="modalRoomDetail" tabindex="-1" aria-labelledby="modalRoomDetailTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRoomDetailTitle" data-field="name">Detail Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="rounded overflow-hidden border mb-3" style="height: 200px; background: #e8edf4;">
          <img data-field="image" alt="" class="w-100 h-100 d-none" style="object-fit: cover;">
          <div data-field="image-placeholder" class="h-100">
            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
              <i class="bi bi-door-open" style="font-size: 3rem;"></i>
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge text-bg-success" data-field="status">-</span>
          <span class="badge text-bg-secondary" data-field="active">-</span>
        </div>

        <div class="row g-3">
          <div class="col-6">
            <div class="small text-muted">Gedung</div>
            <div class="fw-semibold" data-field="building">-</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Lantai</div>
            <div class="fw-semibold" data-field="floor">-</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Kapasitas</div>
            <div class="fw-semibold" data-field="capacity">-</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Jumlah Kursi</div>
            <div class="fw-semibold" data-field="chairs">-</div>
          </div>
          <div class="col-6">
            <div class="small text-muted">Jumlah Meja</div>
            <div class="fw-semibold" data-field="tables">-</div>
          </div>
          <div class="col-12">
            <div class="small text-muted mb-1">Fasilitas</div>
            <div class="d-flex flex-wrap gap-2" data-field="facilities">-</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRoomSchedule">
          <i class="bi bi-calendar3 me-1"></i>Cek Jadwal Ruang Ini
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL JADWAL RUANG -->
<div class="modal fade" id="modalRoomSchedule" tabindex="-1" aria-labelledby="modalRoomScheduleTitle" aria-hidden="true"
  data-error="<?= $scheduleError ? '1' : '0' ?>" data-today="<?= (int) $todayDow ?>">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title" id="modalRoomScheduleTitle" data-field="sch-name">Jadwal Ruangan</h5>
          <div class="small text-muted" data-field="sch-location"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <?php if ($scheduleError): ?>
          <div class="alert alert-warning small"><?= $esc($scheduleError) ?></div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-1 mb-3" data-field="sch-tabs"></div>

        <div class="table-responsive" data-field="sch-table">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead>
              <tr>
                <th style="width: 120px;">Jam</th>
                <th>Mata Kuliah</th>
                <th>Dosen</th>
                <th>Kelas</th>
              </tr>
            </thead>
            <tbody data-field="sch-rows"></tbody>
          </table>
        </div>
        <div class="text-center text-muted py-4 d-none" data-field="sch-empty"></div>

        <div class="small text-muted mt-3">
          Jadwal mingguan tetap pada tahun ajaran aktif. Pembatalan atau perubahan pada tanggal tertentu tidak
          ditampilkan di sini.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Data jadwal semua ruangan (JSON_HEX_* mencegah isi data menutup tag script) -->
<script type="application/json" id="roomSchedulesData"><?= json_encode(
  empty($roomSchedules) ? new stdClass : $roomSchedules,
  JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
) ?></script>

<?php
// JavaScript halaman ini ada di assets/js/user/room.js. Dicari dengan pola yang sama seperti app.js di footer.
$roomsJsHref = null;
foreach (['assets/js/user/', '../../assets/js/user/', '../assets/js/user/', $basePath . 'assets/js/user/', '../../../assets/js/user/', '../../../../assets/js/user/'] as $dir) {
  if (file_exists($dir . 'room.js')) {
    $roomsJsHref = $dir . 'room.js?v=' . filemtime($dir . 'room.js');
    break;
  }
}
?>
<?php if ($roomsJsHref): ?>
  <script src="<?= htmlspecialchars($roomsJsHref) ?>"></script>
<?php else: ?>
  <script>console.error('room.js tidak ditemukan. Letakkan di assets/js/user/room.js');</script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
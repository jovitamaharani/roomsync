<?php
$pageTitle = 'Riwayat Okupansi - RIMORA';
$activePage = 'riwayat';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';

$e = static fn($v): string => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$formatDate = static fn($v): string => $v ? date('d/m/Y', strtotime((string) $v)) : '-';
$formatTime = static fn($v): string => $v ? date('H:i', strtotime((string) $v)) : '-';
$isTrue = static fn($v): bool => in_array($v, [true, 't', 'true', 1, '1'], true);

$logs = $logs ?? [];
$rooms = $rooms ?? [];

$sourceBadge = [
  'schedule' => ['class' => 'bg-primary-subtle text-primary', 'label' => 'Jadwal Kuliah'],
  'booking' => ['class' => 'bg-success-subtle text-success', 'label' => 'Peminjaman'],
  'bubar' => ['class' => 'bg-danger-subtle text-danger', 'label' => 'Bubar Kelas'],
  'other' => ['class' => 'bg-secondary-subtle text-secondary', 'label' => 'Lainnya']
];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<main id="mainApp">
  <section id="viewRiwayat" class="page-view active">
    <div class="subpage-header">
      <div class="subpage-header-container">
        <div class="subpage-title-group">
          <h1 class="hero-title">Riwayat & Log Okupansi Ruang<br>(Append-Only)</h1>
          <p class="hero-subtitle">Rekapitulasi riwayat pengisian laporan ketua kelas, eksekusi bubar kelas, peminjaman
            mahasiswa, dan audit utilisasi.</p><br>
        </div>
      </div>
    </div>

    <div class="subpage-container">
      <!-- Filter Panel -->
      <div class="filter-controls-panel log-filter-card">
        <div class="filter-header d-flex justify-content-between align-items-center">
          <div class="filter-title-group">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <h3 class="mb-0">Filter dan Penyaringan Jadwal</h3>
          </div>
          <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary">Reset</button>
        </div>
        <div class="filter-inputs-grid log-grid-3col">
          <div class="filter-group">
            <label for="logSearchKeyword">Cari Kata Kunci</label>
            <input type="text" id="logSearchKeyword" class="form-control"
              placeholder="Nama peminjam, dosen, ruang, kegiatan...">
          </div>

          <!-- Jenis Aktivitas -->
          <div class="filter-group">
            <label for="logFilterType">Jenis Aktivitas</label>
            <select id="logFilterType" class="form-select">
              <option value="ALL">Semua Jenis Riwayat</option>
              <option value="bubar">Bubar Kelas (Override)</option>
              <option value="schedule">Jadwal Kuliah</option>
              <option value="booking">Peminjaman Mahasiswa</option>
              <option value="other">Lainnya</option>
            </select>
          </div>

          <!-- Ruangan -->
          <div class="filter-group">
            <label for="logFilterRoom">Ruangan</label>
            <select id="logFilterRoom" class="form-select">
              <option value="ALL">Semua Ruangan</option>
              <?php foreach ($rooms as $room): ?>
                <option value="<?= $e($room) ?>"><?= $e($room) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabel Data -->
      <div class="table-card log-table-card">
        <div class="table-responsive">
          <table class="data-table" id="userOccupancyTable">
            <thead>
              <tr>
                <th>Waktu (WIB)</th>
                <th>Ruangan</th>
                <th>Jenis Transaksi</th>
                <th>Aktor / Pihak Terkait</th>
                <th>Detail Aktivitas / Penggunaan</th>
                <th>Status Akhir</th>
              </tr>
            </thead>
            <tbody id="auditLogTableBody">
              <?php if (empty($logs)): ?>
                <tr id="emptyInitialRow">
                  <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat penggunaan ruangan pada periode
                    ini.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($logs as $log):
                  $source = (string) ($log['source'] ?? 'other');
                  $badge = $sourceBadge[$source] ?? $sourceBadge['other'];
                  $ongoing = $isTrue($log['is_ongoing'] ?? false);
                  $detail = ($source === 'schedule')
                    ? implode(' • ', array_filter([$log['course'] ?? '', $log['lecturer'] ?? '']))
                    : ($log['purpose'] ?? '-');
                  $actorSub = $log['classroom'] ?? ($log['user_role'] ?? '');
                  ?>
                  <tr class="log-row" data-source="<?= $e($source) ?>" data-room="<?= $e($log['room'] ?? '') ?>">
                    <td>
                      <strong><?= $e($formatDate($log['usage_date'] ?? $log['start_time'])) ?></strong>
                      <small class="text-muted d-block"><?= $e($formatTime($log['start_time'])) ?> -
                        <?= $e($formatTime($log['end_time'])) ?></small>
                    </td>
                    <td>
                      <?= $e($log['room'] ?? '-') ?>
                      <small class="text-muted d-block"><?= $e($log['building'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                    </td>
                    <td>
                      <?= $e($log['user_name'] ?? '-') ?>
                      <?php if (!empty($actorSub)): ?>
                        <small class="text-muted d-block"><?= $e($actorSub) ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?= $e($detail !== '' ? $detail : '-') ?></td>
                    <td>
                      <span
                        class="badge <?= $ongoing ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary' ?>">
                        <?= $ongoing ? 'Berlangsung' : 'Selesai' ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination: Info & Tombol Navigasi -->
        <div class="log-pagination-wrap" id="logPagination">
          <div class="pagination-info" id="logPaginationInfo"></div>
          <nav class="room-pagination" id="roomPaginationNav"></nav>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="assets/js/user/riwayat.js"></script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
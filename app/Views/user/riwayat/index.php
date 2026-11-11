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
$statusBadge = [
  'Berlangsung' => ['class' => 'bg-warning-subtle text-warning', 'label' => 'Berlangsung'],
  'Selesai' => ['class' => 'bg-secondary-subtle text-secondary', 'label' => 'Selesai'],
  'Terkonfirmasi' => ['class' => 'bg-info-subtle text-info', 'label' => 'Terkonfirmasi'],
  'Dibatalkan' => ['class' => 'bg-danger-subtle text-danger', 'label' => 'Dibatalkan'],
  'Tidak Hadir' => ['class' => 'bg-dark-subtle text-dark', 'label' => 'Tidak Hadir']
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

          <div class="filter-group">
            <label for="logFilterType">Jenis Aktivitas</label>
            <select id="logFilterType" class="form-select">
              <option value="">Pilih Jenis Aktivitas</option>
              <option value="bubar">Bubar Kelas (Override)</option>
              <option value="schedule">Jadwal Kuliah</option>
              <option value="booking">Peminjaman Mahasiswa</option>
              <option value="other">Lainnya</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="logFilterRoom">Ruangan</label>
            <select id="logFilterRoom" class="form-select">
              <option value="">Pilih Ruangan</option>
              <?php foreach ($rooms as $room): ?>
                <option value="<?= $e($room) ?>"><?= $e($room) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabel Data -->
      <div class="table-card log-table-card p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle data-table w-100" id="userOccupancyTable">
            <thead>
              <tr>
                <th>Waktu (WIB)</th>
                <th>Ruangan</th>
                <th>Jenis Transaksi</th>
                <th>Aktor / Pihak Terkait</th>
                <th>Detail Aktivitas / Penggunaan</th>
                <th>Status Akhir</th>
                <th class="text-start" style="width: 160px;">Aksi</th>
              </tr>
            </thead>
            <tbody id="auditLogTableBody">
              <?php foreach ($logs as $log):
                $source = (string) ($log['source'] ?? 'other');
                $badge = $sourceBadge[$source] ?? $sourceBadge['other'];
                $ongoing = $isTrue($log['is_ongoing'] ?? false);
                $detail = ($source === 'schedule')
                  ? implode(' • ', array_filter([$log['course'] ?? '', $log['lecturer'] ?? '']))
                  : ($log['purpose'] ?? '-');
                $actorSub = $log['classroom'] ?? ($log['user_role'] ?? '');
                $finalStatus = $log['status_label'] ?? ($ongoing ? 'Berlangsung' : 'Selesai');
                $statusInfo = $statusBadge[$finalStatus] ?? ['class' => 'bg-secondary-subtle text-secondary', 'label' => $finalStatus];
                $isBookingConfirmed = ($source === 'booking' && $finalStatus === 'Terkonfirmasi');
                $rawTimestamp = strtotime((string) ($log['start_time'] ?? 'now'));
                $timeRange = $formatTime($log['start_time']) . ' - ' . $formatTime($log['end_time']);
                $dateFormatted = $formatDate($log['usage_date'] ?? $log['start_time']);
                ?>
                <tr>
                  <td data-order="<?= $rawTimestamp ?>">
                    <strong><?= $e($dateFormatted) ?></strong>
                    <small class="text-muted d-block"><?= $e($timeRange) ?></small>
                  </td>
                  <td>
                    <strong><?= $e($log['room'] ?? '-') ?></strong>
                    <small class="text-muted d-block"><?= $e($log['building'] ?? '') ?></small>
                  </td>
                  <td>
                    <span class="badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                  </td>
                  <td>
                    <strong><?= $e($log['user_name'] ?? '-') ?></strong>
                    <?php if (!empty($actorSub)): ?>
                      <small class="text-muted d-block"><?= $e($actorSub) ?></small>
                    <?php endif; ?>
                  </td>
                  <td><?= $e($detail !== '' ? $detail : '-') ?></td>
                  <td>
                    <span class="badge <?= $statusInfo['class'] ?>">
                      <?= $e($statusInfo['label']) ?>
                    </span>
                  </td>
                  <td class="text-start">
                    <?php if ($isBookingConfirmed):
                      $rawDate = (string) ($log['usage_date'] ?? $log['start_time'] ?? '');
                      $dateIso = $rawDate ? date('Y-m-d', strtotime($rawDate)) : '';
                      $startTime = !empty($log['start_time']) ? date('H:i', strtotime((string) $log['start_time'])) : '';
                      $endTime = !empty($log['end_time']) ? date('H:i', strtotime((string) $log['end_time'])) : '';
                      ?>
                      <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 btn-edit-booking"
                          data-bs-toggle="modal" data-bs-target="#bookingModal" data-id="<?= $e($log['id']) ?>"
                          data-room-id="<?= $e($log['room_id'] ?? '') ?>" data-room-name="<?= $e($log['room'] ?? '') ?>"
                          data-date="<?= $e($dateIso) ?>" data-start="<?= $e($startTime) ?>" data-end="<?= $e($endTime) ?>"
                          data-purpose="<?= $e($log['purpose'] ?? '') ?>">
                          <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" data-bs-toggle="modal"
                          data-bs-target="#deleteBookingModal" data-id="<?= $e($log['id']) ?>"
                          data-room-name="<?= $e($log['room'] ?? '') ?>" data-date="<?= $e($dateIso) ?>"
                          data-time="<?= $e($startTime . ' - ' . $endTime) ?>">
                          Batalkan
                        </button>
                      </div>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="assets/js/user/riwayat.js"></script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
<?php
$pageTitle = 'Jadwal Kelas - RIMORA';
$activePage = 'schedule';
$basePath = $basePath ?? '../';

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';

$schedules = $schedules ?? [];
$filterOptions = $filterOptions ?? [];
$dbError = $dbError ?? null;

$isTrue = static fn($v): bool => in_array($v, [true, 't', 'true', 1, '1'], true);

$computeStatus = static function (array $s) use ($isTrue): string {
  if (!empty($s['exception_type']))
    return 'Ada Pengecualian';
  if ($isTrue($s['is_reported'] ?? false))
    return 'Selesai';

  $now = date('H:i');
  $start = substr((string) ($s['start_time'] ?? '00:00'), 0, 5);
  $end = substr((string) ($s['end_time'] ?? '23:59'), 0, 5);

  if ($now < $start)
    return 'Belum Dimulai';
  if ($now > $end)
    return 'Selesai';
  return 'Sedang Berlangsung';
};

$statusClass = [
  'Belum Dimulai' => 'is-belum',
  'Sedang Berlangsung' => 'is-sedang',
  'Selesai' => 'is-selesai',
  'Ada Pengecualian' => 'is-ubah',
];

$fromSchedules = static function (string $key) use ($schedules): array {
  $v = array_values(array_unique(array_filter(array_column($schedules, $key))));
  natsort($v);
  return array_values($v);
};
$roomOptions = !empty($filterOptions['rooms']) ? $filterOptions['rooms'] : $fromSchedules('room');
$classOptions = !empty($filterOptions['classes']) ? $filterOptions['classes'] : $fromSchedules('classroom');

$normalize = static fn(string $v): string => strtolower(preg_replace('/[^a-z0-9]/i', '', $v));

$initialRoom = '';
$roomNotFound = false;
$roomParam = $_GET['room'] ?? '';
$roomParam = is_string($roomParam) ? trim($roomParam) : '';

if ($roomParam !== '') {
  foreach ($roomOptions as $opt) {
    if (strcasecmp((string) $opt, $roomParam) === 0) {
      $initialRoom = (string) $opt;
      break;
    }
  }
  if ($initialRoom === '' && $normalize($roomParam) !== '') {
    foreach ($roomOptions as $opt) {
      if ($normalize((string) $opt) === $normalize($roomParam)) {
        $initialRoom = (string) $opt;
        break;
      }
    }
  }
  $roomNotFound = $initialRoom === '';
}

$hariNama = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$namaHari = $hariNama[(int) date('N') - 1];

$emptyMsg = (int) date('N') >= 6
  ? 'Hari ini ' . $namaHari . ' (akhir pekan), tidak ada perkuliahan. Jadwal hanya tersedia Senin–Jumat.'
  : 'Tidak ada jadwal kuliah hari ini (' . $namaHari . ').';
?>

<main id="mainApp">
  <section id="viewSchedule" class="page-view active">
    <div class="schedule-page-header">
      <div class="schedule-page-inner">
        <h1 class="hero-title">Jadwal Kelas</h1>
        <p class="hero-subtitle">Jadwal perkuliahan hari ini untuk seluruh kelas dan ruangan.</p>
      </div>
    </div>

    <div class="subpage-container">

      <?php if ($dbError): ?>
        <div class="alert alert-warning" role="alert"><?= $e($dbError) ?></div>
      <?php endif; ?>

      <?php if ($roomNotFound): ?>
        <div class="alert alert-warning" role="alert">
          Ruang "<?= $e($roomParam) ?>" tidak ditemukan. Menampilkan semua ruang.
        </div>
      <?php endif; ?>

      <div class="filter-controls-panel schedule-filters">
        <div class="filter-header">
          <div class="filter-title-group">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <h3>Filter dan Penyaringan Jadwal</h3>
          </div>
          <button type="button" id="scheduleResetBtn" class="btn btn-sm btn-outline-secondary">Reset</button>
        </div>

        <div class="filter-inputs-grid schedule-grid-4col">
          <div class="filter-group">
            <label for="scheduleSearch">Cari Jadwal</label>
            <input id="scheduleSearch" class="form-control" type="search" autocomplete="off"
              placeholder="Mata kuliah, dosen, atau ruang...">
          </div>

          <div class="filter-group">
            <label for="scheduleRoomFilter">Ruang Kelas</label>
            <select id="scheduleRoomFilter" class="form-select js-searchable-select">
              <option value="">Semua Ruang</option>
              <?php foreach ($roomOptions as $room): ?>
                <option value="<?= $e($room) ?>" <?= (string) $room === $initialRoom ? 'selected' : '' ?>><?= $e($room) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="scheduleClassFilter">Kelas</label>
            <select id="scheduleClassFilter" class="form-select js-searchable-select">
              <option value="">Semua Kelas</option>
              <?php foreach ($classOptions as $kelas): ?>
                <option value="<?= $e($kelas) ?>"><?= $e($kelas) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="scheduleStatusFilter">Status</label>
            <select id="scheduleStatusFilter" class="form-select">
              <option value="">Semua Status</option>
              <option value="Belum Dimulai">Belum Dimulai</option>
              <option value="Sedang Berlangsung">Sedang Berlangsung</option>
              <option value="Selesai">Selesai</option>
              <option value="Ada Pengecualian">Ada Pengecualian</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-card schedule-table-card">
        <thead class="table-responsive">
          <table id="masterScheduleTable" class="data-table schedule-data-table table" style="width:100%">
            <thead>
              <tr>
                <th>Jam Kuliah</th>
                <th>Ruang</th>
                <th>Mata Kuliah</th>
                <th>Dosen Pengampu</th>
                <th>Kelas</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($schedules as $s):
                $status = $computeStatus($s);
                $badge = $statusClass[$status] ?? 'is-belum';
                $jam = substr((string) ($s['start_time'] ?? ''), 0, 5) . ' - ' . substr((string) ($s['end_time'] ?? ''), 0, 5);
                ?>
                <tr>
                  <td><strong><?= $e($jam) ?></strong></td>
                  <td><span class="room-code-badge"><?= $e($s['room'] ?? '-') ?></span></td>
                  <td><?= $e($s['course'] ?? '-') ?></td>
                  <td><?= $e($s['lecturer'] ?? '-') ?></td>
                  <td><?= $e($s['classroom'] ?? '-') ?></td>
                  <td>
                    <span class="schedule-status-badge <?= $badge ?>" <?php if (!empty($s['exception_type'])): ?>
                        title="<?= $e(ucfirst((string) $s['exception_type']) . (!empty($s['exception_reason']) ? ': ' . $s['exception_reason'] : '')) ?>"
                      <?php endif; ?>><?= $e($status) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </thead>
        <tbody id="masterScheduleTableBody"></tbody>
        </table>
      </div>
      <div class="schedule-pagination-wrap" id="schedulePagination">
        <nav class="room-pagination">
          <button type="button" class="pagination-button" disabled>Sebelumnya</button>
          <button type="button" class="pagination-button active">1</button>
          <button type="button" class="pagination-button">2</button>
          <button type="button" class="pagination-button">Berikutnya</button>
        </nav>
      </div>
    </div>
    </div>

    </div>
  </section>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var $ = window.jQuery;
    if (!$ || !$.fn.DataTable) return;

    var table = $('#masterScheduleTable').DataTable({
      dom: 't<"schedule-dt-footer"i>',
      paging: false,
      order: [[0, 'asc']],
      autoWidth: false,
      language: {
        emptyTable: <?= json_encode($emptyMsg, JSON_UNESCAPED_UNICODE) ?>,
        zeroRecords: 'Tidak ada jadwal yang cocok dengan filter.',
        info: 'Menampilkan _START_–_END_ dari _TOTAL_ jadwal',
        infoEmpty: 'Tidak ada jadwal',
        infoFiltered: '(disaring dari _MAX_ jadwal)'
      }
    });

    var el = {
      q: document.getElementById('scheduleSearch'),
      room: document.getElementById('scheduleRoomFilter'),
      kelas: document.getElementById('scheduleClassFilter'),
      status: document.getElementById('scheduleStatusFilter'),
      reset: document.getElementById('scheduleResetBtn')
    };

    function exact(value) {
      return value ? '^' + value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$' : '';
    }

    function applyFilters() {
      table.search(el.q.value);
      table.column(1).search(exact(el.room.value), true, false);
      table.column(4).search(exact(el.kelas.value), true, false);
      table.column(5).search(exact(el.status.value), true, false);
      table.draw();
    }

    el.q.addEventListener('input', applyFilters);
    el.kelas.addEventListener('change', applyFilters);
    el.room.addEventListener('change', applyFilters);
    el.status.addEventListener('change', applyFilters);

    document.querySelectorAll('select.js-searchable-select').forEach(function (sel) {
      if (!window.TomSelect) return;
      new TomSelect(sel, {
        placeholder: sel.options.length ? sel.options[0].text : '',
        maxOptions: null,
        plugins: { clear_button: { title: 'Hapus pilihan' } },
        onChange: applyFilters
      });
    });

    function clearSelect(sel) {
      if (sel.tomselect) { sel.tomselect.clear(true); } else { sel.value = ''; }
    }

    el.reset.addEventListener('click', function () {
      el.q.value = '';
      clearSelect(el.room); clearSelect(el.kelas); clearSelect(el.status);
      applyFilters();
    });

    if (el.room.value) { applyFilters(); }
  });
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
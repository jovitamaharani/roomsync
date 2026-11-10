<?php
$pageTitle = 'Jadwal Kelas - SIKARA';
$activePage = 'schedule';
$basePath = $basePath ?? '../';
$todaySchedules = $todaySchedules ?? [];

// Filter dari query string
$filters = [
  'q' => trim($_GET['q'] ?? ''),
  'room' => $_GET['room'] ?? 'ALL',
  'class' => trim($_GET['class'] ?? ''),
  'status' => $_GET['status'] ?? 'ALL',
];
if (!in_array($filters['status'], ['ALL', 'Belum', 'Sedang', 'Selesai'], true)) {
  $filters['status'] = 'ALL';
}

// Pilihan dropdown ruang, hanya ruang yang punya jadwal hari ini
$roomOptions = array_values(array_unique(array_column($todaySchedules, 'room')));
sort($roomOptions, SORT_NATURAL | SORT_FLAG_CASE);

$filteredSchedules = array_values(array_filter($todaySchedules, function ($r) use ($filters) {
  if ($filters['status'] !== 'ALL' && $r['status'] !== $filters['status'])
    return false;
  if ($filters['room'] !== 'ALL' && $r['room'] !== $filters['room'])
    return false;
  if ($filters['class'] !== '' && mb_stripos($r['classroom'], $filters['class']) === false)
    return false;
  if ($filters['q'] !== '') {
    $haystack = implode(' ', [$r['course'], $r['kode_mk'], $r['lecturer'], $r['room'], $r['classroom']]);
    if (mb_stripos($haystack, $filters['q']) === false)
      return false;
  }
  return true;
}));

// pagination 10 per halaman
$perPage = 10;
$totalRows = count($filteredSchedules);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
$pageRows = array_slice($filteredSchedules, ($page - 1) * $perPage, $perPage);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$hhmm = fn($t) => substr((string) $t, 0, 5);

// url halaman dg filter
$pageUrl = function (int $p) use ($filters) {
  $params = ['controller' => 'user', 'view' => 'schedule'];
  foreach (['q', 'class'] as $k) {
    if ($filters[$k] !== '')
      $params[$k] = $filters[$k];
  }
  if ($filters['room'] !== 'ALL')
    $params['room'] = $filters['room'];
  if ($filters['status'] !== 'ALL')
    $params['status'] = $filters['status'];
  if ($p > 1)
    $params['page'] = $p;
  return 'index.php?' . http_build_query($params);
};

$statusLabel = ['Belum' => 'Belum Dimulai', 'Sedang' => 'Sedang Berlangsung', 'Selesai' => 'Selesai'];
$statusTone = ['Belum' => 'green', 'Sedang' => 'red', 'Selesai' => ''];

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
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

      <div class="filter-controls-panel schedule-filters">
        <div class="filter-header">
          <div class="filter-title-group">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <h3>Filter dan Penyaringan Jadwal</h3>
          </div>
        </div>

        <form method="GET" action="index.php" id="scheduleFilterForm">
          <input type="hidden" name="controller" value="user">
          <input type="hidden" name="view" value="schedule">
          <div class="filter-inputs-grid schedule-grid-4col">
            <div class="filter-group">
              <label for="scheduleSearch">Cari Jadwal</label>
              <input id="scheduleSearch" class="form-control" type="text" name="q" value="<?= $h($filters['q']) ?>"
                placeholder="Mata Kuliah, dosen, atau ruang...">
            </div>
            <div class="filter-group">
              <label for="scheduleRoomFilter">Ruang Kelas</label>
              <select id="scheduleRoomFilter" class="form-select" name="room">
                <option value="ALL">Semua Ruang</option>
                <?php foreach ($roomOptions as $room): ?>
                  <option value="<?= $h($room) ?>" <?= $filters['room'] === $room ? 'selected' : '' ?>><?= $h($room) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="filter-group">
              <label for="scheduleClassFilter">Kelas</label>
              <input id="scheduleClassFilter" class="form-control" type="text" name="class"
                value="<?= $h($filters['class']) ?>" placeholder="Semua Kelas">
            </div>
            <div class="filter-group">
              <label for="scheduleStatusFilter">Status</label>
              <select id="scheduleStatusFilter" class="form-select" name="status">
                <option value="ALL" <?= $filters['status'] === 'ALL' ? 'selected' : '' ?>>Semua Status</option>
                <?php foreach ($statusLabel as $value => $label): ?>
                  <option value="<?= $value ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <noscript><button type="submit" class="btn btn-primary mt-2">Terapkan</button></noscript>
        </form>
      </div>

      <div class="table-card schedule-table-card">
        <div class="table-responsive">
          <table class="data-table schedule-data-table">
            <thead>
              <tr>
                <th>Jam Kuliah</th>
                <th>Ruang</th>
                <th>Mata Kuliah</th>
                <th>Dosen Pengampu</th>
                <th>Kelas</th>
                <th>Status Real-Time</th>
              </tr>
            </thead>
            <tbody id="masterScheduleTableBody">
              <?php if (empty($pageRows)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">
                    <?= empty($todaySchedules) ? 'Tidak ada jadwal kuliah hari ini.' : 'Tidak ada jadwal yang cocok dengan filter.' ?>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($pageRows as $row): ?>
                  <tr>
                    <td><strong><?= $h($hhmm($row['start_time'])) ?> - <?= $h($hhmm($row['end_time'])) ?></strong></td>
                    <td><?= $h($row['room']) ?><br><small class="text-muted"><?= $h($row['building']) ?></small></td>
                    <td><?= $h($row['course']) ?><br><small class="text-muted"><?= $h($row['kode_mk']) ?></small></td>
                    <td><?= $h($row['lecturer']) ?></td>
                    <td><?= $h($row['classroom']) ?></td>
                    <td>
                      <span class="mini-status-badge <?= $statusTone[$row['status']] ?>">
                        <?= $h($statusLabel[$row['status']]) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="schedule-pagination-wrap" id="schedulePagination">
            <nav class="room-pagination" aria-label="Halaman jadwal">
              <?php if ($page > 1): ?>
                <a class="pagination-button" href="<?= $h($pageUrl($page - 1)) ?>">&lsaquo; Sebelumnya</a>
              <?php else: ?>
                <button type="button" class="pagination-button" disabled>&lsaquo; Sebelumnya</button>
              <?php endif; ?>

              <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a class="pagination-button <?= $p === $page ? 'active' : '' ?>"
                  href="<?= $h($pageUrl($p)) ?>"><?= $p ?></a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a class="pagination-button" href="<?= $h($pageUrl($page + 1)) ?>">Berikutnya &rsaquo;</a>
              <?php else: ?>
                <button type="button" class="pagination-button" disabled>Berikutnya &rsaquo;</button>
              <?php endif; ?>
            </nav>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</main>

<!-- script auto refresh -->
 <script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('scheduleFilterForm');
  if (!form) return;

  const DELAY = 1500;
  let timer = null;

  function submitForm(fieldName) {
    clearTimeout(timer);
    sessionStorage.setItem('scheduleFocus', fieldName);   // untuk mengembalikan fokus setelah reload
    form.submit();
  }

  // kolom teks, kirim setelah pengguna berhenti mengetik
  form.querySelectorAll('input[type="text"]').forEach(function (input) {
    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () { submitForm(input.name); }, DELAY);
    });
  });

  // dropdown, langsung kirim saat pilihan berubah
  form.querySelectorAll('select').forEach(function (select) {
    select.addEventListener('change', function () { submitForm(select.name); });
  });

  // setelah refresh, fokus dikembalikan ke kolom teks yang tadi diisi
  const last = sessionStorage.getItem('scheduleFocus');
  if (last) {
    sessionStorage.removeItem('scheduleFocus');
    const el = form.elements[last];
    if (el && el.type === 'text') {
      el.focus();
      el.setSelectionRange(el.value.length, el.value.length);
    }
  }
});
</script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
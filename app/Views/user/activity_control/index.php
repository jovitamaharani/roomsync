<?php
$pageTitle = 'Activity Control - SIKARA';
$activePage = 'activity';
$basePath = $basePath ?? '../';

// Halaman ini hanya boleh dimuat lewat hub (membutuhkan $db)
if (!isset($db)) {
  http_response_code(404);
  exit('Buka halaman ini lewat router: index.php?controller=user&view=activity');
}

require_once __DIR__ . '/../../../Models/Report.php';

$esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$user = Auth::user() ?? [];

// ---------- Data form ----------
$sessions = [];
$activityTypes = [];
$userClass = null;
$loadError = null;
try {
  $reportModel = new Report($db);
  $sessions = $reportModel->getSessions($user, true);   // hanya yang belum dilaporkan
  $activityTypes = $reportModel->getActivityTypes();
  $userClass = $reportModel->getClassName($user['class_id'] ?? null);
} catch (Throwable $e) {
  error_log('[user/activity_control] ' . $e->getMessage());
  $loadError = 'Data sesi tidak dapat dimuat saat ini.';
}

$canReport = Report::canReport($user);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$old = $_SESSION['activity_old'] ?? [];
unset($_SESSION['activity_old']);

$dayNames = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
$today = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');

// Sesi terpilih: isian lama, jika tidak ada pilih yang sedang berlangsung, jika tidak yang pertama
$selectedKey = $old['session'] ?? '';
if ($selectedKey === '') {
  foreach ($sessions as $s) {
    if ($s['in_progress']) {
      $selectedKey = $s['key'];
      break;
    }
  }
}
if ($selectedKey === '' && $sessions) {
  $selectedKey = $sessions[0]['key'];
}

// Ruang unik dari semua sesi (select ruang hanya menampilkan ruang sesi terpilih)
$roomOptions = [];
foreach ($sessions as $s) {
  $roomOptions[$s['room_id']] = $s['room'] . ' (' . $s['building'] . ')';
}

$hasSessions = !empty($sessions);

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
  <section id="viewActivityControl" class="page-view active">
    <div class="subpage-header">
      <div class="subpage-header-container">
        <div class="subpage-title-group">
          <a href="index.php?controller=user&view=rooms" class="back-link">Kembali ke Ruang Kelas</a>
          <h1>Activity Control</h1>
          <p>Isi jurnal aktivitas berdasarkan jadwal kelas hari ini.</p>
        </div>
      </div>
    </div>
    <div class="subpage-container activity-page-container">
      <div class="activity-page-panel">

        <?php if ($flash): ?>
          <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> mb-3"
            role="alert">
            <?= $esc($flash['message'] ?? '') ?>
          </div>
        <?php endif; ?>
        <?php if ($loadError): ?>
          <div class="alert alert-warning mb-3" role="alert"><?= $esc($loadError) ?></div>
        <?php elseif (!$canReport): ?>
          <div class="alert alert-info mb-3" role="alert">
            Jurnal activity control hanya dapat diisi oleh mahasiswa yang terdaftar pada sebuah kelas.
          </div>
        <?php elseif (!$hasSessions): ?>
          <div class="alert alert-info mb-3" role="alert">
            Tidak ada sesi kuliah yang perlu dilaporkan saat ini.
          </div>
        <?php endif; ?>

        <div class="activity-form-heading">
          <h2 id="reportModalRoomTitle">Activity Control</h2>
        </div>
        <form id="activityReportForm" method="POST" action="index.php?controller=activity_control&action=store">
          <input type="hidden" name="csrf_token" value="<?= $esc(Auth::csrfToken()) ?>">
          <div class="activity-form-body">

            <!-- Special Notice: The Override Mechanism -->
            <div class="override-notice-box activity-status-callout">
              <div class="notice-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
              </div>
              <div class="notice-text">
                <strong>Kontrol Status Ruangan Real-time</strong>
                <p>Catat aktivitas perkuliahan di bawah ini. Status ruangan tetap <span
                    class="badge-tag red">Terpakai</span> sampai kelas selesai dan tombol <strong>Mahasiswa
                    Bubar</strong> dikonfirmasi.</p>
              </div>
            </div>

            <!-- Class and room info -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportClass">Kelas Mahasiswa</label>
                <input type="text" id="reportClass" class="form-control readonly-input"
                  value="<?= $esc($userClass ?? '-') ?>" readonly>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportRoomSelect">Ruang Kuliah</label>
                <select id="reportRoomSelect" class="form-select" disabled>
                  <?php if (!$hasSessions): ?>
                    <option value="">-</option>
                  <?php endif; ?>
                  <?php foreach ($roomOptions as $roomId => $roomLabel): ?>
                    <option value="<?= $esc($roomId) ?>"><?= $esc($roomLabel) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Sesi kuliah sesuai jadwal -->
            <div class="form-group">
              <label class="form-label" for="reportMataKuliah">Mata Kuliah</label>
              <select id="reportMataKuliah" name="session" class="form-select" <?= $hasSessions ? 'required' : 'disabled' ?>>
                <?php if (!$hasSessions): ?>
                  <option value="">Tidak ada sesi yang perlu dilaporkan</option>
                <?php endif; ?>
                <?php foreach ($sessions as $s):
                  $dt = new DateTime($s['date']);
                  $dateLabel = $s['date'] === $today ? 'Hari ini' : $dayNames[(int) $dt->format('N')] . ', ' . $dt->format('d/m');
                  $label = $s['title'] . ' (' . $s['lecturer'] . ') · ' . $dateLabel . ' ' . $s['start'] . '-' . $s['end'];
                  ?>
                  <option value="<?= $esc($s['key']) ?>" data-room-id="<?= $esc($s['room_id']) ?>"
                    data-room="<?= $esc($s['room']) ?>" data-class="<?= $esc($s['class']) ?>"
                    data-start="<?= $esc($s['start']) ?>" data-end="<?= $esc($s['end']) ?>"
                    data-in-progress="<?= $s['in_progress'] ? '1' : '0' ?>" <?= $selectedKey === $s['key'] ? 'selected' : '' ?>>
                    <?= $esc($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Time Inputs (Jam Masuk & Jam Keluar Dosen - KF-06) -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportJamMasuk">Jam Masuk Dosen (WIB)</label>
                <input type="time" id="reportJamMasuk" name="lecturer_enter" class="form-control"
                  value="<?= $esc($old['lecturer_enter'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportJamKeluar">Jam Keluar Dosen (WIB)</label>
                <input type="time" id="reportJamKeluar" name="lecturer_leave" class="form-control"
                  value="<?= $esc($old['lecturer_leave'] ?? '') ?>" required>
              </div>
            </div>

            <!-- Activity Type & Method (From SKPL Requirements) -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportJenisAktivitas">Jenis Aktivitas Kelas</label>
                <select id="reportJenisAktivitas" name="activity_type_id" class="form-select" required>
                  <?php foreach ($activityTypes as $t): ?>
                    <option value="<?= $esc($t['id']) ?>" <?= ($old['activity_type_id'] ?? '') === $t['id'] ? 'selected' : '' ?>><?= $esc($t['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportMetode">Metode Perkuliahan</label>
                <select id="reportMetode" name="mode" class="form-select" required>
                  <?php foreach (Report::MODES as $value => $label): ?>
                    <option value="<?= $esc($value) ?>" <?= ($old['mode'] ?? '') === $value ? 'selected' : '' ?>>
                      <?= $esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Attendance & Activity Details -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportKehadiran">Kehadiran Mahasiswa</label>
                <input type="number" id="reportKehadiran" name="student_attendance" class="form-control"
                  value="<?= $esc($old['student_attendance'] ?? '') ?>" min="0" max="500"
                  placeholder="Jumlah mahasiswa hadir" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportStatusDosen">Kehadiran Dosen</label>
                <select id="reportStatusDosen" name="lecturer_attendance" class="form-select" required>
                  <?php foreach (Report::LECTURER_STATUS as $value => $label): ?>
                    <option value="<?= $esc($value) ?>" <?= ($old['lecturer_attendance'] ?? '') === $value ? 'selected' : '' ?>><?= $esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="reportCatatanMateri">Materi Perkuliahan & Catatan Jurnal</label>
              <textarea id="reportCatatanMateri" name="description" class="form-control text-area" rows="3"
                maxlength="2000" required
                placeholder="Contoh: Implementasi State Management SPA dan Event Bubbling pada Frontend..."><?= $esc($old['description'] ?? '') ?></textarea>
            </div>

            <!-- PROMINENT MAHASISWA BUBAR TRIGGER SECTION (KF-07) -->
            <div class="bubar-action-card activity-bubar-callout">
              <div class="bubar-header">
                <div class="bubar-badge-icon">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                  </svg>
                </div>
                <div>
                  <h4 class="bubar-title">Konfirmasi kelas selesai</h4>
                  <p class="bubar-desc">Gunakan tombol ini setelah mahasiswa benar-benar meninggalkan ruangan. Status
                    ruang akan berubah dari <strong>Terpakai</strong> menjadi <strong>Tersedia</strong>, sehingga dapat
                    digunakan kelas lain.</p>
                </div>
              </div>

              <div class="bubar-button-wrapper">
                <button type="submit" class="btn-mahasiswa-bubar" id="btnMahasiswaBubar"
                  formaction="index.php?controller=activity_control&action=dismiss" formnovalidate>
                  <div class="btn-bubar-inner">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                      <polyline points="16 17 21 12 16 7"></polyline>
                      <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Konfirmasi Mahasiswa Bubar</span>
                  </div>
                </button>
              </div>
            </div>

          </div>

          <div class="activity-form-footer">
            <a href="index.php?controller=user&view=rooms"
              class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
              style="text-decoration: none;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
              </svg>
              Kembali
            </a>
            <button type="submit" id="btnSimpanJurnal"
              class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2"
              <?= $hasSessions ? '' : 'disabled' ?>>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Simpan Jurnal Laporan
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sessionSel = document.getElementById('reportMataKuliah');
    const roomSel = document.getElementById('reportRoomSelect');
    const classInput = document.getElementById('reportClass');
    const enterInput = document.getElementById('reportJamMasuk');
    const leaveInput = document.getElementById('reportJamKeluar');
    const bubarBtn = document.getElementById('btnMahasiswaBubar');
    const title = document.getElementById('reportModalRoomTitle');

    function apply(fromUserChange) {
      const opt = sessionSel.options[sessionSel.selectedIndex];
      const hasSession = !!(opt && opt.dataset.roomId);

      // Tombol Bubar hanya aktif untuk sesi yang sedang berlangsung
      bubarBtn.disabled = !(hasSession && opt.dataset.inProgress === '1');
      bubarBtn.title = bubarBtn.disabled
        ? 'Tombol aktif hanya saat sesi sedang berlangsung'
        : 'Akhiri pemakaian ruangan sekarang';

      if (!hasSession) {
        title.textContent = 'Activity Control';
        return;
      }

      title.textContent = 'Activity Control ' + opt.dataset.room;
      roomSel.value = opt.dataset.roomId;
      classInput.value = opt.dataset.class || '-';

      // Usulan jam dosen = jam jadwal. Dari pilihan baru: selalu diganti; saat awal: hanya jika kosong.
      if (fromUserChange || !enterInput.value) enterInput.value = opt.dataset.start;
      if (fromUserChange || !leaveInput.value) leaveInput.value = opt.dataset.end;
    }

    sessionSel.addEventListener('change', function () { apply(true); });

    // Konfirmasi sebelum bubar (tidak bisa dibatalkan)
    bubarBtn.addEventListener('click', function (e) {
      if (!confirm('Konfirmasi mahasiswa sudah meninggalkan ruangan?\nStatus ruangan akan langsung menjadi Kosong.')) {
        e.preventDefault();
      }
    });

    apply(false);
  });
</script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
<?php
$pageTitle = 'Activity Control - SIKARA';
$activePage = 'activity';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
  <section id="viewActivityControl" class="page-view active">
    <div class="subpage-header">
      <div class="subpage-header-container">
        <div class="subpage-title-group">
          <a href="#" class="back-link" onclick="app.navigate('dashboard'); return false;">? Kembali ke Ruang Kelas</a>
          <h1>Activity Control</h1>
          <p>Isi jurnal aktivitas berdasarkan jadwal kelas hari ini.</p>
        </div>
      </div>
    </div>
    <div class="subpage-container activity-page-container">
      <div class="activity-page-panel">
        <div class="activity-form-heading">
          <h2 id="reportModalRoomTitle">Activity Control Ã‚Â· RT 02</h2>
        </div>
        <form id="activityReportForm" onsubmit="app.handleActivityFormSubmit(event)">
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

            <!-- Pre-filled class and course info -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportClass">Kelas Mahasiswa</label>
                <input type="text" id="reportClass" class="form-control readonly-input" value="TI - 2F (Semester 4)"
                  readonly>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportRoomSelect">Ruang Kuliah</label>
                <select id="reportRoomSelect" class="form-select" onchange="app.handleReportRoomChange(this.value)">
                  <!-- Options filled via JS -->
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="reportMataKuliah">Mata Kuliah</label>
              <select id="reportMataKuliah" class="form-select">
                <option value="Desain dan Pemrograman Web">Desain dan Pemrograman Web (Dimas Wahyu Wibowo, S.T., M.T.)
                </option>
                <option value="Manajemen Proyek">Manajemen Proyek (Titis Octary Satrio, S.ST., M.MT.)</option>
                <option value="Basis Data Lanjut">Basis Data Lanjut (Mustika Mentari, S.Kom., M.Kom.)</option>
                <option value="Sistem Informasi Manajemen">Sistem Informasi Manajemen (Budi Harijanto, S.T., M.MKom.)
                </option>
              </select>
            </div>

            <!-- Time Inputs (Jam Masuk & Jam Keluar Dosen - KF-06) -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportJamMasuk">Jam Masuk Dosen (WIB)</label>
                <input type="time" id="reportJamMasuk" class="form-control" value="08:00" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportJamKeluar">Jam Keluar Dosen (WIB)</label>
                <input type="time" id="reportJamKeluar" class="form-control" value="09:40" required>
              </div>
            </div>

            <!-- Activity Type & Method (From SKPL Requirements) -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportJenisAktivitas">Jenis Aktivitas Kelas</label>
                <select id="reportJenisAktivitas" class="form-select">
                  <option value="Pembelajaran Teori / Ceramah">Pembelajaran Teori / Ceramah</option>
                  <option value="Praktikum Coding / Lab">Praktikum Coding / Lab</option>
                  <option value="Belajar Mandiri / Penugasan">Belajar Mandiri / Penugasan Kelompok</option>
                  <option value="Kuis / Ujian Tengah Semester">Kuis / Ujian Tengah Semester</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="reportMetode">Metode Perkuliahan</label>
                <select id="reportMetode" class="form-select">
                  <option value="Offline / Luring di Ruang">Offline / Luring di Ruang</option>
                  <option value="Hybrid (Zoom Bersama di Kelas)">Hybrid (Zoom Bersama di Kelas)</option>
                  <option value="Daring / Online">Daring / Online</option>
                </select>
              </div>
            </div>

            <!-- Attendance & Activity Details -->
            <div class="form-row-2col">
              <div class="form-group">
                <label class="form-label" for="reportKehadiran">Kehadiran Mahasiswa</label>
                <input type="number" id="reportKehadiran" class="form-control" value="28" min="1" max="40"
                  placeholder="Jumlah mahasiswa hadir">
              </div>
              <div class="form-group">
                <label class="form-label" for="reportStatusDosen">Kehadiran Dosen</label>
                <select id="reportStatusDosen" class="form-select">
                  <option value="Hadir Tepat Waktu">Hadir Tepat Waktu</option>
                  <option value="Hadir dengan Asisten">Hadir dengan Asisten</option>
                  <option value="Penugasan Mandiri (Dosen Izin)">Penugasan Mandiri (Dosen Izin)</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="reportCatatanMateri">Materi Perkuliahan & Catatan Jurnal</label>
              <textarea id="reportCatatanMateri" class="form-control text-area" rows="3"
                placeholder="Contoh: Implementasi State Management SPA dan Event Bubbling pada Frontend...">Pembahasan CSS Grid, Flexbox, dan implementasi UI/UX aplikasi SIKARA.</textarea>
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
                <button type="button" class="btn-mahasiswa-bubar" id="btnMahasiswaBubar"
                  onclick="app.executeMahasiswaBubar()">
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
            <button type="button"
              class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
              onclick="app.navigate('dashboard')">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
              </svg>
              Kembali
            </button>
            <button type="submit"
              class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
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

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
<?php
$pageTitle = 'Laporan Aktivitas & Bubar Kelas - SIKARA';
$activePage = 'activity';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
  <!-- ================= CONSISTENT PAGE BANNER ================= -->
  <div class="page-banner-card">
    <div class="page-banner-inner">
      <span class="room-code-pill">Formulir Ketua Kelas &bull; TI - 2F</span>
      <h1 class="page-banner-title">Laporan Aktivitas & Bubar Kelas</h1>
      <p class="page-banner-subtitle">Pelaporan kegiatan perkuliahan, kehadiran dosen, dan tombol pembaruan status ruangan kelas menjadi Kosong (Hijau).</p>
    </div>
  </div>

  <div class="page-wrapper">
    <div class="form-card-container">
      <div class="modal-header modal-header-navy">
        <div class="modal-title-wrap text-white">
          <span class="report-badge">Jurnal & Pelaporan Aktivitas (Ketua Kelas)</span>
          <h2 class="modal-title text-white">Laporan Perkuliahan Ruang RT 02</h2>
        </div>
        <a href="index.php" class="btn-close-modal text-white" style="text-decoration: none;">&times;</a>
      </div>

      <form id="standaloneReportForm" onsubmit="handleFormSubmit(event)">
        <div class="modal-body">
          <!-- System Override Warning Box -->
          <div class="override-notice-box">
            <div class="notice-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
            </div>
            <div class="notice-text">
              <strong>Mekanisme Override Real-time SIKARA</strong>
              <p>Menekan tombol konfirmasi <strong>"Mahasiswa Bubar"</strong> akan segera mengubah status ruangan dari <span class="badge-tag red">Terpakai</span> menjadi <span class="badge-tag green">Kosong (Hijau)</span> secara instan dan memperbarui log okupansi permanen.</p>
            </div>
          </div>

          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label" for="kelas">Kelas Terdaftar</label>
              <input type="text" id="kelas" class="form-control readonly-input" value="TI - 2F (Semester 4)" readonly>
            </div>
            <div class="form-group">
              <label class="form-label" for="ruang">Ruang Kuliah</label>
              <input type="text" id="ruang" class="form-control readonly-input" value="RT 02 (Gedung TI Lt 2)" readonly>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="mk">Mata Kuliah & Dosen Pengampu</label>
            <select id="mk" class="form-select">
              <option value="Desain dan Pemrograman Web">Desain dan Pemrograman Web (Dimas Wahyu Wibowo, S.T., M.T.)</option>
              <option value="Manajemen Proyek">Manajemen Proyek (Titis Octary Satrio, S.ST., M.MT.)</option>
              <option value="Basis Data Lanjut">Basis Data Lanjut (Mustika Mentari, S.Kom., M.Kom.)</option>
              <option value="Sistem Informasi Manajemen">Sistem Informasi Manajemen (Budi Harijanto, S.T., M.MKom.)</option>
            </select>
          </div>

          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label" for="masuk">Jam Masuk Dosen (WIB)</label>
              <input type="time" id="masuk" class="form-control" value="08:00" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="keluar">Jam Keluar Dosen (WIB)</label>
              <input type="time" id="keluar" class="form-control" value="09:40" required>
            </div>
          </div>

          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label" for="aktivitas">Jenis Aktivitas Kelas</label>
              <select id="aktivitas" class="form-select">
                <option value="Praktikum Coding">Praktikum Coding / Frontend UI</option>
                <option value="Ceramah Teori">Pembelajaran Teori / Ceramah</option>
                <option value="Belajar Mandiri">Belajar Mandiri / Penugasan Kelompok</option>
                <option value="Kuis">Kuis / Evaluasi Perkuliahan</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="metode">Metode Perkuliahan</label>
              <select id="metode" class="form-select">
                <option value="Offline">Offline / Luring di Kelas</option>
                <option value="Hybrid">Hybrid (Zoom Bersama di Kelas)</option>
                <option value="Daring">Daring / Online</option>
              </select>
            </div>
          </div>

          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label" for="kehadiran">Jumlah Mahasiswa Hadir</label>
              <input type="number" id="kehadiran" class="form-control" value="28" min="1" max="35">
            </div>
            <div class="form-group">
              <label class="form-label" for="statusDosen">Kehadiran Dosen</label>
              <select id="statusDosen" class="form-select">
                <option value="Hadir">Hadir Tepat Waktu</option>
                <option value="Asisten">Didampingi Asisten Lab</option>
                <option value="Izin">Izin / Penugasan Mandiri</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="materi">Catatan Jurnal / Topik Materi Perkuliahan</label>
            <textarea id="materi" class="form-control text-area" rows="3" placeholder="Deskripsi ringkas materi yang disampaikan hari ini...">Pembahasan implementasi HTML5, CSS3 Grid/Flexbox, dan JavaScript SPA untuk antarmuka sistem SIKARA.</textarea>
          </div>

          <!-- PROMINENT MAHASISWA BUBAR TRIGGER SECTION -->
          <div class="bubar-action-card">
            <div class="bubar-header">
              <div class="bubar-badge-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.3">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
              </div>
              <div>
                <h4 class="bubar-title">Konfirmasi Selesai & Mahasiswa Bubar</h4>
                <p class="bubar-desc">Klik tombol di bawah ini segera setelah perkuliahan selesai dan mahasiswa mulai keluar dari ruangan. Status ruangan akan langsung berubah menjadi HIJAU (KOSONG).</p>
              </div>
            </div>

            <div class="bubar-button-wrapper">
              <button type="button" class="btn-mahasiswa-bubar" onclick="triggerBubarNow()">
                <div class="btn-bubar-inner">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                  </svg>
                  <span>MAHASISWA BUBAR (RESET STATUS JADI HIJAU)</span>
                </div>
              </button>
            </div>
          </div>

        </div>

        <div class="modal-footer d-flex justify-content-between align-items-center pt-3 border-top">
          <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2" style="text-decoration: none;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Batal & Kembali
          </a>
          <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

</main>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>

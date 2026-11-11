<?php
$pageTitle = 'Riwayat Okupansi - SIKARA';
$activePage = 'riwayat';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
    <section id="viewRiwayat" class="page-view active">
      <div class="subpage-header">
        <div class="subpage-header-container">
          <div class="subpage-title-group">
            <h1 class="hero-title">Riwayat & Log Okupansi Ruang<br>(Append-Only)</h1>
            <p class="hero-subtitle">Rekapitulasi riwayat pengisian laporan ketua kelas, eksekusi bubar kelas, peminjaman mahasiswa, dan audit utilisasi.</p><br>
          </div>
        </div>
      </div>

      <div class="subpage-container">
        <!-- Log Filter Card (Overlapping Banner matching Image 3) -->
        <div class="filter-controls-panel log-filter-card">
          <div class="filter-header">
            <div class="filter-title-group">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
              </svg>
              <h3>Filter dan Penyaringan Jadwal</h3>
            </div>
          </div>
          <div class="filter-inputs-grid log-grid-3col">
            <div class="filter-group">
              <label for="logSearchKeyword">Cari Kata Kunci</label>
              <input type="text" id="logSearchKeyword" class="form-control" placeholder="Nama Peminjam, dosen, kelas..." oninput="app.filterAuditLogs()">
            </div>
            <div class="filter-group">
              <label for="logFilterType">Jenis Aktivitas</label>
              <select id="logFilterType" class="form-select" onchange="app.filterAuditLogs()">
                <option value="ALL">Semua Jenis Riwayat</option>
                <option value="BUBAR">Bubar Kelas (Status Override)</option>
                <option value="REPORT">Pengisian Jurnal & Jam Dosen</option>
                <option value="BOOKING">Peminjaman Mahasiswa</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="logFilterRoom">Jenis Ruangan</label>
              <select id="logFilterRoom" class="form-select" onchange="app.filterAuditLogs()">
                <option value="ALL">Semua Ruangan</option>
                <option value="RT 01">Ruang Teori</option>
                <option value="Lab RPL 1">Laboratorium Komputer</option>
                <option value="RK 201">Ruang Kuliah Besar</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Occupancy Log Table -->
        <div class="table-card log-table-card">
          <div class="table-responsive">
            <table class="data-table">
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
                <!-- Log rows will be populated by JS -->
              </tbody>
            </table>
          </div>
          <div class="log-pagination-wrap" id="logPagination">
            <nav class="room-pagination">
              <button type="button" class="pagination-button" disabled>Sebelumnya</button>
              <button type="button" class="pagination-button active">1</button>
              <button type="button" class="pagination-button">2</button>
              <button type="button" class="pagination-button">Berikutnya</button>
            </nav>
          </div>
        </div>
      </div>
    </section>

  </main>
</main>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>

<?php
$pageTitle = 'Jadwal Kelas - SIKARA';
$activePage = 'schedule';
$basePath = $basePath ?? '../';

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
        <!-- Filter Card (Overlapping Banner matching Image 2) -->
        <div class="filter-controls-panel schedule-filters">
          <div class="filter-header">
            <div class="filter-title-group">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
              </svg>
              <h3>Filter dan Penyaringan Jadwal</h3>
            </div>
          </div>
          <div class="filter-inputs-grid schedule-grid-4col">
            <div class="filter-group">
              <label for="scheduleSearch">Cari Jadwal</label>
              <input id="scheduleSearch" class="form-control" type="text" placeholder="Mata Kuliah, dosen, atau ruang..." oninput="app.renderSchedulePage()">
            </div>
            <div class="filter-group">
              <label for="scheduleRoomFilter">Ruang Kelas</label>
              <select id="scheduleRoomFilter" class="form-select" onchange="app.renderSchedulePage()">
                <option value="ALL">Semua Ruang</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="scheduleClassFilter">Kelas</label>
              <input id="scheduleClassFilter" class="form-control" type="text" placeholder="Semua Kelas" oninput="app.renderSchedulePage()">
            </div>
            <div class="filter-group">
              <label for="scheduleStatusFilter">Status</label>
              <select id="scheduleStatusFilter" class="form-select" onchange="app.renderSchedulePage()">
                <option value="Belum">Belum Dimulai</option>
                <option value="ALL">Semua Status</option>
                <option value="Sedang">Sedang Berlangsung</option>
                <option value="Selesai">Selesai</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table Card matching Image 2 -->
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
              <tbody id="masterScheduleTableBody"></tbody>
            </table>
          </div>
          <div class="schedule-pagination-wrap" id="schedulePagination">
            <nav class="room-pagination">
              <button type="button" class="pagination-button" disabled>Ã¢â‚¬Â¹ Sebelumnya</button>
              <button type="button" class="pagination-button active">1</button>
              <button type="button" class="pagination-button">2</button>
              <button type="button" class="pagination-button">Berikutnya Ã¢â‚¬Âº</button>
            </nav>
          </div>
        </div>
      </div>
    </section>

</main>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>

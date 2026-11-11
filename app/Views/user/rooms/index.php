<?php
$pageTitle = 'Ruang Kelas - SIKARA';
$activePage = 'rooms';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
  <section id="viewDashboard" class="page-view active">
    <!-- HERO SECTION (Matching reference image) -->
    <div class="dashboard-hero">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">Ruang Kelas</h1>
        <p class="hero-subtitle">Pemantauan okupansi dan alokasi ruang kelas kampus secara langsung.</p>

        <!-- Floating Universal Search Pill (Wide & Bootstrap-integrated) -->
        <div class="hero-search-pill d-flex align-items-center bg-white rounded-pill px-4 py-2 mx-auto shadow-sm">
          <div class="search-input-group w-100 d-flex align-items-center">
            <i class="bi bi-search text-secondary fs-5 me-3 flex-shrink-0" aria-hidden="true"></i>
            <input type="search" id="quickSearchInput"
              class="form-control border-0 shadow-none bg-transparent p-0 fs-6 text-dark"
              placeholder="Cari berdasarkan nama ruang, kode, atau nama kelas..."
              oninput="app.handleSearchInput(this.value)" autocomplete="off">
            <button type="button"
              class="btn btn-sm btn-light rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 ms-2 btn-search-clear"
              id="btnClearSearch" onclick="app.clearSearch()" style="display: none; width: 28px; height: 28px;"
              aria-label="Hapus pencarian">&times;</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN CONTAINER: STATISTICS & FILTERS & ROOM GRID -->
    <div class="dashboard-main-container">

      <!-- Live Status Bar & Statistics Cards (8pt Grid) -->
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
            <div class="stat-number time-display" id="currentTimeClock">11:10:56</div>
            <span class="stat-sub" id="currentDateDisplay">Jumat, 16 Oktober 2026</span>
          </div>
        </div>
      </div>

      <!-- Filter Controls Bar (Filter dan Penyaringan Ruang - Gedung Sipil Lt 5-8) -->
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
          <!-- Filter Status -->
          <div class="filter-group">
            <label for="filterStatus">Status Ruang</label>
            <select id="filterStatus" class="form-select" onchange="app.applyFilters()">
              <option value="ALL">Semua Status</option>
              <option value="KOSONG">Kosong</option>
              <option value="TERPAKAI">Terpakai</option>
            </select>
          </div>

          <!-- Filter Lantai -->
          <div class="filter-group">
            <label for="filterLantai">Lantai</label>
            <select id="filterLantai" class="form-select" onchange="app.applyFilters()">
              <option value="ALL">Semua Lantai</option>
              <option value="5">Lantai 5</option>
              <option value="6">Lantai 6</option>
              <option value="7">Lantai 7</option>
              <option value="8">Lantai 8</option>
            </select>
          </div>

          <!-- Filter Kapasitas -->
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

        <!-- Quick Status Pills -->
        <div class="quick-status-pills">
          <span class="pills-label">Tampilan Cepat:</span>
          <button class="status-tab-btn active" data-filter="ALL"
            onclick="app.setQuickFilter('all', 'ALL', this)">Semua</button>
          <button class="status-tab-btn" data-filter="KOSONG"
            onclick="app.setQuickFilter('status', 'KOSONG', this)">Kosong</button>
          <button class="status-tab-btn" data-filter="TERPAKAI"
            onclick="app.setQuickFilter('status', 'TERPAKAI', this)">Terpakai</button>
          <button class="status-tab-btn" data-floor="5" onclick="app.setQuickFilter('floor', '5', this)">Lantai
            5</button>
          <button class="status-tab-btn" data-floor="6" onclick="app.setQuickFilter('floor', '6', this)">Lantai
            6</button>
          <button class="status-tab-btn" data-floor="7" onclick="app.setQuickFilter('floor', '7', this)">Lantai
            7</button>
          <button class="status-tab-btn" data-floor="8" onclick="app.setQuickFilter('floor', '8', this)">Lantai
            8</button>
        </div>
      </div>

      <!-- Room Cards Grid -->
      <div class="rooms-section-header">
        <h2 class="section-title">Daftar Ruang Perkuliahan</h2>
        <span class="showing-count" id="showingCountText">Menampilkan 9 dari 36 Ruangan</span>
      </div>

      <div class="room-cards-grid" id="roomCardsGrid">
        <?php foreach ($rooms as $r):
          $status = strtolower($r['status']); ?>
          <article class="room-card"
            data-name="<?= htmlspecialchars(strtolower($r['name'] . ' ' . $r['building'] . ' ' . ($r['facilities'] ?? ''))) ?>"
            data-floor="<?= (int) $r['floor'] ?>" data-capacity="<?= (int) $r['capacity'] ?>"
            data-status="<?= htmlspecialchars(strtoupper($status)) ?>">
            <div class="card-image-wrap">
              <?php if (!empty($r['image_path'])): ?>
                <img src="<?= htmlspecialchars($r['image_path']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
              <?php endif; ?>
              <div class="card-top-bar">
                <span class="room-status-badge <?= $status ?>">
                  <span class="status-dot"></span><?= strtoupper($status) ?>
                </span>
              </div>
            </div>
            <div class="card-body">
              <div class="card-title-group">
                <h3 class="room-name"><?= htmlspecialchars($r['name']) ?></h3>
                <div class="room-location">
                  <?= htmlspecialchars($r['building']) ?> · Lantai <?= (int) $r['floor'] ?>
                </div>
              </div>
              <div class="card-occupancy-info <?= $status ?>">
                Kapasitas <?= (int) $r['capacity'] ?> orang
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div id="roomPagination"></div>

      <!-- No Results Fallback (KF-03 Requirement) -->
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
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (!window.app) return;

    const PER_PAGE = 9;
    const grid = document.getElementById('roomCardsGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.room-card'));
    const state = { keyword: '', status: 'ALL', floor: 'ALL', cap: 'ALL', page: 1 };

    const matches = (c) => {
      const cap = +c.dataset.capacity;
      return (!state.keyword || c.dataset.name.includes(state.keyword))
        && (state.status === 'ALL' || c.dataset.status === state.status)
        && (state.floor === 'ALL' || c.dataset.floor === state.floor)
        && (state.cap === 'ALL'
          || (state.cap === 'LT30' && cap < 30)
          || (state.cap === '30-35' && cap >= 30 && cap <= 35)
          || (state.cap === 'GT35' && cap > 35));
    };

    const pageItems = (total, cur) => {
      const items = [];
      for (let p = 1; p <= total; p++) {
        if (p === 1 || p === total || Math.abs(p - cur) <= 1) items.push(p);
        else if (items[items.length - 1] !== '…') items.push('…');
      }
      return items;
    };

    const renderPagination = (totalPages) => {
      const box = document.getElementById('roomPagination');
      if (!box) return;
      if (totalPages <= 1) { box.innerHTML = ''; return; }

      const li = (label, page, active = false, disabled = false) =>
        `<li class="page-item${active ? ' active' : ''}${disabled ? ' disabled' : ''}">
         <button type="button" class="page-link" ${page ? `data-page="${page}"` : 'tabindex="-1"'}>${label}</button>
       </li>`;

      let html = '<nav aria-label="Navigasi halaman ruangan"><ul class="pagination justify-content-center mt-4">';
      html += li('&laquo;', state.page - 1, false, state.page === 1);
      pageItems(totalPages, state.page).forEach(p => {
        html += p === '…' ? li('…', null, false, true) : li(p, p, p === state.page);
      });
      html += li('&raquo;', state.page + 1, false, state.page === totalPages);
      box.innerHTML = html + '</ul></nav>';
    };

    const render = () => {
      const filtered = cards.filter(matches);
      const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
      state.page = Math.min(Math.max(1, state.page), totalPages);

      const start = (state.page - 1) * PER_PAGE;
      const visible = new Set(filtered.slice(start, start + PER_PAGE));
      cards.forEach(c => {
        if (visible.has(c))
          c.style.removeProperty('display');
        else
          c.style.setProperty('display', 'none', 'important');
      });

      const countEl = document.getElementById('showingCountText');
      if (countEl) {
        countEl.textContent = filtered.length
          ? `Menampilkan ${start + 1}–${start + visible.size} dari ${filtered.length} Ruangan`
          : 'Tidak ada ruangan';
      }
      const empty = document.getElementById('noResultsState');
      if (empty) empty.style.display = filtered.length ? 'none' : '';

      renderPagination(filtered.length ? totalPages : 0);
    };

    // klik tombol halaman (event delegation)
    document.getElementById('roomPagination')?.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-page]');
      if (!btn) return;
      state.page = parseInt(btn.dataset.page, 10);
      render();
      grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    const setActivePill = (btn) => {
      document.querySelectorAll('.status-tab-btn').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
    };

    Object.assign(window.app, {
      handleSearchInput(v) {
        state.keyword = v.trim().toLowerCase();
        state.page = 1;
        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) clearBtn.style.display = v ? 'flex' : 'none';
        render();
      },
      clearSearch() {
        const input = document.getElementById('quickSearchInput');
        if (input) input.value = '';
        this.handleSearchInput('');
      },
      applyFilters() {
        state.status = document.getElementById('filterStatus').value;
        state.floor = document.getElementById('filterLantai').value;
        state.cap = document.getElementById('filterKapasitas').value;
        state.page = 1;
        setActivePill(null);
        render();
      },
      setQuickFilter(type, value, btn) {
        state.status = type === 'status' ? value : 'ALL';
        state.floor = type === 'floor' ? value : 'ALL';
        state.page = 1;
        document.getElementById('filterStatus').value = state.status;
        document.getElementById('filterLantai').value = state.floor;
        setActivePill(btn);
        render();
      },
      resetFilters() {
        Object.assign(state, { keyword: '', status: 'ALL', floor: 'ALL', cap: 'ALL', page: 1 });
        ['filterStatus', 'filterLantai', 'filterKapasitas']
          .forEach(id => { document.getElementById(id).value = 'ALL'; });
        const input = document.getElementById('quickSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('btnClearSearch');
        if (clearBtn) clearBtn.style.display = 'none';
        setActivePill(document.querySelector('.status-tab-btn'));
        render();
      }
    });

    render(); // tampilan awal: halaman 1, maksimal 9 kartu
  });
</script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>
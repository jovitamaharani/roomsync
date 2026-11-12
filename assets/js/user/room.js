/**
 * Halaman Ruang Kelas (user): modal detail, modal jadwal, filter, pencarian, dan pagination.
 * Dimuat dari app/Views/user/rooms/index.php.
 *
 * Data dari PHP tidak ditulis di file ini, melainkan dibaca dari HTML:
 *   - #roomSchedulesData        : JSON jadwal mingguan semua ruangan
 *   - #modalRoomSchedule        : atribut data-today (hari ini) dan data-error
 *   - .room-card                : atribut data-room, data-room-id, data-name, data-floor, data-capacity, data-status
 */
  // ===================== Modal detail dan modal jadwal =====================
  document.addEventListener('DOMContentLoaded', function () {
    const detailEl = document.getElementById('modalRoomDetail');
    const scheduleEl = document.getElementById('modalRoomSchedule');

    // Modal dipindah langsung ke <body> agar tidak tertutup lapisan gelap (backdrop)
    [detailEl, scheduleEl].forEach(function (el) { if (el) document.body.appendChild(el); });

    let activeCard = null;   // kartu yang sedang dibuka, dipakai bersama kedua modal

    // Pemicu di dalam kartu: ambil kartunya. Pemicu di dalam modal: pakai kartu terakhir.
    const cardOf = function (trigger) {
      return (trigger && trigger.closest('.room-card')) || activeCard;
    };
    const dash = function (v) { return (v === null || v === undefined || v === '') ? '-' : v; };
        // Data jadwal mingguan semua ruangan (dari PHP), dipakai modal detail dan modal jadwal
    let allSchedules = {};
    try {
      allSchedules = JSON.parse(document.getElementById('roomSchedulesData').textContent) || {};
    } catch (e) { /* data kosong atau rusak: modal tetap terbuka tanpa jadwal */ }

    // Hari dan jam sekarang dalam WIB (1 = Senin ... 7 = Minggu; time = "HH:MM")
    const nowWib = function () {
      const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta', weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23'
      }).formatToParts(new Date());
      const get = function (t) { return parts.find(function (p) { return p.type === t; }).value; };
      return {
        day: { Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6, Sun: 7 }[get('weekday')] || 0,
        time: get('hour') + ':' + get('minute')
      };
    };
    const todayLabel = function () {
      return new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
      }).format(new Date());
    };
    const el = function (tag, className, text) {
      const node = document.createElement(tag);
      if (className) node.className = className;
      if (text !== undefined) node.textContent = text;
      return node;
    };

    // ---------- Modal detail ruang ----------
    // ---------- Modal detail ruang ----------
    if (detailEl) {
      const field = function (name) { return detailEl.querySelector('[data-field="' + name + '"]'); };
      const OPEN_TIME = '07:00', CLOSE_TIME = '17:30';   // jam operasional, dipakai bila rentang kosong tidak berujung

      detailEl.addEventListener('show.bs.modal', function (event) {
        const card = cardOf(event.relatedTarget);
        if (!card || !card.dataset.room) return;
        activeCard = card;
        const d = JSON.parse(card.dataset.room);
        const used = d.status === 'terpakai';
        const type = d.room_type || 'Lain-Lain';

        // textContent (bukan innerHTML) agar isi database tidak bisa menyisipkan HTML
        // Judul
        field('name').textContent = d.name;
        field('title').textContent = type + ' - ' + dash(d.building) + ' Lantai ' + d.floor;

        // Banner status
        field('status-banner').className = 'room-detail-status-bar ' + (used ? 'terpakai' : 'kosong');
        field('status-label').textContent = used ? 'TERPAKAI (IN USE)' : 'KOSONG (AVAILABLE)';
        field('status-text').textContent = used
          ? (d.current_course ? 'Sedang berlangsung: ' + d.current_course
            : d.current_purpose ? 'Sedang dipinjam: ' + d.current_purpose
            : 'Ruangan sedang digunakan.')
          : 'Ruangan tidak sedang digunakan dan siap dialokasikan.';

        // Tombol peminjaman hanya untuk ruangan kosong
        field('borrow').classList.toggle('d-none', used);

        // Foto + keterangan lokasi
        field('location').textContent = dash(d.building) + ' · Lantai ' + d.floor;
        const img = field('image');
        const placeholder = field('image-placeholder');
        if (d.image_path) {
          img.src = d.image_path;
          img.alt = d.name;
          img.classList.remove('d-none');
          placeholder.classList.add('d-none');
        } else {
          img.removeAttribute('src');
          img.classList.add('d-none');
          placeholder.classList.remove('d-none');
        }

        // Kartu info
        field('capacity').textContent = d.capacity + ' Mahasiswa';
        field('seating').textContent = [
          d.chairs !== null && d.chairs !== undefined ? d.chairs + ' kursi' : '',
          d.tables !== null && d.tables !== undefined ? d.tables + ' meja' : ''
        ].filter(Boolean).join(' · ');
        field('room-type').textContent = type;

        const sessionValue = field('session');
        const sessionSub = field('session-sub');
        if (used && d.current_course) {
          sessionValue.textContent = d.current_course;
          sessionSub.textContent = [d.current_lecturer, d.current_class,
            (d.current_start || '') + ' - ' + (d.current_end || '') + ' WIB'].filter(Boolean).join(' • ');
        } else if (used) {
          sessionValue.textContent = d.current_purpose ? 'Dipinjam' : 'Sedang Digunakan';
          sessionSub.textContent = d.current_purpose || '';
        } else {
          sessionValue.textContent = 'Tidak Ada Sesi Aktif';
          sessionSub.textContent = (d.free_from || OPEN_TIME) + ' - ' + (d.free_until || CLOSE_TIME) + ' WIB' + '  |  ' + 'Tersedia untuk booking';
        }
        sessionSub.title = sessionSub.textContent;   // teks panjang dipotong "..." oleh CSS; isi lengkap tampil saat kursor diarahkan

        // Fasilitas: dipecah per koma; "-" berarti kosong
        const chips = field('facilities');
        chips.replaceChildren();
        const items = (d.facilities || '').split(',')
          .map(function (t) { return t.trim(); })
          .filter(function (t) { return t && t !== '-'; });
        if (items.length === 0) {
          chips.appendChild(el('span', 'text-muted', 'Belum ada data fasilitas.'));
        } else {
          items.forEach(function (text) {
            const chip = el('span', 'facility-pill');
            chip.appendChild(el('i', 'bi bi-check2'));
            chip.appendChild(el('span', '', text));
            chips.appendChild(chip);
          });
        }

        // Jadwal hari ini (dari jadwal mingguan, WIB)
        const now = nowWib();
        field('today-date').textContent = todayLabel();
        const list = field('today-list');
        list.replaceChildren();
        const todays = (allSchedules[card.dataset.roomId] || []).filter(function (s) { return s.day === now.day; });
        if (todays.length === 0) {
          list.appendChild(el('div', 'text-center text-muted small py-3', 'Tidak ada jadwal perkuliahan hari ini.'));
        } else {
          const label = { done: 'Selesai', live: 'Berlangsung', next: 'Akan Datang' };
          const badge = { done: 'text-secondary bg-light', live: 'terpakai', next: 'kosong' };
          todays.forEach(function (s) {
            const state = now.time >= s.end ? 'done' : (now.time >= s.start ? 'live' : 'next');
            const row = el('div', 'timeline-item' + (state === 'live' ? ' active-now' : '') + (state === 'done' ? ' opacity-75' : ''));
            row.appendChild(el('span', 'timeline-time', s.start + ' - ' + s.end));

            const subject = el('div', 'timeline-subject');
            const title = el('div', 'fw-semibold', s.course + ' ');
            title.appendChild(el('span', 'fw-normal text-muted', '(' + s.classroom + ')'));
            subject.appendChild(title);
            subject.appendChild(el('div', 'text-muted', s.lecturer));
            row.appendChild(subject);

            row.appendChild(el('span', 'timeline-status ' + badge[state], label[state]));
            list.appendChild(row);
          });
        }
      });
    }

    // ---------- Modal jadwal ruang ----------
    if (scheduleEl) {
      const sField = function (name) { return scheduleEl.querySelector('[data-field="' + name + '"]'); };
      const tabsBox = sField('sch-tabs');
      const tbody = sField('sch-rows');
      const tableWrap = sField('sch-table');
      const emptyBox = sField('sch-empty');
      const hasError = scheduleEl.dataset.error === '1';

      const dayShort = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
      const dayFull = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
      const DAY_COUNT = 5;   // jadwal hanya Senin-Jumat; ubah ke 7 kalau akhir pekan dipakai
      const TODAY = Number(scheduleEl.dataset.today) || 1;   // 1 = Senin ... 7 = Minggu (dari atribut data-today)

      let current = [];

      function td(text, className) {
        const cell = document.createElement('td');
        if (className) cell.className = className;
        cell.textContent = text;
        return cell;
      }

      function renderDay(day) {
        tabsBox.querySelectorAll('button').forEach(function (b) {
          b.classList.toggle('active', Number(b.dataset.day) === day);
        });

        const rows = current.filter(function (s) { return s.day === day; });
        tbody.replaceChildren();
        tableWrap.classList.toggle('d-none', rows.length === 0);
        emptyBox.classList.toggle('d-none', rows.length > 0);

        if (hasError) {
          emptyBox.textContent = 'Data jadwal tidak tersedia.';
        } else if (current.length === 0) {
          emptyBox.textContent = 'Ruangan ini belum memiliki jadwal kuliah.';
        } else {
          emptyBox.textContent = 'Tidak ada jadwal kuliah pada hari ' + dayFull[day - 1] + '.';
        }

        rows.forEach(function (s) {
          const tr = document.createElement('tr');

          const courseCell = document.createElement('td');
          const name = document.createElement('div');
          name.className = 'fw-semibold';
          name.textContent = s.course;
          const code = document.createElement('small');
          code.className = 'text-muted';
          code.textContent = s.code;
          courseCell.append(name, code);

          tr.append(td(s.start + ' - ' + s.end, 'fw-semibold text-nowrap'), courseCell, td(s.lecturer), td(s.classroom));
          tbody.appendChild(tr);
        });
      }

      // Tab hari dibuat sekali
      for (let day = 1; day <= DAY_COUNT; day++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-primary';
        btn.dataset.day = day;
        btn.title = dayFull[day - 1] + (day === TODAY ? ' (hari ini)' : '');
        btn.textContent = dayShort[day - 1];
        if (day === TODAY) {
          const dot = document.createElement('i');
          dot.className = 'bi bi-circle-fill ms-1';
          dot.style.fontSize = '6px';
          btn.appendChild(dot);
        }
        btn.addEventListener('click', function () { renderDay(day); });
        tabsBox.appendChild(btn);
      }

      scheduleEl.addEventListener('show.bs.modal', function (event) {
        const card = cardOf(event.relatedTarget);
        if (!card || !card.dataset.room) return;
        activeCard = card;
        const d = JSON.parse(card.dataset.room);

        current = allSchedules[card.dataset.roomId] || [];
        sField('sch-name').textContent = 'Jadwal ' + d.name;
        sField('sch-location').textContent =
          d.building + ' \u00b7 Lantai ' + d.floor + ' \u00b7 ' + current.length + ' sesi per minggu';
        renderDay(TODAY <= DAY_COUNT ? TODAY : 1);   // buka di tab hari ini (akhir pekan: Senin)
      });
    }
  });

  // ===================== Filter, pencarian, dan pagination =====================
  document.addEventListener('DOMContentLoaded', function () {

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
        else if (items[items.length - 1] !== '\u2026') items.push('\u2026');
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
        html += p === '\u2026' ? li('\u2026', null, false, true) : li(p, p, p === state.page);
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
        // .room-card memakai "display: flex !important", jadi perlu style inline dengan !important
        if (visible.has(c))
          c.style.removeProperty('display');
        else
          c.style.setProperty('display', 'none', 'important');
      });

      const countEl = document.getElementById('showingCountText');
      if (countEl) {
        countEl.textContent = filtered.length
          ? `Menampilkan ${start + 1}\u2013${start + visible.size} dari ${filtered.length} Ruangan`
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

    Object.assign(window.app || (window.app = {}), {
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

    render();
  });
<?php
$bkUser = $user ?? (class_exists('Auth') ? Auth::user() : ($_SESSION['user'] ?? null));
$bkName = (string) ($bkUser['name'] ?? '');
$bkNim = (string) ($bkUser['nim'] ?? '');
$bkCsrf = (string) ($csrfToken ?? '');
$bkFlash = $_SESSION['flash'] ?? [];
$bkReopen = (($bkFlash['target'] ?? '') === 'booking' && is_array($bkFlash['old'] ?? null)) ? $bkFlash['old'] : null;
$bkConfig = ['today' => date('Y-m-d'), 'reopen' => $bkReopen];
// Halaman asal (untuk kembali ke halaman yang sama setelah simpan/gagal)
$bkUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$bkPos = strpos($bkUri, 'index.php?');
$bkReturn = $bkPos !== false ? substr($bkUri, $bkPos) : '';
$bkFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
$bkH = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!-- ================= NOTIFICATION TOAST ================= -->
<div id="toastNotification" class="toast-container" aria-live="polite"></div>

<!-- ================= MODAL 1: ROOM DETAIL MODAL ================= -->
<div class="modal fade" id="roomDetailModal" tabindex="-1" aria-labelledby="modalRoomName" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <span class="room-code-badge" id="modalRoomCode">RT 02</span>
          <h5 class="modal-title fs-6 fw-bold mb-0" id="modalRoomName">Ruang Teori 02 - Gedung Sipil Lantai 5</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3">
        <div class="room-detail-status-bar" id="modalStatusBar">
          <div class="status-indicator-huge" id="modalStatusIndicator">
            <span class="indicator-dot"></span>
            <span class="status-text-large" id="modalStatusText">TERPAKAI</span>
          </div>
          <div class="status-secondary-info" id="modalStatusSecondary">
            Sedang berlangsung perkuliahan hingga 11:30 WIB
          </div>
        </div>

        <div class="room-meta-grid">
          <div class="room-img-container">
            <img id="modalRoomImage"
              src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80"
              alt="Foto Ruangan">
            <div class="room-location-tag" id="modalRoomLocation">Gedung Sipil &bull; Lantai 5</div>
          </div>

          <div class="room-quick-specs">
            <div class="spec-card">
              <span class="spec-label">Kapasitas Tempat Duduk</span>
              <span class="spec-value" id="modalRoomCapacity">35 Kursi</span>
              <span class="spec-desc">Layout standar kelas teori modern</span>
            </div>
            <div class="spec-card">
              <span class="spec-label">Tipe Ruangan</span>
              <span class="spec-value" id="modalRoomType">Ruang Kelas Teori</span>
              <span class="spec-desc">Jurusan Teknologi Informasi</span>
            </div>
            <div class="spec-card">
              <span class="spec-label">Jadwal Sesi Ini</span>
              <span class="spec-value text-primary" id="modalRoomSession">Desain & Pemrograman Web</span>
              <span class="spec-desc" id="modalRoomLecturer">Dimas Wahyu Wibowo, S.T., M.T. (Kelas 2F)</span>
            </div>
          </div>
        </div>

        <div class="facilities-section">
          <h4 class="subheading-modal">Fasilitas Ruang Kelas</h4>
          <div class="facility-badges" id="modalFacilityBadges"></div>
        </div>

        <div class="schedule-timeline-section">
          <div class="timeline-header">
            <h4 class="subheading-modal">Jadwal Penggunaan Hari Ini</h4>
            <span class="timeline-date" id="modalTimelineDate">Jumat, 16 Oktober 2026</span>
          </div>
          <div class="timeline-list" id="modalTimelineList"></div>
        </div>
      </div>

      <div class="modal-footer py-2 px-3">
        <button type="button" class="btn btn-sm btn-secondary" style="font-size: 11px;"
          data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-sm btn-danger" style="font-size: 11px;" id="btnModalReportActivity"
          onclick="app.openActivityReportFromCurrentRoom()">
          <i class="bi bi-file-earmark-text me-1"></i> Laporan Bubar
        </button>
        <button type="button" class="btn btn-sm btn-primary" style="font-size: 11px;" id="btnModalBooking"
          onclick="app.openBookingModalFromCurrentRoom()">
          <i class="bi bi-calendar-plus me-1"></i> Peminjaman Ruang
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 2: FORM BOOKING RUANGAN (TAMBAH & EDIT) ================= -->
<style>
  #bookingModal .select2-container {
    width: 100% !important;
  }

  #bookingModal .select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 6px !important;
    background-color: #ffffff;
    padding-left: 10px !important;
    padding-right: 32px !important;
    display: flex !important;
    align-items: center !important;
    position: relative !important;
  }

  #bookingModal .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #212529 !important;
    font-size: 13px !important;
    line-height: normal !important;
    padding: 0 !important;
  }

  #bookingModal .select2-container--default .select2-selection--single .select2-selection__arrow b {
    display: none !important;
  }

  #bookingModal .select2-container--default .select2-selection--single .select2-selection__arrow {
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    right: 10px !important;
    width: 16px !important;
    height: 16px !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z' clip-rule='evenodd' /%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: contain !important;
    pointer-events: none !important;
  }

  #bookingModal .select2-container--default.select2-container--disabled .select2-selection--single {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    opacity: 0.75;
    cursor: not-allowed !important;
  }

  #bookingModal .select2-dropdown {
    z-index: 1065 !important;
    font-size: 13px !important;
  }
</style>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <span class="badge text-bg-primary px-2.5 py-1" style="font-size: 11px;" id="bookingModalRoomCode">-</span>
          <h5 class="modal-title fs-6 fw-bold mb-0" id="bookingModalTitle">Form Peminjaman Ruang Kelas</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="bookingForm" method="POST" action="index.php?controller=booking&amp;action=store">
        <input type="hidden" name="id" id="bookingEditId" value="">
        <input type="hidden" name="room_id" id="bookingRoomId" value="">
        <input type="hidden" name="return_to" value="<?= $bkH($bkReturn) ?>">
        <?php if ($bkCsrf !== ''): ?>
          <input type="hidden" name="csrf_token" value="<?= $bkH($bkCsrf) ?>">
        <?php endif; ?>

        <div class="modal-body">
          <div class="alert alert-info d-flex align-items-start gap-2 mb-3 py-2 px-3 rounded-2">
            <i class="bi bi-info-circle-fill text-info fs-6 flex-shrink-0 mt-0.5"></i>
            <div style="font-size: 11.5px; line-height: 1.45;">
              Booking langsung terkonfirmasi (siapa cepat dia dapat). Sesi jam yang sudah terpakai atau sudah lewat
              tidak bisa dipilih.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="bookingRoomName">Ruangan yang Dipinjam</label>
            <input type="text" id="bookingRoomName" class="form-control bg-light" value="" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label" for="bookingBorrowerName">Nama Peminjam</label>
            <input type="text" id="bookingBorrowerName" class="form-control bg-light" value="<?= $bkH($bkName) ?>"
              readonly>
          </div>

          <div class="mb-3">
            <label class="form-label" for="bookingDate">Tanggal Peminjaman</label>
            <input type="date" id="bookingDate" name="date" class="form-control" min="<?= date('Y-m-d') ?>"
              value="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label" for="bookingSlotStart">Jam Mulai</label>
              <select id="bookingSlotStart" name="start_time" class="form-select" required disabled>
                <option value="">Pilih jam mulai</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label" for="bookingSlotEnd">Jam Selesai</label>
              <select id="bookingSlotEnd" name="end_time" class="form-select" required disabled>
                <option value="">Pilih jam selesai</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="bookingPurpose">Keperluan Peminjaman</label>
            <textarea id="bookingPurpose" name="purpose" class="form-control" rows="2" maxlength="255"
              placeholder="Tuliskan tujuan peminjaman..." required></textarea>
          </div>

          <div id="bookingValidationNotice"
            class="alert alert-secondary d-flex align-items-center gap-2 mb-0 py-2 px-3 rounded-2">
            <i id="bookingValidationIcon" class="bi bi-info-circle-fill fs-6 flex-shrink-0"></i>
            <span class="fw-semibold" style="font-size: 11px;" id="bookingValidationMsg">Pilih tanggal untuk melihat jam
              yang tersedia.</span>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-3" id="btnSubmitBooking" disabled>Pinjam Ruangan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= MODAL: KONFIRMASI HAPUS / BATALKAN PEMINJAMAN ================= -->
<div class="modal fade" id="deleteBookingModal" tabindex="-1" aria-labelledby="deleteBookingModalTitle"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fs-6 fw-bold text-danger d-flex align-items-center gap-2" id="deleteBookingModalTitle">
          <i class="bi bi-exclamation-triangle-fill"></i> Batalkan Peminjaman Ruang
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Action disesuaikan ke action=cancel -->
      <form id="deleteBookingForm" method="POST" action="index.php?controller=booking&amp;action=cancel">
        <!-- Name disesuaikan ke booking_id -->
        <input type="hidden" name="booking_id" id="deleteBookingId" value="">
        <input type="hidden" name="return_to" value="<?= $bkH($bkReturn) ?>">
        <?php if ($bkCsrf !== ''): ?>
          <input type="hidden" name="csrf_token" value="<?= $bkH($bkCsrf) ?>">
        <?php endif; ?>

        <div class="modal-body py-3">
          <p class="mb-3" style="font-size: 13px; color: #475569;">
            Apakah Anda yakin ingin membatalkan jadwal peminjaman ruangan berikut? Jadwal yang dibatalkan akan langsung
            dilepas dan bisa dipinjam oleh mahasiswa lain.
          </p>
          <div class="card bg-light border-0 p-3 rounded-3" style="font-size: 13px;">
            <div class="d-flex justify-content-between mb-1.5">
              <span class="text-muted">Ruangan:</span>
              <strong id="deleteBookingRoom">-</strong>
            </div>
            <div class="d-flex justify-content-between mb-1.5">
              <span class="text-muted">Tanggal:</span>
              <strong id="deleteBookingDate">-</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Waktu:</span>
              <strong id="deleteBookingTime">-</strong>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-danger btn-sm px-3" id="btnConfirmDelete">Ya, Batalkan
            Peminjaman</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="application/json" id="bookingModalConfig"><?= json_encode($bkConfig, $bkFlags) ?></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('bookingModal');
    var delModalEl = document.getElementById('deleteBookingModal');
    var roomDetailModalEl = document.getElementById('roomDetailModal');
    var cfgEl = document.getElementById('bookingModalConfig');
    if (!modalEl || !cfgEl) return;

    var cfg = {};
    try { cfg = JSON.parse(cfgEl.textContent || '{}'); } catch (err) { cfg = {}; }

    var SLOTS_URL = 'index.php?controller=booking&action=slots';
    var form = document.getElementById('bookingForm');
    var f = {
      editId: document.getElementById('bookingEditId'),
      roomId: document.getElementById('bookingRoomId'),
      roomName: document.getElementById('bookingRoomName'),
      badge: document.getElementById('bookingModalRoomCode'),
      date: document.getElementById('bookingDate'),
      start: document.getElementById('bookingSlotStart'),
      end: document.getElementById('bookingSlotEnd'),
      purpose: document.getElementById('bookingPurpose'),
      submit: document.getElementById('btnSubmitBooking'),
      title: document.getElementById('bookingModalTitle'),
      notice: document.getElementById('bookingValidationNotice'),
      noticeIcon: document.getElementById('bookingValidationIcon'),
      noticeMsg: document.getElementById('bookingValidationMsg')
    };

    var state = {
      slots: [],
      request: 0,
      pending: null,
      isEdit: false,
      activeRoom: { id: '', name: '' },
      reopen: (cfg && cfg.reopen && cfg.reopen.room_id) ? cfg.reopen : null
    };

    var NOTICE = {
      info: ['alert-secondary', 'bi-info-circle-fill'],
      ok: ['alert-success', 'bi-check-circle-fill'],
      warn: ['alert-warning', 'bi-exclamation-triangle-fill'],
      error: ['alert-danger', 'bi-x-circle-fill']
    };

    // jQuery/Select2 opsional: halaman ruangan tidak selalu memuatnya
    var hasJq = !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2);
    var $start = hasJq ? window.jQuery('#bookingSlotStart') : null;
    var $end = hasJq ? window.jQuery('#bookingSlotEnd') : null;

    if (hasJq) {
      $start.select2({ dropdownParent: window.jQuery('#bookingModal'), width: '100%', minimumResultsForSearch: Infinity });
      $end.select2({ dropdownParent: window.jQuery('#bookingModal'), width: '100%', minimumResultsForSearch: Infinity });
    }

    function syncSelect2() {
      if (hasJq) {
        $start.trigger('change.select2');
        $end.trigger('change.select2');
      }
    }

    function onSelectChange(el, $el, handler) {
      if (hasJq) { $el.on('change', handler); } else { el.addEventListener('change', handler); }
    }

    function setModalMode(mode) {
      state.isEdit = (mode === 'edit');
      if (state.isEdit) {
        form.action = 'index.php?controller=booking&action=update';
        if (f.title) f.title.textContent = 'Edit Peminjaman Ruang Kelas';
        if (f.submit) f.submit.textContent = 'Simpan Perubahan';
      } else {
        form.action = 'index.php?controller=booking&action=store';
        if (f.title) f.title.textContent = 'Form Peminjaman Ruang Kelas';
        if (f.submit) f.submit.textContent = 'Pinjam Ruangan';
        if (f.editId) f.editId.value = '';
      }
    }

    function setNotice(kind, text) {
      var n = NOTICE[kind] || NOTICE.info;
      f.notice.className = 'alert ' + n[0] + ' d-flex align-items-center gap-2 mb-0 py-2 px-3 rounded-2';
      f.noticeIcon.className = 'bi ' + n[1] + ' fs-6 flex-shrink-0';
      f.noticeMsg.textContent = text;
    }

    function usable(slot) { return slot.is_free && !slot.is_past; }
    function reason(slot) { return !slot.is_free ? 'terpakai' : 'sudah lewat'; }
    function timeClean(val) { return (val || '').trim().substring(0, 5); }

    // Slot milik booking yang sedang diedit tetap diizinkan
    function isOwnSlot(slot) {
      if (!state.isEdit || !state.pending || !state.pending.start || !state.pending.end) return false;
      var pStart = timeClean(state.pending.start);
      var pEnd = timeClean(state.pending.end);
      var sStart = timeClean(slot.start);
      var sEnd = timeClean(slot.end);
      return (sStart >= pStart && sEnd <= pEnd);
    }

    function slotByStart(value) {
      var target = timeClean(value);
      return state.slots.find(function (s) { return timeClean(s.start) === target; }) || null;
    }

    function slotByEnd(value) {
      var target = timeClean(value);
      return state.slots.find(function (s) { return timeClean(s.end) === target; }) || null;
    }

    function fillRoom(id, name) {
      f.roomId.value = id || '';
      f.roomName.value = name || '';
      if (f.badge) f.badge.textContent = name || '-';
      state.activeRoom = { id: id || '', name: name || '' };
    }

    function resetFields() {
      f.date.value = cfg.today || '';
      f.purpose.value = '';
      f.start.innerHTML = '<option value="">Pilih jam mulai</option>';
      f.end.innerHTML = '<option value="">Pilih jam selesai</option>';
      f.start.disabled = true;
      f.end.disabled = true;
      f.submit.disabled = true;
      state.slots = [];
      state.pending = null;
      syncSelect2();
    }

    // Helper untuk mengambil ID & Nama Ruangan dari card / button
    function getRoomInfo(btn) {
      var id = '';
      var name = '';

      // Catatan: data-name pada kartu ruangan adalah string pencarian (huruf kecil), bukan nama ruangan.
      if (btn) {
        id = btn.dataset.roomId || btn.getAttribute('data-room-id') || '';
        name = btn.dataset.roomName || btn.getAttribute('data-room-name') || '';

        if (btn.dataset.room) {
          try {
            var bData = JSON.parse(btn.dataset.room);
            if (bData.id && !id) id = bData.id;
            if (bData.name && !name) name = bData.name;
          } catch (err) { }
        }
      }

      var card = btn && btn.closest ? btn.closest('.room-card, [data-room-id]') : null;
      if (card) {
        if (!id) id = card.dataset.roomId || card.getAttribute('data-room-id') || '';
        if (!name) name = card.dataset.roomName || card.getAttribute('data-room-name') || '';

        if (card.dataset.room) {
          try {
            var cData = JSON.parse(card.dataset.room);
            if (cData.id && !id) id = cData.id;
            if (cData.name && !name) name = cData.name;
          } catch (err) { }
        }

        if (!name) {
          var nameEl = card.querySelector('.room-name, .room-title, .card-title, h5, h4, h3');
          if (nameEl) name = nameEl.textContent.trim();
        }
      }

      if (!id && state.activeRoom && state.activeRoom.id) {
        id = state.activeRoom.id;
        name = state.activeRoom.name;
      }

      return { id: String(id || ''), name: String(name || '') };
    }

    function renderStart() {
      var previous = f.start.value;
      f.start.innerHTML = '<option value="">Pilih jam mulai</option>';
      state.slots.forEach(function (s) {
        var isAvailable = usable(s) || isOwnSlot(s);
        var o = document.createElement('option');
        o.value = s.start;
        o.textContent = 'Jam ke-' + s.slot_no + ' (' + timeClean(s.start) + ' - ' + timeClean(s.end) + ')' + (isAvailable ? '' : ' - ' + reason(s));
        o.disabled = !isAvailable;
        f.start.appendChild(o);
      });
      f.start.disabled = state.slots.length === 0;
      if (previous && slotByStart(previous)) f.start.value = previous;
      syncSelect2();
    }

    function renderEnd() {
      var previous = f.end.value;
      var from = slotByStart(f.start.value);
      var blocked = false;
      f.end.innerHTML = '<option value="">Pilih jam selesai</option>';
      state.slots.forEach(function (s) {
        var isCurrent = isOwnSlot(s);
        var enabled = false;
        if (from && s.slot_no >= from.slot_no && !blocked) {
          if (usable(s) || isCurrent) {
            enabled = true;
          } else {
            blocked = true;
          }
        }
        var o = document.createElement('option');
        o.value = s.end;
        o.textContent = 'Jam ke-' + s.slot_no + ' (selesai ' + timeClean(s.end) + ')' + (from && s.slot_no >= from.slot_no && !enabled ? ' - ' + reason(s) : '');
        o.disabled = !enabled;
        f.end.appendChild(o);
      });
      f.end.disabled = !from;
      if (previous && slotByEnd(previous)) {
        var opt = Array.prototype.find.call(f.end.options, function (x) { return timeClean(x.value) === timeClean(previous); });
        if (opt && !opt.disabled) f.end.value = opt.value;
      }
      syncSelect2();
    }

    function refresh() {
      var a = slotByStart(f.start.value);
      var b = slotByEnd(f.end.value);
      var ok = !!(a && b && b.slot_no >= a.slot_no);
      f.submit.disabled = !ok;
      if (state.slots.length === 0) return;
      if (!ok) {
        setNotice('info', 'Pilih jam mulai dan jam selesai yang valid.');
        return;
      }
      var n = b.slot_no - a.slot_no + 1;
      setNotice('ok', 'Jam ke-' + a.slot_no + (n > 1 ? ' s.d. ' + b.slot_no : '') + ': ' + timeClean(a.start) + ' - ' + timeClean(b.end) + ' (' + n + ' sesi). Tersedia.');
    }

    function applyPending() {
      if (!state.pending || !state.pending.start) return;
      var p = state.pending;
      var a = slotByStart(p.start);
      if (a) {
        f.start.value = a.start;
        renderEnd();
        var b = slotByEnd(p.end);
        if (b) {
          var opt = Array.prototype.find.call(f.end.options, function (x) { return timeClean(x.value) === timeClean(b.end); });
          if (opt) f.end.value = opt.value;
        }
        syncSelect2();
      }
    }

    function loadSlots(callback) {
      if (!f.roomId.value) {
        setNotice('warn', 'Ruangan belum dipilih.');
        return;
      }
      if (!f.date.value) {
        setNotice('info', 'Pilih tanggal untuk melihat jam yang tersedia.');
        return;
      }

      var request = ++state.request;
      f.start.disabled = true;
      f.end.disabled = true;
      f.submit.disabled = true;
      syncSelect2();
      setNotice('info', 'Memuat ketersediaan jam...');

      var url = SLOTS_URL + '&room_id=' + encodeURIComponent(f.roomId.value) + '&date=' + encodeURIComponent(f.date.value);
      fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (res) {
          if (!res.ok) throw new Error('http ' + res.status);
          return res.json();
        })
        .then(function (data) {
          if (request !== state.request) return;
          if (!Array.isArray(data)) throw new Error('format');
          state.slots = data;
          renderStart();
          applyPending();
          renderEnd();
          refresh();
          if (typeof callback === 'function') callback();
        })
        .catch(function () {
          if (request !== state.request) return;
          state.slots = [];
          renderStart();
          renderEnd();
          syncSelect2();
          setNotice('error', 'Gagal memuat ketersediaan jam.');
        });
    }

    f.date.addEventListener('change', function () {
      if (!state.isEdit) state.pending = null;
      f.start.value = '';
      f.end.value = '';
      syncSelect2();
      loadSlots();
    });

    onSelectChange(f.start, $start, function () {
      f.end.value = '';
      renderEnd();
      var a = slotByStart(f.start.value);
      if (a) {
        var opt = Array.prototype.find.call(f.end.options, function (x) { return timeClean(x.value) === timeClean(a.end); });
        if (opt && !opt.disabled) f.end.value = opt.value;
      }
      syncSelect2();
      refresh();
    });

    onSelectChange(f.end, $end, refresh);

    // Simpan data ruangan saat Modal Detail dibuka
    if (roomDetailModalEl) {
      roomDetailModalEl.addEventListener('show.bs.modal', function (e) {
        var trigger = e.relatedTarget;
        var info = getRoomInfo(trigger);
        if (info.id) state.activeRoom = info;
      });
    }

    // Listener Saat Modal Booking Dibuka
    modalEl.addEventListener('show.bs.modal', function (e) {
      var btn = e.relatedTarget;

      // 0. BUKA ULANG setelah submit gagal: pulihkan data yang tadi diisi
      if (state.reopen) {
        var ro = state.reopen;
        state.reopen = null;
        var wasEdit = !!ro.id;

        setModalMode(wasEdit ? 'edit' : 'add');
        resetFields();

        if (wasEdit) f.editId.value = ro.id;
        fillRoom(ro.room_id || '', ro.room_name || '');
        if (ro.date) f.date.value = ro.date;
        f.purpose.value = ro.purpose || '';
        state.pending = { start: ro.start_time || '', end: ro.end_time || '' };

        loadSlots(function () {
          applyPending();
          refresh();
        });
        return;
      }

      // 1. MODE EDIT
      if (btn && btn.classList.contains('btn-edit-booking')) {
        setModalMode('edit');
        resetFields();

        var editId = btn.getAttribute('data-id') || '';
        var rId = btn.getAttribute('data-room-id') || '';
        var rName = btn.getAttribute('data-room-name') || '';
        var bDate = btn.getAttribute('data-date') || '';
        var bStart = btn.getAttribute('data-start') || '';
        var bEnd = btn.getAttribute('data-end') || '';
        var bPurpose = btn.getAttribute('data-purpose') || '';

        f.editId.value = editId;
        fillRoom(rId, rName);
        f.date.value = bDate;
        f.purpose.value = bPurpose;
        state.pending = { start: bStart, end: bEnd };

        loadSlots(function () {
          applyPending();
          refresh();
        });
        return;
      }

      // 2. MODE TAMBAH
      setModalMode('add');
      resetFields();

      var roomInfo = getRoomInfo(btn);
      if (roomInfo.id) {
        fillRoom(roomInfo.id, roomInfo.name);
        loadSlots();
      } else {
        setNotice('warn', 'Ruangan belum dipilih.');
      }
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      setModalMode('add');
      resetFields();
    });

    // Tombol Booking dari Modal Detail
    window.app = window.app || {};
    window.app.openBookingModalFromCurrentRoom = function () {
      if (roomDetailModalEl && window.bootstrap) {
        var detailInstance = bootstrap.Modal.getInstance(roomDetailModalEl);
        if (detailInstance) detailInstance.hide();
      }
      setModalMode('add');
      resetFields();
      if (state.activeRoom.id) {
        fillRoom(state.activeRoom.id, state.activeRoom.name);
        loadSlots();
      }
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    // Handler Modal Hapus
    if (delModalEl) {
      delModalEl.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        var delId = document.getElementById('deleteBookingId');
        var delRoom = document.getElementById('deleteBookingRoom');
        var delDate = document.getElementById('deleteBookingDate');
        var delTime = document.getElementById('deleteBookingTime');

        if (delId) delId.value = btn.getAttribute('data-id') || '';
        if (delRoom) delRoom.textContent = btn.getAttribute('data-room-name') || '-';
        if (delDate) delDate.textContent = btn.getAttribute('data-date') || '-';
        if (delTime) delTime.textContent = btn.getAttribute('data-time') || '-';
      });
    }

    form.addEventListener('submit', function (e) {
      if (!f.roomId.value || !f.start.value || !f.end.value) {
        e.preventDefault();
        setNotice('warn', 'Lengkapi jam mulai dan jam selesai terlebih dahulu.');
        return;
      }
      f.submit.disabled = true;
    });

    // Buka otomatis modal jika sebelumnya submit gagal (data lama dipulihkan di show.bs.modal)
    if (state.reopen && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
  });
</script>

<!-- ================= MODAL 3: QR CODE SCANNER SIMULATOR ================= -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fs-6 fw-bold mb-0" id="qrScannerModalTitle">Simulasi Pindai QR Pintu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="qr-camera-mockup mx-auto mb-3" style="width: 140px; height: 140px;">
          <div class="qr-crosshair"></div>
          <div class="qr-scan-line"></div>
          <p class="qr-cam-sub" style="font-size: 9.5px;">Arahkan kamera ke stiker QR pintu</p>
        </div>
        <p class="text-secondary mb-2" style="font-size: 11px;">Pilih pintu kelas yang ingin disimulasikan scan:</p>
        <div class="qr-room-select-grid d-flex flex-wrap gap-1.5 justify-content-center" id="qrQuickRoomList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL 4: JADWAL KELAS OVERVIEW ================= -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 780px;">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <span class="badge text-bg-secondary mb-1" style="font-size: 10px;">Jadwal Perkuliahan Akademik</span>
          <h5 class="modal-title fs-6 fw-bold mb-0" id="scheduleModalTitle">Jadwal Kuliah Hari Ini (Semua Kelas &
            Ruangan)</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-3">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0" style="font-size: 11.5px;">
            <thead class="table-light">
              <tr class="text-secondary" style="font-size: 11px;">
                <th class="py-2.5 px-3">Jam Kuliah</th>
                <th class="py-2.5 px-3">Ruang</th>
                <th class="py-2.5 px-3">Mata Kuliah</th>
                <th class="py-2.5 px-3">Dosen Pengampu</th>
                <th class="py-2.5 px-3">Kelas</th>
                <th class="py-2.5 px-3">Status</th>
              </tr>
            </thead>
            <tbody id="masterScheduleTableBody"></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
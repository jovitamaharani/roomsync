  <!-- ================= NOTIFICATION TOAST ================= -->
  <div id="toastNotification" class="toast-container" aria-live="polite"></div>

  <!-- ================= MODAL 1: ROOM DETAIL MODAL ================= -->
  <!-- Vertically centered modal -->
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
          <!-- Room Status Ribbon -->
          <div class="room-detail-status-bar" id="modalStatusBar">
            <div class="status-indicator-huge" id="modalStatusIndicator">
              <span class="indicator-dot"></span>
              <span class="status-text-large" id="modalStatusText">TERPAKAI</span>
            </div>
            <div class="status-secondary-info" id="modalStatusSecondary">
              Sedang berlangsung perkuliahan hingga 11:30 WIB
            </div>
          </div>

          <!-- Room Visual & Quick Metrics -->
          <div class="room-meta-grid">
            <div class="room-img-container">
              <img id="modalRoomImage" src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80" alt="Foto Ruangan">
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

          <!-- Room Facilities List -->
          <div class="facilities-section">
            <h4 class="subheading-modal">Fasilitas Ruang Kelas</h4>
            <div class="facility-badges" id="modalFacilityBadges">
              <!-- Dynamically populated -->
            </div>
          </div>

          <!-- Schedule Timeline Today -->
          <div class="schedule-timeline-section">
            <div class="timeline-header">
              <h4 class="subheading-modal">Jadwal Penggunaan Hari Ini</h4>
              <span class="timeline-date" id="modalTimelineDate">Jumat, 16 Oktober 2026</span>
            </div>
            <div class="timeline-list" id="modalTimelineList">
              <!-- Dynamically populated -->
            </div>
          </div>
        </div>

        <div class="modal-footer py-2 px-3">
          <button type="button" class="btn btn-sm btn-secondary" style="font-size: 11px;" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-sm btn-danger" style="font-size: 11px;" id="btnModalReportActivity" onclick="app.openActivityReportFromCurrentRoom()">
            <i class="bi bi-file-earmark-text me-1"></i> Laporan Bubar
          </button>
          <button type="button" class="btn btn-sm btn-primary" style="font-size: 11px;" id="btnModalBooking" onclick="app.openBookingModalFromCurrentRoom()">
            <i class="bi bi-calendar-plus me-1"></i> Peminjaman Ruang
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL 2: ROOM RESERVATION / BOOKING FORM ================= -->
  <!-- Vertically centered modal -->
  <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
      <div class="modal-content">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-primary px-2.5 py-1" style="font-size: 11px;" id="bookingModalRoomCode">RT 01</span>
            <h5 class="modal-title fs-6 fw-bold mb-0" id="bookingModalTitle">Form Peminjaman Ruang Kelas</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="bookingForm" onsubmit="app.handleBookingSubmit(event)">
          <div class="modal-body">
            <div class="alert alert-info d-flex align-items-start gap-2 mb-3 py-2 px-3 rounded-2">
              <i class="bi bi-info-circle-fill text-info fs-6 flex-shrink-0 mt-0.5"></i>
              <div style="font-size: 11.5px; line-height: 1.45;">
                Sistem SIKARA akan otomatis memvalidasi ketersediaan waktu untuk mencegah bentrok jadwal dengan perkuliahan reguler.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="bookingRoom">Ruangan yang Dipinjam</label>
              <select id="bookingRoom" class="form-select" onchange="app.handleBookingRoomChange(this.value)">
                <!-- Populated dynamically -->
              </select>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label" for="bookingBorrowerName">Nama Peminjam</label>
                <input type="text" id="bookingBorrowerName" class="form-control" placeholder="Nama Mahasiswa / Dosen" value="Jovita Maharani" required>
              </div>
              <div class="col-6">
                <label class="form-label" for="bookingNIM">NIM / NIP</label>
                <input type="text" id="bookingNIM" class="form-control" placeholder="Contoh: 254107020022" value="254107020022" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label" for="bookingDate">Tanggal Peminjaman</label>
                <input type="date" id="bookingDate" class="form-control" value="2026-10-16" required>
              </div>
              <div class="col-6">
                <label class="form-label" for="bookingOrganisasi">Organisasi / Kelas</label>
                <input type="text" id="bookingOrganisasi" class="form-control" value="Himpunan Mahasiswa TI (HMTI)" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label" for="bookingTimeStart">Jam Mulai (WIB)</label>
                <input type="time" id="bookingTimeStart" class="form-control" value="13:00" required>
              </div>
              <div class="col-6">
                <label class="form-label" for="bookingTimeEnd">Jam Selesai (WIB)</label>
                <input type="time" id="bookingTimeEnd" class="form-control" value="15:30" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label" for="bookingPurpose">Keperluan Peminjaman</label>
              <textarea id="bookingPurpose" class="form-control" rows="2" placeholder="Tuliskan tujuan peminjaman..." required>Koordinasi pengerjaan Proyek Berbasis Masalah (PBL) Semester Genap.</textarea>
            </div>

            <!-- Live Conflict Validator Box -->
            <div id="bookingValidationNotice" class="alert alert-success d-flex align-items-center gap-2 mb-0 py-2 px-3 rounded-2">
              <i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0"></i>
              <span class="fw-semibold" style="font-size: 11px;" id="bookingValidationMsg">Rentang waktu tersedia. Tidak ada jadwal tumpang tindih.</span>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary px-3" id="btnSubmitBooking">
              Ajukan Peminjaman
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ================= MODAL 3: QR CODE SCANNER SIMULATOR ================= -->
  <!-- Vertically centered modal -->
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
          <div class="qr-room-select-grid d-flex flex-wrap gap-1.5 justify-content-center" id="qrQuickRoomList">
            <!-- Filled via JS -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL 4: JADWAL KELAS OVERVIEW ================= -->
  <!-- Vertically centered modal -->
  <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 780px;">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <span class="badge text-bg-secondary mb-1" style="font-size: 10px;">Jadwal Perkuliahan Akademik</span>
            <h5 class="modal-title fs-6 fw-bold mb-0" id="scheduleModalTitle">Jadwal Kuliah Hari Ini (Semua Kelas & Ruangan)</h5>
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
              <tbody id="masterScheduleTableBody">
                <!-- Dynamically populated -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

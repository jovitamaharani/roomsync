<?php
$pageTitle = 'Profil Pengguna - SIKARA';
$activePage = 'profile';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
  <section id="viewProfile" class="page-view active">
    <div class="profile-page-wrapper py-4 px-3 mx-auto" style="max-width: 920px;">
      
      <!-- Top Bar: Title & Back Button -->
      <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
          <h2 class="fw-bold mb-1" style="color: #0A255C; font-size: 24px;">Profil Pengguna</h2>
          <p class="text-muted mb-0" style="font-size: 13px;">Kelola foto profil, informasi akun, dan kata sandi Anda.</p>
        </div>
        <a href="#" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" onclick="app.navigate('dashboard'); return false;">
          &larr; Kembali ke Ruang Kelas
        </a>
      </div>

      <!-- Avatar & Quick Overview Card -->
      <div class="card border-0 shadow-sm rounded-3 p-4 mb-4" style="border: 1px solid #E2E8F0 !important;">
        <div class="d-flex flex-column flex-sm-row align-items-center gap-4">
          <div class="position-relative flex-shrink-0">
            <img id="profileAvatarImg" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="Foto Profil" class="rounded-circle object-fit-cover shadow-sm border border-3 border-white" style="width: 96px; height: 96px;">
            <button type="button" class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" onclick="document.getElementById('profilePhotoInput').click()" title="Ganti Foto Profil" aria-label="Ganti Foto Profil">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
              </svg>
            </button>
            <input type="file" id="profilePhotoInput" accept="image/*" class="d-none" onchange="app.handlePhotoUpload(event)">
          </div>
          <div class="flex-grow-1 text-center text-sm-start">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2 mb-1">
              <h4 id="profileName" class="fw-bold mb-0" style="color: #0A255C; font-size: 20px;">Dafa Naufal Rabbani</h4>
              <span id="profileRole" class="badge rounded-pill px-3 py-1 fw-semibold" style="background: #EEF4FF; color: #2563EB; font-size: 11px;">Ketua Kelas TI-2F</span>
            </div>
            <p id="profileRoleDescription" class="text-muted small mb-3">Akun aktif Sistem Informasi Kelas & Ruangan Kampus (SIKARA)</p>
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="document.getElementById('profilePhotoInput').click()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Unggah Foto Baru
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="app.resetDefaultPhoto()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                  <path d="M3 3v5h5"></path>
                </svg>
                Reset Foto
              </button>
            </div>
            <small class="text-muted d-block mt-2" style="font-size: 11px;">Mendukung format JPG, PNG, atau WEBP. Maksimal ukuran 3MB.</small>
          </div>
        </div>
      </div>

      <!-- Two Column Layout: Info Pribadi & Ubah Kata Sandi -->
      <div class="row g-4">
        <!-- Column 1: Informasi Pribadi -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-3 h-100 p-4" style="border: 1px solid #E2E8F0 !important;">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <h5 class="fw-bold mb-0" style="color: #0A255C; font-size: 15px;">Informasi Pribadi</h5>
            </div>
            <form id="profileInfoForm" onsubmit="app.handleProfileInfoSubmit(event)">
              <div class="mb-3">
                <label for="profileInputName" class="form-label text-secondary small fw-semibold mb-1">Nama Lengkap</label>
                <input type="text" id="profileInputName" class="form-control" style="font-size: 13px;" required>
              </div>
              <div class="mb-3">
                <label for="profileInputEmail" class="form-label text-secondary small fw-semibold mb-1">Email Polinema</label>
                <input type="email" id="profileInputEmail" class="form-control" style="font-size: 13px;" required>
              </div>
              <div class="row g-2 mb-3">
                <div class="col-sm-6">
                  <label for="profileInputNIM" class="form-label text-secondary small fw-semibold mb-1">NIM / Identitas</label>
                  <input type="text" id="profileInputNIM" class="form-control bg-light" value="2341720088" style="font-size: 13px;" readonly>
                  <small class="text-muted" style="font-size: 10px;">Terhubung SIAKAD (Kunci)</small>
                </div>
                <div class="col-sm-6">
                  <label for="profileInputRole" class="form-label text-secondary small fw-semibold mb-1">Peran Akses</label>
                  <input type="text" id="profileInputRole" class="form-control bg-light" value="Ketua Kelas TI-2F" style="font-size: 13px;" readonly>
                  <small class="text-muted" style="font-size: 10px;">Hak Akses Terverifikasi</small>
                </div>
              </div>
              <div class="mb-4">
                <label for="profileInputPhone" class="form-label text-secondary small fw-semibold mb-1">Nomor WhatsApp / HP</label>
                <input type="tel" id="profileInputPhone" class="form-control" value="081234567890" style="font-size: 13px;">
              </div>
              <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 w-100 fw-semibold" style="font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                  <polyline points="17 21 17 13 7 13 7 21"></polyline>
                  <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Simpan Perubahan Profil
              </button>
            </form>
          </div>
        </div>

        <!-- Column 2: Ubah Kata Sandi -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-3 h-100 p-4" style="border: 1px solid #E2E8F0 !important;">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <h5 class="fw-bold mb-0" style="color: #0A255C; font-size: 15px;">Ubah Kata Sandi</h5>
            </div>
            <form id="profilePasswordForm" onsubmit="app.handlePasswordSubmit(event)">
              <div class="mb-3">
                <label for="currentPassword" class="form-label text-secondary small fw-semibold mb-1">Kata Sandi Saat Ini</label>
                <div class="input-group">
                  <input type="password" id="currentPassword" class="form-control" style="font-size: 13px;" placeholder="Masukkan kata sandi lama" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="app.togglePasswordVisibility('currentPassword', this)" aria-label="Lihat kata sandi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label text-secondary small fw-semibold mb-1">Kata Sandi Baru</label>
                <div class="input-group">
                  <input type="password" id="newPassword" class="form-control" style="font-size: 13px;" placeholder="Minimal 6 karakter" minlength="6" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="app.togglePasswordVisibility('newPassword', this)" aria-label="Lihat kata sandi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label text-secondary small fw-semibold mb-1">Konfirmasi Kata Sandi Baru</label>
                <div class="input-group">
                  <input type="password" id="confirmPassword" class="form-control" style="font-size: 13px;" placeholder="Ulangi kata sandi baru" minlength="6" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="app.togglePasswordVisibility('confirmPassword', this)" aria-label="Lihat kata sandi">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="p-2 mb-3 rounded bg-light" style="font-size: 11px; color: #64748B;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Gunakan minimal 6 karakter kombinasi huruf dan angka untuk keamanan optimal.
              </div>
              <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 w-100 fw-semibold" style="font-size: 13px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Perbarui Kata Sandi
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>

</main>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/footer.php';
?>

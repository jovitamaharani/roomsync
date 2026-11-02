<?php
$pageTitle = 'Login - SIKARA';
$activePage = 'login';
$basePath = $basePath ?? '../';

require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<main id="mainApp">
    <section id="viewLogin" class="page-view active">
      <div class="login-wrapper">
        <div class="login-card">
          <!-- Left Branding Banner (Exactly as reference image) -->
          <div class="login-left-banner">
            <div class="login-brand-group">
              <div class="login-logo-symbol">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3.5 10.2L12 3.6L20.5 10.2V19.8C20.5 20.4627 19.9627 21 19.3 21H4.7C4.03726 21 3.5 20.4627 3.5 19.8V10.2Z" fill="white"/>
                  <path d="M8.5 14.5C8.5 13 10.067 11.8 12 11.8C13.933 11.8 15.5 13 15.5 14.5" stroke="#0E2C6A" stroke-width="2.3" stroke-linecap="round"/>
                </svg>
              </div>
              <h1 class="login-brand-title">SIKARA</h1>
              <p class="login-brand-desc">
                Pantau ketersediaan ruangan, cek jadwal, dan kelola penggunaan kelas dengan mudah dan cepat.
              </p>
            </div>
            <div class="login-footer-badge">
              <span>Sistem Monitoring Ruang &middot; PBL 2026</span>
            </div>
          </div>

          <!-- Right Form Container -->
          <div class="login-right-form">
            <div class="form-header">
              <h2 class="form-title">Masuk ke Akun</h2>
              <p class="form-subtitle">Silakan masukkan kredensial Anda untuk melanjutkan.</p>
            </div>

            <!-- Demo Quick Login Chips for easy testing -->
            <div class="demo-role-selector">
              <span class="demo-label">Login Cepat Demo:</span>
              <div class="demo-chips">
                <button type="button" class="chip active" onclick="app.fillDemoAccount('ketua')">Ketua Kelas (2F)</button>
                <button type="button" class="chip" onclick="app.fillDemoAccount('admin')">Admin (Kajur)</button>
                <button type="button" class="chip" onclick="app.fillDemoAccount('mhs')">Mahasiswa</button>
              </div>
            </div>

            <form id="loginForm" onsubmit="app.handleLoginSubmit(event)">
              <div class="form-group">
                <label for="loginEmail" class="form-label">Email Pengguna</label>
                <div class="input-icon-wrapper">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                      <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                  </span>
                  <input type="email" id="loginEmail" class="form-control" placeholder="Masukkan Email" value="ketua.2f@polinema.ac.id" required>
                </div>
              </div>

              <div class="form-group">
                <label for="loginPassword" class="form-label">Kata Sandi</label>
                <div class="input-icon-wrapper">
                  <span class="input-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                  </span>
                  <input type="password" id="loginPassword" class="form-control password-input" placeholder="Masukkan Kata Sandi" value="password2f" required>
                  <button type="button" class="password-toggle" id="toggleLoginPassword" onclick="app.toggleLoginPasswordVisibility()" aria-label="Tampilkan kata sandi" aria-pressed="false">
                    <svg class="password-eye-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                  </button>
                </div>
              </div>

              <div class="form-row-remember">
                <label class="checkbox-container">
                  <input type="checkbox" id="rememberMe" checked>
                  <span class="custom-checkbox"></span>
                  <span class="checkbox-label">Ingat saya</span>
                </label>
                <a href="#" class="forgot-link" onclick="app.navigate('forgotPassword'); return false;">Lupa Kata Sandi?</a>
              </div>

              <button type="submit" class="btn-login-submit">
                <span>Masuk</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                  <polyline points="10 17 15 12 10 7"></polyline>
                  <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
              </button>
            </form>

            <div class="guest-access-hint">
              <p>Ingin melihat ketersediaan tanpa login? <a href="#" onclick="app.navigate('dashboard'); return false;">Lihat Dashboard Publik &rarr;</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>

</main>

<?php

require_once __DIR__ . '/../components/footer.php';
?>

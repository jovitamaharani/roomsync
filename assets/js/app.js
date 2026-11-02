/**
 * SIKARA - Sistem Informasi Ketersediaan dan Alokasi Ruang
 * Core Frontend SPA Controller & Mock State Engine
 * PBL RoomSync - JTI POLINEMA 2026
 */

(function () {
  'use strict';

  // ==========================================
  // INITIAL MOCK DATA (ROOMS, JADWAL, LOGS)
  // ==========================================
    const initialRooms = (() => {
    const list = [];
    const subjects = [
      { subject: 'Pemrograman Berbasis Objek', lecturer: 'M. Hasyim Adiyani', class: 'TI - 2F' },
      { subject: 'Desain dan Pemrograman Web', lecturer: 'Dimas Wahyu Wibowo', class: 'TI - 2E' },
      { subject: 'Struktur Data & Algoritma', lecturer: 'Titis Octary Satrio', class: 'TI - 2D' },
      { subject: 'Basis Data Relasional', lecturer: 'Mustika Mentari', class: 'TI - 2C' },
      { subject: 'Jaringan Komputer', lecturer: 'Budi Harijanto', class: 'TI - 2B' },
      { subject: 'Kecerdasan Buatan', lecturer: 'Farid Angga', class: 'TI - 3A' },
      { subject: 'Sistem Operasi', lecturer: 'Vivin Ayu Lestari', class: 'TI - 2A' },
      { subject: 'Rekayasa Perangkat Lunak', lecturer: 'Usman Nurhasan', class: 'TI - 3B' },
      { subject: 'Keamanan Siber & Jaringan', lecturer: 'Ahmad Afif Supianto', class: 'TI - 3C' }
    ];

    const kosongCodes = ['RT 01', 'RT 03', 'RT 04', 'RT 06', 'RT 07', 'RT 09'];

    for (let i = 1; i <= 36; i++) {
      const numStr = String(i).padStart(2, '0');
      const code = `RT ${numStr}`;
      const floor = i <= 9 ? 5 : (i <= 18 ? 6 : (i <= 27 ? 7 : 8));
      const isKosong = kosongCodes.includes(code);
      const sub = subjects[(i - 1) % subjects.length];
      const capacity = 35;
      const type = 'Ruang Teori';

      list.push({
        id: `rt-${numStr}`,
        code: code,
        name: `Ruang Teori ${numStr}`,
        building: 'Gedung Sipil',
        floor: floor,
        capacity: capacity,
        type: type,
        status: isKosong ? 'KOSONG' : 'TERPAKAI',
        image: 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=800&q=80',
        facilities: ['Smart TV 65 Inch', '2 Unit AC Split', 'Whiteboard Magnetik', 'Audio System', 'Wi-Fi 100Mbps', 'Colokan Listrik Tiap Meja'],
        currentActivity: isKosong ? null : {
          subject: sub.subject,
          lecturer: sub.lecturer,
          class: sub.class,
          timeRange: '08:00 - 11:30 WIB',
          method: 'Luring di Kelas'
        },
        todaySchedule: [
          { time: '08:00 - 11:30', subject: sub.subject, lecturer: sub.lecturer, class: sub.class, status: isKosong ? 'Kosong' : 'Sedang Berlangsung' },
          { time: '12:30 - 15:00', subject: 'Praktikum Mandiri', lecturer: 'Asisten Lab', class: 'TI - 2F', status: 'Akan Datang' }
        ]
      });
    }
    return list;
  })();

  // Initial Occupancy & Audit Log (Append-Only - KF-09)
  const initialAuditLogs = [
    {
      timestamp: '16 Okt 2026 09:30:15 WIB',
      room: 'RT 01',
      type: 'BUBAR',
      typeLabel: 'Bubar Kelas',
      actor: 'Ketua Kelas 1E (Subari)',
      details: 'Kelas Matematika Komputasi selesai lebih awal. Ruangan diserahkan kembali ke sistem.',
      finalStatus: 'KOSONG (Tersedia)'
    },
    {
      timestamp: '16 Okt 2026 08:05:22 WIB',
      room: 'RT 02',
      type: 'REPORT',
      typeLabel: 'Jurnal Masuk',
      actor: 'Ketua Kelas 2F (Dafa Naufal)',
      details: 'Dosen Dimas Wahyu Wibowo hadir pk 08:00. Materi: Implementasi Desain & Web SIKARA.',
      finalStatus: 'TERPAKAI (Sedang Berlangsung)'
    },
    {
      timestamp: '16 Okt 2026 07:55:00 WIB',
      room: 'Lab RPL 2',
      type: 'REPORT',
      typeLabel: 'Jurnal Masuk',
      actor: 'Ketua Kelas 2F (Dafa Naufal)',
      details: 'Perkuliahan Basis Data Lanjut (Mustika Mentari, M.Kom) dimulai di Lab RPL 2.',
      finalStatus: 'TERPAKAI (Sedang Berlangsung)'
    },
    {
      timestamp: '15 Okt 2026 15:45:10 WIB',
      room: 'Lab RPL 1',
      type: 'BOOKING',
      typeLabel: 'Peminjaman',
      actor: 'HMTI (Jovita Maharani)',
      details: 'Peminjaman untuk Workshop React & State Architecture (Disetujui otomatis tanpa bentrok).',
      finalStatus: 'KOSONG (Booking Selesai)'
    }
  ];

  // ==========================================
  // APPLICATION CONTROLLER STATE
  // ==========================================
  const app = {
    rooms: [...initialRooms],
    auditLogs: [...initialAuditLogs],
    currentUser: {
      name: 'Dafa Naufal Rabbani',
      role: 'ketua',
      roleLabel: 'Ketua Kelas TI-2F',
      email: 'ketua.2f@polinema.ac.id',
      avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80'
    },
    activeView: 'landing',
    activeModal: null,
    selectedRoomId: null,

    // Filter states
    filterKeyword: '',
    filterStatus: 'ALL',
    filterGedung: 'ALL',
    filterLantai: 'ALL',
    filterKapasitas: 'ALL',
    roomPage: 1,
    roomsPerPage: 9,

    /**
     * Initialization routine
     */
    init: function () {
      this.startClock();
      this.renderRoomCards();
      this.updateStatsCounters();
      this.renderSchedulePage();
      this.renderAuditLogs();
      this.populateRoomSelects();
      this.setupLiveValidationListeners();
      this.setupDropdownHandlers();
      const savedAvatar = localStorage.getItem('sikara_user_avatar');
      if (savedAvatar && this.currentUser) {
        this.currentUser.avatar = savedAvatar;
      }
      this.updateNavbarAuth();
      this.setupProfileDropdown();
      console.log('SIKARA Engine initialized successfully.');
    },

    toggleProfileDropdown: function (e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      const menu = document.getElementById('profileDropdownMenu');
      const btn = document.getElementById('userProfileBtn');
      if (!menu) return;
      const isShown = menu.classList.contains('show');
      if (isShown) {
        menu.classList.remove('show');
        if (btn) btn.setAttribute('aria-expanded', 'false');
      } else {
        menu.classList.add('show');
        if (btn) btn.setAttribute('aria-expanded', 'true');
      }
    },

    closeProfileDropdown: function () {
      const menu = document.getElementById('profileDropdownMenu');
      const btn = document.getElementById('userProfileBtn');
      if (menu) menu.classList.remove('show');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    },

    setupProfileDropdown: function () {
      const self = this;
      document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('profileDropdownWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
          self.closeProfileDropdown();
        }
      });
    },

    setupDropdownHandlers: function () {
      const dropdownBtn = document.getElementById('berandaDropdownBtn');
      if (dropdownBtn) {
        dropdownBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          const parent = dropdownBtn.closest('.dropdown');
          if (parent) parent.classList.toggle('show');
        });
        document.addEventListener('click', function () {
          const parent = dropdownBtn.closest('.dropdown');
          if (parent) parent.classList.remove('show');
        });
      }
    },

    /**
     * Real-time Server Clock (WIB)
     */
    startClock: function () {
      const clockEl = document.getElementById('currentTimeClock');
      const dateEl = document.getElementById('currentDateDisplay');
      const update = () => {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        if (clockEl) clockEl.textContent = `${hours}:${minutes}:${seconds}`;

        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', options);
      };
      update();
      setInterval(update, 1000);
    },

    /**
     * Toast notification manager
     */
    showToast: function (message, type = 'info') {
      const container = document.getElementById('toastNotification');
      if (!container) return;

      const toast = document.createElement('div');
      toast.className = `toast-item ${type}`;

      let icon = '';
      if (type === 'success') {
        icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>';
      } else if (type === 'error') {
        icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
      } else if (type === 'warning') {
        icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
      } else {
        icon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
      }

      toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-text">${message}</div>
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 4200);
    },

    /**
     * SPA View Routing
     */
    navigate: function (viewName) {
      document.querySelectorAll('.page-view').forEach(view => view.classList.remove('active'));

      if (viewName === 'activity' && this.currentUser?.role !== 'ketua') {
        this.showToast('Halaman Activity Control hanya tersedia untuk Ketua Kelas.', 'error');
        viewName = 'dashboard';
      }
      const targetView = document.getElementById(
        viewName === 'landing' ? 'viewLanding' :
        viewName === 'login' ? 'viewLogin' :
        viewName === 'forgotPassword' ? 'viewForgotPassword' :
        viewName === 'riwayat' ? 'viewRiwayat' :
        viewName === 'schedule' ? 'viewSchedule' :
        viewName === 'profile' ? 'viewProfile' :
        viewName === 'activity' ? 'viewActivityControl' : 'viewDashboard'
      );

      if (targetView) {
        targetView.classList.add('active');
        this.activeView = viewName;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      // Update Nav active pills
      const navBeranda = document.getElementById('navBeranda');
      const navRuang = document.getElementById('navRuangKelas');
      const navRiwayat = document.getElementById('navRiwayat');
      const navJadwal = document.getElementById('navJadwal');
      const navActivity = document.getElementById('navActivityControl');
      const navProfil = document.getElementById('navProfil');

      if (navBeranda) navBeranda.classList.remove('nav-pill-active');
      if (navRuang) navRuang.classList.remove('nav-pill-active');
      if (navRiwayat) navRiwayat.classList.remove('nav-pill-active');
      if (navJadwal) navJadwal.classList.remove('nav-pill-active');
      if (navActivity) navActivity.classList.remove('nav-pill-active');
      if (navProfil) navProfil.classList.remove('nav-pill-active');

      if (viewName === 'landing') {
        if (navBeranda) navBeranda.classList.add('nav-pill-active');
      } else if (viewName === 'dashboard') {
        if (navRuang) navRuang.classList.add('nav-pill-active');
      } else if (viewName === 'riwayat') {
        if (navRiwayat) navRiwayat.classList.add('nav-pill-active');
      } else if (viewName === 'schedule') {
        if (navJadwal) navJadwal.classList.add('nav-pill-active');
      } else if (viewName === 'activity') {
        if (navActivity) navActivity.classList.add('nav-pill-active');
      } else if (viewName === 'profile') {
        document.getElementById('navProfil')?.classList.add('nav-pill-active');
      }
      if (viewName === 'profile') this.renderUserProfile();
    },

    renderUserProfile: function () {
      const user = this.currentUser;
      if (!user || !['ketua', 'mhs'].includes(user.role)) {
        this.showToast('Halaman profil hanya tersedia untuk Ketua Kelas dan Mahasiswa.', 'error');
        this.navigate('dashboard');
        return;
      }
      const name = user.name || 'Pengguna';
      const roleLabel = user.roleLabel || (user.role === 'ketua' ? 'Ketua Kelas' : 'Mahasiswa');
      const savedAvatar = localStorage.getItem('sikara_user_avatar');
      const avatarSrc = savedAvatar || user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
      user.avatar = avatarSrc;

      const profileAvatarImg = document.getElementById('profileAvatarImg');
      if (profileAvatarImg) profileAvatarImg.src = avatarSrc;

      const profileName = document.getElementById('profileName');
      if (profileName) profileName.textContent = name;

      const profileRole = document.getElementById('profileRole');
      if (profileRole) profileRole.textContent = roleLabel;

      const profileRoleDesc = document.getElementById('profileRoleDescription');
      if (profileRoleDesc) profileRoleDesc.textContent = user.role === 'ketua' ? 'Akun aktif Sistem Informasi Kelas & Ruangan Kampus (SIKARA)' : 'Akun aktif Mahasiswa Polinema (SIKARA)';

      const inputName = document.getElementById('profileInputName');
      if (inputName) inputName.value = name;

      const inputEmail = document.getElementById('profileInputEmail');
      if (inputEmail) inputEmail.value = user.email || (user.role === 'ketua' ? 'ketua.2f@polinema.ac.id' : 'jovita.m@polinema.ac.id');

      const inputNIM = document.getElementById('profileInputNIM');
      if (inputNIM) inputNIM.value = user.nim || (user.role === 'ketua' ? '2341720088' : '2341720102');

      const inputRole = document.getElementById('profileInputRole');
      if (inputRole) inputRole.value = roleLabel;

      // Legacy fallback support
      const oldAvatar = document.getElementById('profileAvatar');
      if (oldAvatar) oldAvatar.textContent = name.trim().charAt(0).toUpperCase();
      const oldDetailName = document.getElementById('profileDetailName');
      if (oldDetailName) oldDetailName.textContent = name;
      const oldEmail = document.getElementById('profileEmail');
      if (oldEmail) oldEmail.textContent = user.email || '—';
      const oldDetailRole = document.getElementById('profileDetailRole');
      if (oldDetailRole) oldDetailRole.textContent = roleLabel;
    },

    handlePhotoUpload: function (event) {
      const file = event.target.files && event.target.files[0];
      if (!file) return;

      if (!file.type.startsWith('image/')) {
        this.showToast('Harap pilih file gambar (JPG, PNG, atau WEBP).', 'error');
        return;
      }

      if (file.size > 3 * 1024 * 1024) {
        this.showToast('Ukuran gambar maksimal 3MB.', 'warning');
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        const dataUrl = e.target.result;
        if (this.currentUser) {
          this.currentUser.avatar = dataUrl;
        }
        localStorage.setItem('sikara_user_avatar', dataUrl);

        // Update profile view image
        const profileImg = document.getElementById('profileAvatarImg');
        if (profileImg) profileImg.src = dataUrl;

        // Update navbar images
        document.querySelectorAll('.profile-avatar-img, .profile-header-avatar img').forEach(img => {
          img.src = dataUrl;
        });

        this.showToast('Foto profil berhasil diperbarui!', 'success');
      };
      reader.readAsDataURL(file);
    },

    resetDefaultPhoto: function () {
      const defaultAvatar = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
      if (this.currentUser) {
        this.currentUser.avatar = defaultAvatar;
      }
      localStorage.removeItem('sikara_user_avatar');

      const profileImg = document.getElementById('profileAvatarImg');
      if (profileImg) profileImg.src = defaultAvatar;

      document.querySelectorAll('.profile-avatar-img, .profile-header-avatar img').forEach(img => {
        img.src = defaultAvatar;
      });

      this.showToast('Foto profil berhasil dikembalikan ke default.', 'info');
    },

    togglePasswordVisibility: function (inputId, btnEl) {
      const input = document.getElementById(inputId);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      if (btnEl) {
        btnEl.innerHTML = isPassword
          ? `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
          : `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
      }
    },

    handlePasswordSubmit: function (event) {
      if (event) event.preventDefault();
      const current = document.getElementById('currentPassword')?.value;
      const newPass = document.getElementById('newPassword')?.value;
      const confirmPass = document.getElementById('confirmPassword')?.value;

      if (!current) {
        this.showToast('Silakan masukkan kata sandi saat ini.', 'warning');
        return;
      }
      if (!newPass || newPass.length < 6) {
        this.showToast('Kata sandi baru minimal 6 karakter.', 'error');
        return;
      }
      if (newPass !== confirmPass) {
        this.showToast('Konfirmasi kata sandi baru tidak cocok.', 'error');
        return;
      }

      document.getElementById('profilePasswordForm')?.reset();
      this.showToast('Kata sandi berhasil diperbarui dengan aman!', 'success');
    },

    handleProfileInfoSubmit: function (event) {
      if (event) event.preventDefault();
      const name = document.getElementById('profileInputName')?.value.trim();
      const email = document.getElementById('profileInputEmail')?.value.trim();

      if (!name || !email) {
        this.showToast('Nama dan Email tidak boleh kosong.', 'error');
        return;
      }

      if (this.currentUser) {
        this.currentUser.name = name;
        this.currentUser.email = email;
      }

      const dropName = document.getElementById('profileDropdownName');
      if (dropName) dropName.textContent = name;
      const profName = document.getElementById('profileName');
      if (profName) profName.textContent = name;

      this.showToast('Informasi profil berhasil diperbarui!', 'success');
    },

    searchRoomsFromLanding: function (event) {
      event.preventDefault();
      const input = document.getElementById('landingRoomSearch');
      const query = input ? input.value.trim() : '';
      this.filterKeyword = query;
      const dashboardInput = document.getElementById('quickSearchInput');
      if (dashboardInput) dashboardInput.value = query;
      const clearBtn = document.getElementById('btnClearSearch');
      if (clearBtn) clearBtn.style.display = query ? 'flex' : 'none';
      this.navigate('dashboard');
      this.renderRoomCards();
    },

    goToKetuaLogin: function () {
      this.navigate('login');
      this.fillDemoAccount('ketua');
    },

    handleForgotPasswordSubmit: function (event) {
      event.preventDefault();
      const email = document.getElementById('recoveryEmail').value.trim();
      const result = document.getElementById('forgotPasswordResult');
      const loginEmail = document.getElementById('loginEmail');
      if (loginEmail) loginEmail.value = email;
      result.hidden = false;
      result.innerHTML = `<strong>Permintaan diterima</strong><p>Untuk keamanan akun, hubungi Unit Administrator Jurusan Teknologi Informasi dengan menyebutkan email <b>${this.escapeHtml(email)}</b>. Administrator akan membantu proses reset kata sandi.</p><button type="button" class="btn-nav-login" onclick="app.navigate('login')">Kembali ke Login</button>`;
    },

    escapeHtml: function (value) {
      return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    },

    /**
     * Auth Management (Ketua Kelas, Admin, Mahasiswa)
     */
    toggleLoginPasswordVisibility: function () {
      const input = document.getElementById('loginPassword');
      const button = document.getElementById('toggleLoginPassword');
      if (!input || !button) return;
      const showPassword = input.type === 'password';
      input.type = showPassword ? 'text' : 'password';
      button.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      button.setAttribute('aria-pressed', String(showPassword));
      button.classList.toggle('is-visible', showPassword);
    },

    fillDemoAccount: function (role) {
      const emailInput = document.getElementById('loginEmail');
      const pwdInput = document.getElementById('loginPassword');
      const chips = document.querySelectorAll('.demo-chips .chip');

      chips.forEach(c => c.classList.remove('active'));

      if (role === 'ketua') {
        emailInput.value = 'ketua.2f@polinema.ac.id';
        pwdInput.value = 'password2f';
        chips[0]?.classList.add('active');
      } else if (role === 'admin') {
        emailInput.value = 'admin.sekjur@polinema.ac.id';
        pwdInput.value = 'adminsecret';
        chips[1]?.classList.add('active');
      } else {
        emailInput.value = 'mahasiswa@polinema.ac.id';
        pwdInput.value = 'mhs12345';
        chips[2]?.classList.add('active');
      }
    },

    handleLoginSubmit: function (e) {
      e.preventDefault();
      const email = document.getElementById('loginEmail').value.trim();

      if (email.includes('admin') || email.includes('sekjur') || email.includes('kajur')) {
        const adminUser = {
          name: 'Administrator JTI',
          role: 'admin',
          roleLabel: 'Admin Jurusan Teknologi Informasi',
          email: email
        };
        sessionStorage.setItem('sikara_user', JSON.stringify(adminUser));
        const adminUrl = window.location.pathname.endsWith('.php') ? 'admin/index.php' : 'admin/index.html';
        window.location.href = adminUrl;
        return;
      } else if (email.includes('ketua') || email.includes('2f')) {
        this.currentUser = {
          name: 'Dafa Naufal Rabbani',
          role: 'ketua',
          roleLabel: 'Ketua Kelas TI-2F',
          email: email
        };
        this.showToast('Login Berhasil! Akses Ketua Kelas 2F aktif. Anda memiliki wewenang Bubar Kelas.', 'success');
      } else {
        this.currentUser = {
          name: 'Jovita Maharani',
          role: 'mhs',
          roleLabel: 'Mahasiswa / Peminjam',
          email: email
        };
        this.showToast('Login Berhasil sebagai Mahasiswa Kampus.', 'success');
      }

      this.updateNavbarAuth();
      this.navigate('dashboard');
      this.renderRoomCards();
    },

    logout: function () {
      this.closeProfileDropdown();
      this.showToast('Fitur logout / keluar akun (Mode Preview Tampilan).', 'info');
    },

    updateNavbarAuth: function () {
      const container = document.getElementById('authNavContainer');
      const ketuaBanner = document.getElementById('ketuaKelasBanner');
      const activityNav = document.getElementById('navActivityControl');
      const profileNav = document.getElementById('navProfil');

      // Tampilkan seluruh menu navbar yang muncul ketika login
      if (activityNav) {
        activityNav.style.display = 'inline-flex';
      }
      if (profileNav) {
        profileNav.style.display = 'inline-flex';
      }

      if (!container) return;

      const user = this.currentUser || {
        name: 'Dafa Naufal Rabbani',
        role: 'ketua',
        roleLabel: 'Ketua Kelas TI-2F',
        email: 'ketua.2f@polinema.ac.id',
        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80'
      };

      // Hanya tampilkan bulet foto profil (tanpa teks nama), klik memunculkan dropdown
      container.innerHTML = `
        <div class="profile-dropdown-wrapper" id="profileDropdownWrapper">
          <button type="button" class="profile-avatar-btn" id="userProfileBtn" aria-label="Menu Profil" aria-expanded="false" onclick="app.toggleProfileDropdown(event)">
            <img src="${user.avatar}" alt="${this.escapeHtml(user.name)}" class="profile-avatar-img">
          </button>
          <div class="profile-dropdown-menu" id="profileDropdownMenu">
            <div class="profile-dropdown-header">
              <div class="profile-header-avatar">
                <img src="${user.avatar}" alt="Avatar">
              </div>
              <div class="profile-header-info">
                <strong class="profile-header-name">${this.escapeHtml(user.name)}</strong>
                <span class="profile-header-role">${this.escapeHtml(user.roleLabel)}</span>
              </div>
            </div>
            <div class="profile-dropdown-divider"></div>
            <a href="#" class="profile-dropdown-item" onclick="app.navigate('profile'); app.closeProfileDropdown(); return false;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span>Profile</span>
            </a>
            <a href="#" class="profile-dropdown-item text-danger" onclick="app.logout(); return false;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Logout</span>
            </a>
          </div>
        </div>
      `;

      if (ketuaBanner) {
        ketuaBanner.style.display = 'flex';
      }
    },

    /**
     * Statistics Counters Calculation
     */
    updateStatsCounters: function () {
      const total = this.rooms.length;
      const inUse = this.rooms.filter(r => r.status === 'TERPAKAI').length;
      const available = this.rooms.filter(r => r.status === 'KOSONG').length;

      const statTotalEl = document.getElementById('statTotalRooms');
      const statInUseEl = document.getElementById('statInUseRooms');
      const statAvailableEl = document.getElementById('statAvailableRooms');

      if (statTotalEl) statTotalEl.textContent = total;
      if (statInUseEl) statInUseEl.textContent = inUse;
      if (statAvailableEl) statAvailableEl.textContent = available;

      // Update landing page metrics chips
      const landingKosongEl = document.getElementById('landingCountKosong');
      const landingTerpakaiEl = document.getElementById('landingCountTerpakai');
      if (landingKosongEl) landingKosongEl.textContent = available;
      if (landingTerpakaiEl) landingTerpakaiEl.textContent = inUse;

      // Update quick tab counts
      const tabs = document.querySelectorAll('.status-tab-btn');
      tabs.forEach(tab => {
        const filter = tab.dataset.filter;
        if (filter === 'ALL') tab.textContent = `Semua (${total})`;
        if (filter === 'KOSONG') tab.textContent = `🟢 Kosong Saja (${available})`;
        if (filter === 'TERPAKAI') tab.textContent = `🔴 Terpakai Saja (${inUse})`;
      });
    },

    /**
     * Render Room Grid Cards (KF-02 & KF-03)
     */
    renderRoomCards: function () {
      const grid = document.getElementById('roomCardsGrid');
      const noResults = document.getElementById('noResultsState');
      const showingText = document.getElementById('showingCountText');
      if (!grid) return;

      const filtered = this.getFilteredRooms();
      const pageCount = Math.max(1, Math.ceil(filtered.length / this.roomsPerPage));
      this.roomPage = Math.min(Math.max(1, this.roomPage), pageCount);
      const startIndex = (this.roomPage - 1) * this.roomsPerPage;
      const pageRooms = filtered.slice(startIndex, startIndex + this.roomsPerPage);

      if (showingText) {
        showingText.textContent = filtered.length
          ? `Menampilkan ${pageRooms.length} dari ${this.rooms.length} Ruangan`
          : `Menampilkan 0 dari ${this.rooms.length} Ruangan`;
      }

      const pagination = document.getElementById('roomPagination');
      if (pagination) pagination.innerHTML = this.renderRoomPagination(pageCount);

      if (filtered.length === 0) {
        grid.innerHTML = '';
        if (noResults) noResults.style.display = 'block';
        return;
      }

      if (noResults) noResults.style.display = 'none';

      grid.innerHTML = pageRooms.map(room => {
        const isTerpakai = room.status === 'TERPAKAI';
        const badgeClass = isTerpakai ? 'terpakai' : 'kosong';

        // Occupancy info panel exactly like reference screenshot
        let occupancyHtml = '';
        if (isTerpakai) {
          occupancyHtml = `
            <div class="card-occupancy-info terpakai">
              <div class="occupancy-title"><span class="card-status-dot red"></span> Saat Aktif : Pemrograman Berbasis Objek</div>
              <div class="occupancy-detail-sub">M. Hasyim, M.Kom. &bull; TI - 2F &bull; 08.00 - 11.30 WIB</div>
            </div>
          `;
        } else {
          occupancyHtml = `
            <div class="card-occupancy-info kosong">
              <div class="occupancy-title"><span class="card-status-dot green"></span> Ruangan Kosong - Siap Digunakan</div>
              <div class="occupancy-detail-sub">M. Hasyim, M.Kom. &bull; TI - 2F &bull; 08.00 - 11.30 WIB</div>
            </div>
          `;
        }

        let actionBtnHtml = '';
        if (!isTerpakai) {
          actionBtnHtml = `
            <button type="button" class="btn-card-action" onclick="app.openBookingModal('${room.code}')">
              <span>Pinjam Ruangan</span>
              <span class="btn-card-arrow">&rsaquo;</span>
            </button>
          `;
        } else {
          actionBtnHtml = `
            <button type="button" class="btn-card-action" onclick="app.openRoomDetail('${room.code}')">
              <span>Cek Jadwal</span>
              <span class="btn-card-arrow">&rsaquo;</span>
            </button>
          `;
        }

        return `
          <div class="room-card ${badgeClass}" data-room-id="${room.id}">
            <div class="card-image-wrap" onclick="app.openRoomDetail('${room.code}')" style="cursor: pointer;">
              <img src="${room.image}" alt="${room.name}" loading="lazy">
            </div>

            <div class="card-body">
              <div class="card-title-group" onclick="app.openRoomDetail('${room.code}')" style="cursor: pointer;">
                <h3 class="room-name">${room.name}</h3>
                <div class="room-location">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  <span>Gedung TS, Lt ${room.floor} &bull; ${room.capacity} Mahasiswa &bull; ${room.type}</span>
                </div>
              </div>

              ${occupancyHtml}

              <div class="card-actions-row">
                <a href="room-detail.html" class="btn-card-detail" onclick="app.openRoomDetail('${room.code}'); return false;">
                  <span>Lihat Detail</span>
                  <span class="btn-card-arrow">&rsaquo;</span>
                </a>
                ${actionBtnHtml}
              </div>
            </div>
          </div>
        `;
      }).join('');
    },

    renderRoomPagination: function (pageCount) {
      if (pageCount <= 1) return '';
      const pageButton = (page, label, disabled = false, active = false) => `
        <button type="button" class="pagination-button${active ? ' active' : ''}" onclick="app.goToRoomPage(${page})" ${disabled ? 'disabled' : ''} aria-label="Halaman ${page}" ${active ? 'aria-current="page"' : ''}>${label}</button>`;
      let pages = '';
      for (let page = 1; page <= pageCount; page++) pages += pageButton(page, page, false, page === this.roomPage);
      return `<nav class="room-pagination" aria-label="Navigasi halaman ruangan">${pageButton(Math.max(1, this.roomPage - 1), '‹ Sebelumnya', this.roomPage === 1)}${pages}${pageButton(Math.min(pageCount, this.roomPage + 1), 'Berikutnya ›', this.roomPage === pageCount)}</nav>`;
    },

    goToRoomPage: function (page) {
      this.roomPage = page;
      this.renderRoomCards();
      document.getElementById('roomCardsGrid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    /**
     * Filter logic
     */
    getFilteredRooms: function () {
      return this.rooms.filter(room => {
        // Keyword Search
        if (this.filterKeyword) {
          const kw = this.filterKeyword.toLowerCase();
          const matchCode = room.code.toLowerCase().includes(kw);
          const matchName = room.name.toLowerCase().includes(kw);
          const matchSubject = room.currentActivity?.subject.toLowerCase().includes(kw);
          const matchLecturer = room.currentActivity?.lecturer.toLowerCase().includes(kw);

          if (!matchCode && !matchName && !matchSubject && !matchLecturer) {
            return false;
          }
        }

        // Status Filter
        if (this.filterStatus !== 'ALL' && room.status !== this.filterStatus) {
          return false;
        }

        // Lantai Filter (5, 6, 7, 8)
        if (this.filterLantai !== 'ALL' && String(room.floor) !== String(this.filterLantai)) {
          return false;
        }

        // Kapasitas Filter
        if (this.filterKapasitas !== 'ALL') {
          const cap = parseInt(this.filterKapasitas);
          if (this.filterKapasitas === 'LT30' && room.capacity >= 30) return false;
          if (this.filterKapasitas === '30-35' && (room.capacity < 30 || room.capacity > 35)) return false;
          if (this.filterKapasitas === 'GT35' && room.capacity <= 35) return false;
          if (!isNaN(cap) && room.capacity < cap) return false;
        }

        return true;
      });
    },

    handleSearchInput: function (val) {
      this.roomPage = 1;
      this.filterKeyword = val.trim();
      const clearBtn = document.getElementById('btnClearSearch');
      if (clearBtn) {
        clearBtn.style.display = this.filterKeyword ? 'flex' : 'none';
      }
      this.renderRoomCards();
    },

    clearSearch: function () {
      this.roomPage = 1;
      const input = document.getElementById('quickSearchInput');
      if (input) input.value = '';
      this.filterKeyword = '';
      const clearBtn = document.getElementById('btnClearSearch');
      if (clearBtn) clearBtn.style.display = 'none';
      this.renderRoomCards();
    },

    applyFilters: function () {
      this.roomPage = 1;
      const statusEl = document.getElementById('filterStatus');
      const lantaiEl = document.getElementById('filterLantai');
      const kapasitasEl = document.getElementById('filterKapasitas');

      if (statusEl) this.filterStatus = statusEl.value;
      if (lantaiEl) this.filterLantai = lantaiEl.value;
      if (kapasitasEl) this.filterKapasitas = kapasitasEl.value;

      // sync quick tabs
      document.querySelectorAll('.status-tab-btn').forEach(btn => {
        const fStatus = btn.dataset.filter;
        const fFloor = btn.dataset.floor;
        if (fStatus) btn.classList.toggle('active', fStatus === this.filterStatus);
        if (fFloor) btn.classList.toggle('active', fFloor === this.filterLantai);
      });

      this.renderRoomCards();
    },

    resetFilters: function () {
      this.roomPage = 1;
      this.filterKeyword = '';
      this.filterStatus = 'ALL';
      this.filterLantai = 'ALL';
      this.filterKapasitas = 'ALL';

      const searchInput = document.getElementById('quickSearchInput');
      if (searchInput) searchInput.value = '';
      const clearBtn = document.getElementById('btnClearSearch');
      if (clearBtn) clearBtn.style.display = 'none';

      const statusEl = document.getElementById('filterStatus');
      const lantaiEl = document.getElementById('filterLantai');
      const kapasitasEl = document.getElementById('filterKapasitas');

      if (statusEl) statusEl.value = 'ALL';
      if (lantaiEl) lantaiEl.value = 'ALL';
      if (kapasitasEl) kapasitasEl.value = 'ALL';

      document.querySelectorAll('.status-tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.filter === 'ALL');
      });

      this.renderRoomCards();
    },

    setQuickFilter: function (type, val, btnEl) {
      this.roomPage = 1;
      if (type === 'all') {
        this.filterStatus = 'ALL';
        this.filterLantai = 'ALL';
        this.filterKapasitas = 'ALL';
        const s = document.getElementById('filterStatus');
        const l = document.getElementById('filterLantai');
        const k = document.getElementById('filterKapasitas');
        if (s) s.value = 'ALL';
        if (l) l.value = 'ALL';
        if (k) k.value = 'ALL';
      } else if (type === 'status') {
        this.filterStatus = val;
        const el = document.getElementById('filterStatus');
        if (el) el.value = val;
      } else if (type === 'floor') {
        this.filterLantai = String(val);
        const el = document.getElementById('filterLantai');
        if (el) el.value = String(val);
      }

      document.querySelectorAll('.status-tab-btn').forEach(b => b.classList.remove('active'));
      if (btnEl) btnEl.classList.add('active');

      this.renderRoomCards();
    },

    setStatusQuickFilter: function (status, btnEl) {
      this.setQuickFilter('status', status, btnEl);
    },

    showLoginRequiredModal: function () {
      let modal = document.getElementById('loginRequiredModal');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'loginRequiredModal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', 'loginRequiredTitle');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
          <!-- Vertically centered modal -->
          <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content">
              <div class="modal-header py-2 px-3">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="loginRequiredTitle">Silakan Login Terlebih Dahulu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body text-center p-3">
                <div class="text-primary fs-3 mb-2"><i class="bi bi-person-lock"></i></div>
                <p class="text-secondary mb-0" style="font-size: 11.5px; line-height: 1.5;">Login sebagai mahasiswa untuk mengajukan peminjaman ruangan. Detail ruang tetap dapat dilihat tanpa login.</p>
              </div>
              <div class="modal-footer py-1.5 px-3">
                <button type="button" class="btn btn-sm btn-secondary" style="font-size: 11px;" data-bs-dismiss="modal">Nanti</button>
                <a class="btn btn-sm btn-primary" style="font-size: 11px;" href="#" onclick="app.navigate('login'); bootstrap.Modal.getInstance(document.getElementById('loginRequiredModal'))?.hide(); return false;">Login sebagai Mahasiswa</a>
              </div>
            </div>
          </div>`;
        document.body.appendChild(modal);
      }
      if (window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
      } else {
        this.navigate('login');
      }
    },

    // ==========================================
    // MODAL 1: ROOM DETAIL MODAL (KF-02, KF-04)
    // ==========================================
    openRoomDetail: function (roomCode) {
      const room = this.rooms.find(r => r.code === roomCode);
      if (!room) return;

      this.selectedRoomId = room.id;

      // Populate elements
      document.getElementById('modalRoomCode').textContent = room.code;
      document.getElementById('modalRoomName').textContent = `${room.name} - ${room.building} Lantai ${room.floor}`;
      document.getElementById('modalRoomImage').src = room.image;
      document.getElementById('modalRoomLocation').textContent = `${room.building} • Lantai ${room.floor}`;
      document.getElementById('modalRoomCapacity').textContent = `${room.capacity} Mahasiswa`;
      document.getElementById('modalRoomType').textContent = room.type;

      // Status Bar
      const statusBar = document.getElementById('modalStatusBar');
      const statusText = document.getElementById('modalStatusText');
      const statusSec = document.getElementById('modalStatusSecondary');

      if (room.status === 'TERPAKAI') {
        statusBar.className = 'room-detail-status-bar terpakai';
        statusText.textContent = 'TERPAKAI (IN-USE)';
        statusSec.textContent = room.currentActivity ?
          `Digunakan oleh ${room.currentActivity.class} (${room.currentActivity.timeRange})` : 'Sedang digunakan untuk perkuliahan';

        document.getElementById('modalRoomSession').textContent = room.currentActivity?.subject || 'Perkuliahan Aktif';
        document.getElementById('modalRoomLecturer').textContent = `${room.currentActivity?.lecturer || '-'} (${room.currentActivity?.class || '-'})`;
      } else {
        statusBar.className = 'room-detail-status-bar kosong';
        statusText.textContent = 'KOSONG (AVAILABLE)';
        statusSec.textContent = 'Ruangan tidak sedang digunakan dan siap dialokasikan.';

        document.getElementById('modalRoomSession').textContent = 'Tidak Ada Sesi Aktif';
        document.getElementById('modalRoomLecturer').textContent = 'Tersedia untuk booking atau perkuliahan pengganti.';
      }

      // Facilities
      const facilityWrap = document.getElementById('modalFacilityBadges');
      facilityWrap.innerHTML = room.facilities.map(f => `
        <span class="facility-pill">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
          ${f}
        </span>
      `).join('');

      // Timeline
      const timelineWrap = document.getElementById('modalTimelineList');
      if (room.todaySchedule && room.todaySchedule.length > 0) {
        timelineWrap.innerHTML = room.todaySchedule.map(item => {
          const isActive = item.status.includes('Sedang');
          const statusClass = isActive ? 'terpakai' : 'kosong';
          return `
            <div class="timeline-item ${isActive ? 'active-now' : ''}">
              <span class="timeline-time">${item.time}</span>
              <div class="timeline-subject">
                <div><strong>${item.subject}</strong> (${item.class})</div>
                <div class="text-muted text-sm">${item.lecturer}</div>
              </div>
              <span class="timeline-status ${statusClass}">${item.status}</span>
            </div>
          `;
        }).join('');
      } else {
        timelineWrap.innerHTML = `<p class="text-muted text-sm" style="padding: 10px 0;">Tidak ada jadwal reguler terdaftar untuk hari ini.</p>`;
      }

      // Action buttons adjustments
      const btnReport = document.getElementById('btnModalReportActivity');
      const btnBooking = document.getElementById('btnModalBooking');

      if (!this.currentUser) {
        btnReport.style.display = 'none';
        btnBooking.style.display = 'inline-flex';
        btnBooking.textContent = 'Login untuk Meminjam';
      } else if (room.status === 'TERPAKAI') {
        btnReport.style.display = 'inline-flex';
        btnBooking.style.display = 'inline-flex';
        btnBooking.textContent = 'Reservasi Jadwal Lain';
      } else {
        btnReport.style.display = 'none';
        btnBooking.style.display = 'inline-flex';
        btnBooking.textContent = 'Peminjaman Ruang Sekarang';
      }

      this.openModal('roomDetailModal');
    },

    openActivityReportFromCurrentRoom: function () {
      const room = this.rooms.find(r => r.id === this.selectedRoomId);
      this.closeModal('roomDetailModal');
      if (room) {
        this.openActivityReport(room.code);
      }
    },

    openBookingModalFromCurrentRoom: function () {
      if (!this.currentUser) {
        this.closeModal('roomDetailModal');
        this.showLoginRequiredModal();
        return;
      }
      const room = this.rooms.find(r => r.id === this.selectedRoomId);
      this.closeModal('roomDetailModal');
      if (room) {
        this.openBookingModal(room.code);
      }
    },

    simulateScanFromModal: function () {
      const room = this.rooms.find(r => r.id === this.selectedRoomId);
      this.closeModal('roomDetailModal');
      this.showToast(`[QR Verified] Berhasil memindai QR Code fisik pintu ${room.code}. Menampilkan detail terverifikasi.`, 'success');
      setTimeout(() => {
        this.openRoomDetail(room.code);
      }, 300);
    },

    // =========================================================================
    // MODAL 2: CLASS ACTIVITY REPORT & MAHASISWA BUBAR (KF-06 & KF-07)
    // =========================================================================
    openTodayClassActivity: function () {
      if (this.currentUser?.role !== 'ketua') {
        this.showToast('Fitur ini hanya tersedia untuk Ketua Kelas.', 'error');
        return;
      }
      const className = this.currentUser.roleLabel.match(/TI-(.+)$/)?.[1]?.trim() || '2F';
      const todaysClasses = [];
      this.rooms.forEach(room => (room.todaySchedule || []).forEach(schedule => {
        if ((schedule.class || '').toUpperCase().includes(className.toUpperCase())) {
          todaysClasses.push({ room, schedule });
        }
      }));
      if (!todaysClasses.length) {
        this.showToast('Tidak ada jadwal kelas Anda hari ini.', 'info');
        return;
      }
      const { room, schedule } = todaysClasses[0];
      this.selectedRoomId = room.id;
      this.openActivityReport(room.code);
      this.navigate('activity');
      const subjectSelect = document.getElementById('reportMataKuliah');
      if (subjectSelect) {
        const option = [...subjectSelect.options].find(item => item.value === schedule.subject);
        if (option) subjectSelect.value = schedule.subject;
      }
      const classInput = document.getElementById('reportClass');
      if (classInput) classInput.value = `TI - ${className} (Jadwal Hari Ini)`;
    },

    openActivityReport: function (roomCode = 'RT 02') {
      const room = this.rooms.find(r => r.code === roomCode) || this.rooms[1];
      this.selectedRoomId = room.id;

      // Update Header & Select
      document.getElementById('reportModalRoomTitle').textContent = `Laporan Aktivitas Ruang ${room.code}`;
      const select = document.getElementById('reportRoomSelect');
      if (select) select.value = room.code;

      // If room has current activity, populate with it
      if (room.currentActivity) {
        const mkSelect = document.getElementById('reportMataKuliah');
        if (mkSelect) {
          for (let opt of mkSelect.options) {
            if (opt.value.toLowerCase().includes(room.currentActivity.subject.toLowerCase()) ||
                room.currentActivity.subject.toLowerCase().includes(opt.value.toLowerCase())) {
              mkSelect.value = opt.value;
              break;
            }
          }
        }
      }

      // Form is rendered as the Activity Control page, not a modal.
      this.navigate('activity');
    },

    handleReportRoomChange: function (code) {
      const room = this.rooms.find(r => r.code === code);
      if (room) {
        this.selectedRoomId = room.id;
        document.getElementById('reportModalRoomTitle').textContent = `Laporan Aktivitas Ruang ${room.code}`;
      }
    },

    /**
     * CRITICAL REQUIREMENT: "MAHASISWA BUBAR" ACTION (KF-07)
     * Overrides schedule, resets room status to KOSONG (Hijau), adds append-only log.
     */
    executeMahasiswaBubar: function () {
      const room = this.rooms.find(r => r.id === this.selectedRoomId);
      if (!room) return;

      const confirmed = window.confirm(`Konfirmasi Mahasiswa Bubar:\n\nApakah mahasiswa benar-benar telah bubar dan mengosongkan ${room.name} (${room.code})?\nStatus ruangan akan segera diubah menjadi KOSONG (HIJAU) dan di-override di seluruh sistem.`);
      if (!confirmed) return;

      // 1. Override room status
      const prevActivity = room.currentActivity ? room.currentActivity.subject : 'Perkuliahan';
      room.status = 'KOSONG';
      room.currentActivity = null;

      // Update schedule status
      if (room.todaySchedule) {
        room.todaySchedule.forEach(s => {
          if (s.status.includes('Sedang')) {
            s.status = 'Selesai Lebih Awal (Bubar)';
          }
        });
      }

      // 2. Append to permanent Audit Log (KF-09)
      const now = new Date();
      const timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
      const logEntry = {
        timestamp: `${now.getDate()} Okt 2026 ${timeStr} WIB`,
        room: room.code,
        type: 'BUBAR',
        typeLabel: 'Mahasiswa Bubar',
        actor: this.currentUser ? `${this.currentUser.name} (${this.currentUser.roleLabel})` : 'Ketua Kelas 2F (Dafa Naufal)',
        details: `Override Jadwal: Kelas ${prevActivity} selesai & mahasiswa bubar pada ${timeStr} WIB. Ruangan siap digunakan pihak lain.`,
        finalStatus: 'KOSONG (Tersedia)'
      };
      this.auditLogs.unshift(logEntry);
      localStorage.setItem('sikara_audit_logs', JSON.stringify(this.auditLogs));

      // 3. Close modal & update views
      this.updateStatsCounters();
      this.renderRoomCards();
      this.renderAuditLogs();

      // 4. Notification feedback
      this.showToast(`Sukses! Mahasiswa Bubar dikonfirmasi. Status ${room.code} kini KOSONG (🟢 Hijau).`, 'success');
    },

    handleActivityFormSubmit: function (e) {
      e.preventDefault();
      const room = this.rooms.find(r => r.id === this.selectedRoomId);
      if (!room) return;

      const mataKuliah = document.getElementById('reportMataKuliah').value;
      const jamMasuk = document.getElementById('reportJamMasuk').value;
      const jamKeluar = document.getElementById('reportJamKeluar').value;
      const jenisAktivitas = document.getElementById('reportJenisAktivitas').value;
      const catatan = document.getElementById('reportCatatanMateri').value;

      // Append log entry
      const now = new Date();
      const logEntry = {
        timestamp: `${now.getDate()} Okt 2026 ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')} WIB`,
        room: room.code,
        type: 'REPORT',
        typeLabel: 'Jurnal Perkuliahan',
        actor: this.currentUser ? `${this.currentUser.name}` : 'Ketua Kelas 2F',
        details: `Jurnal ${mataKuliah} (${jamMasuk} - ${jamKeluar} WIB). Jenis: ${jenisAktivitas}. Catatan: "${catatan.substring(0, 50)}..."`,
        finalStatus: room.status === 'TERPAKAI' ? 'TERPAKAI (Tercatat)' : 'KOSONG (Tercatat)'
      };
      this.auditLogs.unshift(logEntry);
      localStorage.setItem('sikara_audit_logs', JSON.stringify(this.auditLogs));

      this.navigate('dashboard');
      this.renderAuditLogs();
      this.showToast(`Laporan Jurnal Perkuliahan ${room.code} berhasil disimpan ke sistem audit.`, 'success');
    },

    // ==========================================
    // MODAL 3: ROOM BOOKING / RESERVASI (KF-05)
    // ==========================================
    openBookingModal: function (roomCode = null) {
      if (!this.currentUser) {
        this.showLoginRequiredModal();
        return;
      }
      this.populateRoomSelects();
      const select = document.getElementById('bookingRoom');

      if (roomCode && select) {
        select.value = roomCode;
        this.handleBookingRoomChange(roomCode);
      } else if (select && select.options.length > 0) {
        this.handleBookingRoomChange(select.value);
      }

      this.openModal('bookingModal');
    },

    handleBookingRoomChange: function (code) {
      const codeBadge = document.getElementById('bookingModalRoomCode');
      if (codeBadge) codeBadge.textContent = code;
      this.validateBookingOverlap();
    },

    setupLiveValidationListeners: function () {
      const startInput = document.getElementById('bookingTimeStart');
      const endInput = document.getElementById('bookingTimeEnd');
      const dateInput = document.getElementById('bookingDate');

      if (startInput) startInput.addEventListener('input', () => this.validateBookingOverlap());
      if (endInput) endInput.addEventListener('input', () => this.validateBookingOverlap());
      if (dateInput) dateInput.addEventListener('change', () => this.validateBookingOverlap());
    },

    /**
     * Conflict Validation (KF-05)
     */
    validateBookingOverlap: function () {
      const roomSelect = document.getElementById('bookingRoom');
      const startInput = document.getElementById('bookingTimeStart');
      const endInput = document.getElementById('bookingTimeEnd');
      const noticeBox = document.getElementById('bookingValidationNotice');
      const noticeMsg = document.getElementById('bookingValidationMsg');
      const submitBtn = document.getElementById('btnSubmitBooking');

      if (!roomSelect || !noticeBox || !noticeMsg) return;

      const room = this.rooms.find(r => r.code === roomSelect.value);
      if (!room) return;

      const startTime = startInput.value;
      const endTime = endInput.value;

      // Simulated overlap check: if room is currently TERPAKAI and requested time overlaps with active morning window
      if (room.status === 'TERPAKAI' && (startTime < '12:00' && endTime > '08:00')) {
        noticeBox.className = 'validation-status-box conflict';
        noticeMsg.innerHTML = `<strong>Peringatan Bentrok:</strong> Ruang ${room.code} sedang dipakai perkuliahan hingga 11:30 WIB. Pilih jam setelah 12:00 WIB.`;
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.style.opacity = '0.6';
          submitBtn.style.cursor = 'not-allowed';
        }
        return false;
      } else {
        noticeBox.className = 'validation-status-box ok';
        noticeMsg.innerHTML = `Rentang waktu (${startTime} - ${endTime}) tersedia! Tidak ada jadwal tumpang tindih dengan kelas reguler.`;
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
          submitBtn.style.cursor = 'pointer';
        }
        return true;
      }
    },

    handleBookingSubmit: function (e) {
      e.preventDefault();
      const isValid = this.validateBookingOverlap();
      if (!isValid) {
        this.showToast('Gagal mengajukan: Terjadi bentrok jadwal dengan perkuliahan aktif!', 'error');
        return;
      }

      const roomCode = document.getElementById('bookingRoom').value;
      const borrower = document.getElementById('bookingBorrowerName').value;
      const ormawa = document.getElementById('bookingOrganisasi').value;
      const timeStart = document.getElementById('bookingTimeStart').value;
      const timeEnd = document.getElementById('bookingTimeEnd').value;
      const purpose = document.getElementById('bookingPurpose').value;

      const room = this.rooms.find(r => r.code === roomCode);

      // Append log
      const now = new Date();
      const logEntry = {
        timestamp: `${now.getDate()} Okt 2026 ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')} WIB`,
        room: roomCode,
        type: 'BOOKING',
        typeLabel: 'Peminjaman Ruang',
        actor: `${borrower} (${ormawa})`,
        details: `Reservasi ${roomCode} pk ${timeStart} - ${timeEnd} WIB. Keperluan: "${purpose}". Otomatis disetujui.`,
        finalStatus: 'Disetujui Otomatis'
      };
      this.auditLogs.unshift(logEntry);
      localStorage.setItem('sikara_audit_logs', JSON.stringify(this.auditLogs));

      this.closeModal('bookingModal');
      this.renderAuditLogs();
      this.showToast(`Peminjaman ${roomCode} berhasil diajukan dan disetujui otomatis tanpa birokrasi manual.`, 'success');
    },

    // ==========================================
    // MODAL 4: QR CODE SCANNER (KF-04)
    // ==========================================
    openQrScannerModal: function () {
      const listContainer = document.getElementById('qrQuickRoomList');
      if (listContainer) {
        listContainer.innerHTML = this.rooms.map(r => `
          <button type="button" class="btn-qr-pick" onclick="app.executeSimulatedQrScan('${r.code}')">
            Pintu ${r.code} (${r.status})
          </button>
        `).join('');
      }
      this.openModal('qrScannerModal');
    },

    executeSimulatedQrScan: function (roomCode) {
      this.closeModal('qrScannerModal');
      this.showToast(`Memindai QR Code pintu ${roomCode}... Mengarahkan ke detail instan.`, 'info');
      setTimeout(() => {
        this.openRoomDetail(roomCode);
      }, 500);
    },

    // ==========================================
    // MODAL 5: JADWAL KELAS MASTER
    // ==========================================
    // VIEW: JADWAL KELAS (Matching Image 2)
    // ==========================================
    renderSchedulePage: function () {
      const tbody = document.getElementById('masterScheduleTableBody');
      if (!tbody) return;

      const roomSelect = document.getElementById('scheduleRoomFilter');
      if (roomSelect && roomSelect.options.length <= 1) {
        for (let i = 1; i <= 36; i++) {
          const num = String(i).padStart(2, '0');
          roomSelect.add(new Option(`RT ${num}`, `RT ${num}`));
        }
      }

      // Default entries matching Image 2
      const defaultList = [];
      for (let i = 1; i <= 15; i++) {
        defaultList.push({
          time: '07:00 - 09:30',
          room: 'RT 01',
          subject: 'Metode Numerik',
          lecturer: 'Drs. Subari, M.Kom.',
          class: '1E',
          status: 'Selesai'
        });
      }

      tbody.innerHTML = defaultList.map(item => `
        <tr>
          <td><strong>${item.time}</strong></td>
          <td><span class="room-code-badge">${item.room}</span></td>
          <td>${item.subject}</td>
          <td>${item.lecturer}</td>
          <td>${item.class}</td>
          <td><span class="schedule-status-badge">${item.status}</span></td>
        </tr>
      `).join('');
    },

    // ==========================================
    // VIEW 3: RIWAYAT / OCCUPANCY LOG (KF-09)
    // ==========================================
    renderAuditLogs: function () {
      const tbody = document.getElementById('auditLogTableBody');
      if (!tbody) return;

      const typeFilter = document.getElementById('logFilterType')?.value || 'ALL';
      const roomFilter = document.getElementById('logFilterRoom')?.value || 'ALL';
      const searchKw = document.getElementById('logSearchKeyword')?.value.trim().toLowerCase() || '';

      const filtered = this.auditLogs.filter(item => {
        if (typeFilter !== 'ALL' && item.type !== typeFilter) return false;
        if (roomFilter !== 'ALL' && item.room !== roomFilter) return false;
        if (searchKw) {
          const matchActor = item.actor.toLowerCase().includes(searchKw);
          const matchDetails = item.details.toLowerCase().includes(searchKw);
          const matchRoom = item.room.toLowerCase().includes(searchKw);
          if (!matchActor && !matchDetails && !matchRoom) return false;
        }
        return true;
      });

      if (filtered.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
              Tidak ada catatan riwayat yang cocok dengan filter.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = filtered.map(log => {
        let badgeTypeClass = 'report';
        if (log.type === 'BUBAR') badgeTypeClass = 'bubar';
        if (log.type === 'BOOKING') badgeTypeClass = 'booking';

        return `
          <tr>
            <td style="font-size: 12px; font-variant-numeric: tabular-nums;"><strong>${log.timestamp}</strong></td>
            <td><span class="room-code-badge">${log.room}</span></td>
            <td><span class="log-badge ${badgeTypeClass}">${log.typeLabel}</span></td>
            <td><strong>${log.actor}</strong></td>
            <td style="max-width: 380px;">${log.details}</td>
            <td><span style="font-weight: 700; color: ${log.finalStatus.includes('KOSONG') ? 'var(--green-dark)' : 'var(--red-dark)'};">${log.finalStatus}</span></td>
          </tr>
        `;
      }).join('');
    },

    filterAuditLogs: function () {
      this.renderAuditLogs();
    },

    // ==========================================
    // UTILITY & MODAL HELPERS
    // ==========================================
    populateRoomSelects: function () {
      const reportSelect = document.getElementById('reportRoomSelect');
      const bookingSelect = document.getElementById('bookingRoom');

      if (reportSelect) {
        reportSelect.innerHTML = this.rooms.map(r => `
          <option value="${r.code}">${r.code} - ${r.name} (${r.status})</option>
        `).join('');
      }

      if (bookingSelect) {
        bookingSelect.innerHTML = this.rooms.map(r => `
          <option value="${r.code}">${r.code} - ${r.name} (${r.building} Lt ${r.floor})</option>
        `).join('');
      }
    },

    openModal: function (modalId) {
      const modal = document.getElementById(modalId);
      if (!modal) return;
      if (window.bootstrap?.Modal) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
      } else {
        modal.classList.add('show');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
      }
      this.activeModal = modalId;
    },

    closeModal: function (modalId) {
      const modal = document.getElementById(modalId);
      if (!modal) return;
      if (window.bootstrap?.Modal) {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
          bsModal.hide();
        } else {
          modal.classList.remove('show');
          modal.style.display = 'none';
        }
      } else {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
      this.activeModal = null;
    },

    handleModalBackdropClick: function (e, modalId) {
      if (e.target.id === modalId) {
        this.closeModal(modalId);
      }
    }
  };

  // Expose to window for inline onclick handlers
  window.app = app;

  // Initialize on DOM ready
  document.addEventListener('DOMContentLoaded', function () {
    app.init();
  });

})();

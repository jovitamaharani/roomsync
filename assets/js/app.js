/**
 * SIMORA - Sistem Informasi Ketersediaan dan Alokasi Ruang
 * Frontend helper (UI saja). Data, auth, filter, dan validasi ditangani PHP.
 * PBL RoomSync - JTI POLINEMA 2026
 */

(function () {
  'use strict';

  const app = {
    activeModal: null,

    init: function () {
      this.startClock();
      this.setupDropdownHandlers();
      this.setupProfileDropdown();
    },

    // ==========================================
    // PROFILE DROPDOWN (navbar)
    // ==========================================
    toggleProfileDropdown: function (e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }
      const menu = document.getElementById('profileDropdownMenu');
      const btn = document.getElementById('userProfileBtn');
      if (!menu) return;
      const show = !menu.classList.contains('show');
      menu.classList.toggle('show', show);
      if (btn) btn.setAttribute('aria-expanded', String(show));
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
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') self.closeProfileDropdown();
      });
    },

    setupDropdownHandlers: function () {
      const dropdownBtn = document.getElementById('berandaDropdownBtn');
      if (!dropdownBtn) return;
      dropdownBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const parent = dropdownBtn.closest('.dropdown');
        if (parent) parent.classList.toggle('show');
      });
      document.addEventListener('click', function () {
        const parent = dropdownBtn.closest('.dropdown');
        if (parent) parent.classList.remove('show');
      });
    },

    // ==========================================
    // JAM REAL-TIME (tampilan saja)
    // ==========================================
    startClock: function () {
      const clockEl = document.getElementById('currentTimeClock');
      const dateEl = document.getElementById('currentDateDisplay');
      if (!clockEl && !dateEl) return;

      const update = () => {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        if (clockEl) clockEl.textContent = `${hh}:${mm}:${ss}`;
        if (dateEl) {
          dateEl.textContent = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
          });
        }
      };
      update();
      setInterval(update, 1000);
    },

    // ==========================================
    // TOAST (dipakai juga untuk flash message PHP)
    // ==========================================
    showToast: function (message, type = 'info') {
      let container = document.getElementById('toastNotification');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toastNotification';
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
      }

      const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
      };

      const toast = document.createElement('div');
      toast.className = `toast-item ${type}`;

      const iconEl = document.createElement('div');
      iconEl.className = 'toast-icon';
      iconEl.innerHTML = icons[type] || icons.info; // ikon statis, aman

      const textEl = document.createElement('div');
      textEl.className = 'toast-text';
      textEl.textContent = message; // textContent: pesan dari server tidak bisa menyisipkan HTML

      toast.append(iconEl, textEl);
      container.appendChild(toast);

      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
      }, 4200);
    },

    // ==========================================
    // FORM HELPER (tampilan saja)
    // ==========================================
    togglePasswordVisibility: function (inputId, btnEl) {
      const input = document.getElementById(inputId);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      if (btnEl) {
        btnEl.innerHTML = isPassword
          ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
          : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
      }
    },

    toggleLoginPasswordVisibility: function () {
      const input = document.getElementById('loginPassword');
      const button = document.getElementById('toggleLoginPassword');
      if (!input || !button) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      button.setAttribute('aria-pressed', String(show));
      button.classList.toggle('is-visible', show);
    },

    // ==========================================
    // MODAL (opsional; boleh diganti data-bs-toggle)
    // ==========================================
    openModal: function (modalId) {
      const modal = document.getElementById(modalId);
      if (!modal || !window.bootstrap?.Modal) return;
      bootstrap.Modal.getOrCreateInstance(modal).show();
      this.activeModal = modalId;
    },

    closeModal: function (modalId) {
      const modal = document.getElementById(modalId);
      if (!modal || !window.bootstrap?.Modal) return;
      bootstrap.Modal.getInstance(modal)?.hide();
      this.activeModal = null;
    }
  };

  // Diekspos untuk atribut inline onclick di PHP
  window.app = app;

  document.addEventListener('DOMContentLoaded', function () {
    app.init();
  });
})();
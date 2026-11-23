/**
 * Halaman Profil Pengguna: pilih foto (validasi lalu kirim otomatis) dan pengecekan kata sandi.
 * Dimuat dari app/Views/user/profile/index.php.
 */
document.addEventListener('DOMContentLoaded', function () {
  const notify = function (message) {
    if (window.app && typeof window.app.showToast === 'function') {
      window.app.showToast(message, 'error');
    } else {
      alert(message);
    }
  };

  // ---------- Foto profil ----------
  const photoInput = document.getElementById('profilePhotoInput');
  const photoForm = document.getElementById('profilePhotoForm');
  const MAX_BYTES = 3 * 1024 * 1024;
  const TYPES = ['image/jpeg', 'image/png', 'image/webp'];

  document.querySelectorAll('[data-photo-trigger]').forEach(function (btn) {
    btn.addEventListener('click', function () { if (photoInput) photoInput.click(); });
  });

  if (photoInput && photoForm) {
    photoInput.addEventListener('change', function () {
      const file = photoInput.files[0];
      if (!file) return;
      if (TYPES.indexOf(file.type) === -1) {
        notify('Format foto harus JPG, PNG, atau WEBP.');
        photoInput.value = '';
        return;
      }
      if (file.size > MAX_BYTES) {
        notify('Ukuran foto maksimal 3 MB.');
        photoInput.value = '';
        return;
      }
      photoForm.submit();   // server memeriksa ulang isi dan ukuran file
    });
  }

  // ---------- Kata sandi: baru dan konfirmasi harus sama, validasi dua kali di server dan client ----------
  const pwForm = document.getElementById('profilePasswordForm');
  if (pwForm) {
    const current = document.getElementById('currentPassword');
    const next = document.getElementById('newPassword');
    const confirmInput = document.getElementById('confirmPassword');

    const validate = function () {
      if (next.value.length < 8) return 'Kata sandi baru minimal 8 karakter.';
      if (next.value.length > 72) return 'Kata sandi baru maksimal 72 karakter.';
      if (!/[A-Za-z]/.test(next.value) || !/\d/.test(next.value)) return 'Kata sandi baru harus berisi huruf dan angka.';
      if (next.value === current.value) return 'Kata sandi baru tidak boleh sama dengan yang lama.';
      if (next.value !== confirmInput.value) return 'Konfirmasi kata sandi baru tidak cocok.';
      return '';
    };

    pwForm.addEventListener('submit', function (e) {
      const message = validate();
      if (message) {
        e.preventDefault();
        notify(message);
        return;
      }
      const btn = pwForm.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;   // cegah kirim dua kali
    });
  }
});
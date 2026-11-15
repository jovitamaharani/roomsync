<?php
$pageTitle = 'Ubah Password - RIMORA';
$email = $email ?? 'email-akun-anda';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/login.css">
</head>

<body class="password-page">

    <main class="password-card reset-password-card">

        <div class="password-icon">
            <i class="bi bi-lock"></i>
        </div>

        <h1>Ubah Kata Sandi</h1>

        <p class="password-description">
            Atur ulang kata sandi untuk akun
            <strong><?= htmlspecialchars($email); ?></strong>.
            Pastikan kata sandi baru Anda aman dan mudah diingat.
        </p>

        <form action="#" method="POST">

            <!-- KATA SANDI BARU -->
            <div class="mb-3">
                <label for="newPassword" class="form-label">
                    Kata Sandi Baru
                </label>

                <div class="password-reset-field">

                    <span class="password-lock">
                        <i class="bi bi-lock"></i>
                    </span>

                    <input type="password" id="newPassword" name="new_password" class="form-control"
                        placeholder="Masukkan Kata Sandi Baru" minlength="8" required>

                    <button type="button" class="toggle-reset-password" data-target="newPassword"
                        aria-label="Tampilkan password" aria-pressed="false">
                        <span class="password-eye-icon">
                            <!-- SVG MATA TERTUTUP -->
                            <svg viewBox="0 0 32 32" aria-hidden="true">
                                <path d="M3 17 Q8 26 16 26 Q24 26 29 17" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" />
                                <path d="M7 22 L5 25 M12 25 L11 29 M20 25 L21 29 M25 22 L27 25" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </span>
                    </button>

                </div>
            </div>

            <!-- KONFIRMASI KATA SANDI -->
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">
                    Konfirmasi Kata Sandi Baru
                </label>

                <div class="password-reset-field">

                    <span class="password-lock">
                        <i class="bi bi-lock"></i>
                    </span>

                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control"
                        placeholder="Konfirmasi Kata Sandi Baru" minlength="8" required>

                    <button type="button" class="toggle-reset-password" data-target="confirmPassword"
                        aria-label="Tampilkan password" aria-pressed="false">
                        <span class="password-eye-icon">
                            <!-- SVG MATA TERTUTUP -->
                            <svg viewBox="0 0 32 32" aria-hidden="true">
                                <path d="M3 17 Q8 26 16 26 Q24 26 29 17" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" />
                                <path d="M7 22 L5 25 M12 25 L11 29 M20 25 L21 29 M25 22 L27 25" fill="none"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </span>
                    </button>

                </div>
            </div>

            <!-- ATURAN PASSWORD -->
            <ul class="password-rules">
                <li>Minimal 8 Karakter</li>
                <li>Pastikan Kedua Kata Sandi Sama Persis</li>
            </ul>

            <!-- TOMBOL SIMPAN -->
            <button type="submit" class="btn-password-primary">
                Simpan Kata Sandi Baru
                <i class="bi bi-check-lg ms-1"></i>
            </button>

        </form>

    </main>

    <script src="/assets/auth/reset_password.js"></script>

</body>

</html>
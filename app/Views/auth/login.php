<?php
$flash = $_SESSION['flash'] ?? null;
$oldEmail = $_SESSION['old_email'] ?? '';
unset($_SESSION['flash'], $_SESSION['old_email']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIMORA - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <main class="login-page container-fluid d-flex justify-content-center align-items-center py-4 px-3">
        <div class="login-card d-grid">
            <section
                class="brand-panel position-relative d-flex flex-column justify-content-center align-items-center text-center p-4 text-white">
                <div class="brand-content w-100">
                    <div class="brand-icon mb-3" aria-hidden="true">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <h1 class="mb-3">RIMORA</h1>
                    <p class="mb-0">
                        Pantau ketersediaan ruangan, cek jadwal, dan kelola penggunaan kelas dengan mudah dan cepat.
                    </p>
                </div>

                <small class="brand-footer">
                    Sistem Monitoring Ruang · PBL 2026
                </small>
            </section>

            <section class="form-panel d-flex align-items-center p-4">
                <form class="w-100" action="index.php?controller=auth&action=authenticate" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()); ?>">
                    <h2 class="mb-1">Masuk ke Akun</h2>
                    <p class="form-description mb-4">
                        Silakan masukkan kredensial Anda untuk melanjutkan.
                    </p>
                    <?php if ($flash): ?>
                        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success'; ?> small py-2 px-3">
                            <?= htmlspecialchars($flash['message']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Pengguna</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Masukkan Email" value="<?= htmlspecialchars($oldEmail); ?>"
                                autocomplete="username" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock" aria-hidden="true"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Masukkan Kata Sandi" autocomplete="current-password" required>
                        </div>
                    </div>
                    <div class="form-options d-flex justify-content-between align-items-center gap-2 mt-4 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ingatSaya">
                            <label class="form-check-label" for="ingatSaya">Ingat saya</label>
                        </div>
                        <a href="#" class="forgot-link">Lupa Kata Sandi?</a>
                    </div>
                    <button type="submit" class="btn btn-login w-100 rounded-3">Masuk
                        <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>

</html>
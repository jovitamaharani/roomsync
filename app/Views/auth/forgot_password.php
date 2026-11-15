<?php
$pageTitle = 'Lupa Password - RIMORA';
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

    <main class="password-card">
        <div class="password-icon">
            <i class="bi bi-lock"></i>
        </div>

        <h1>Lupa Kata Sandi?</h1>
        <p class="password-description">
            Masukkan email akun RIMORA. Kami akan
            menampilkan petunjuk pemulihan yang sesuai.
        </p>

        <form action="#" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>

                <div class="password-input-group">
                    <span class="input-icon">
                        <i class="bi bi-envelope"></i>
                    </span>

                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email"
                        autocomplete="email" required>
                </div>
            </div>

            <button type="submit" class="btn-password-primary">
                Lanjutkan
                <i class="bi bi-box-arrow-in-right ms-1"></i>
            </button>
        </form>
    </main>

</body>

</html>
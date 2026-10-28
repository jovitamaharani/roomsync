<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIMORA - Beranda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <h1>landing page</h1>

    <nav>
        <a href="index.php?controller=role&action=index">Role</a> |
        <a href="index.php?controller=academic_year&action=index">Tahun Ajaran</a>
    </nav>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); ?>
        <p><?= htmlspecialchars($flash['message']); ?></p>
    <?php endif; ?>

    <?php if ($user): ?>
        <span><?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['role']); ?>)</span>
        <form action="index.php?controller=auth&action=logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()); ?>">
            <button type="submit">Keluar</button>
        </form>
    <?php else: ?>
        <a href=" index.php?controller=auth&action=login">Masuk</a>
    <?php endif; ?>


    <nav class="navbar navbar-expand-lg bg-white">
        <div class="container navbar-container">

            <a class="navbar-brand d-flex align-items-center gap-2" href="beranda.html">
                <span class="brand-logo">
                    <i class="bi bi-house-door-fill"></i>
                </span>
                <span class="brand-name">RIMORA</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
                aria-controls="navbarMenu" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="beranda.html">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../RuangKelas/index.html">Ruang Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../JadwalKelas/index.html">Jadwal Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../Riwayat/index.html">Riwayat</a>
                    </li>
                </ul>

                <a href="../Login/login.html" class="btn btn-login">LOGIN</a>
            </div>

        </div>
    </nav>

    <main class="home-page">
        <section class="hero-section container">
            <div class="hero-content">

                <div class="hero-text">
                    <h1>Pantau<br>Ruang Kelas</h1>

                    <p class="hero-description">
                        Pantau ketersediaan ruangan secara real-time,
                        cek jadwal kuliah, dan kelola lokasi kelas
                        kampus dengan akurat dan cepat bersama RIMORA!
                    </p>

                    <form class="search-form d-flex" action="#" method="get">
                        <i class="bi bi-search search-icon" aria-hidden="true"></i>

                        <input type="search" name="q" id="cariRuang" class="form-control"
                            placeholder="Cari nama ruang atau nama kelas..."
                            aria-label="Cari nama ruang atau nama kelas">

                        <button type="submit" class="btn btn-search">Cari</button>
                    </form>

                    <div class="hero-actions d-flex flex-wrap gap-2">
                        <a href="../RuangKelas/index.html" class="btn btn-light hero-button">
                            <i class="bi bi-grid"></i>
                            Jelajahi Ruang Kelas
                        </a>

                        <a href="../JadwalKelas/index.html" class="btn btn-light hero-button">
                            <i class="bi bi-calendar3"></i>
                            Lihat Jadwal Kuliah
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <section class="benefit-section container text-center">
            <h2>Optimalkan Pemanfaatan<br>Ruang Kelas Kampus</h2>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
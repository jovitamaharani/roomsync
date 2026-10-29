<?php
$activeAdminPage = 'overview';
$pageTitle = 'Ringkasan - Admin JTI';
$pageHeading = 'Ringkasan';

include __DIR__ . '/../components/header.php';
include __DIR__ . '/../components/sidebar.php';
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<!-- MAIN CONTENT -->
<main class="main-area">

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <!-- TOAST NOTIFICATION -->
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <!-- RINGKASAN OVERVIEW -->
        <div class="admin-page active" id="page-overview">

            <div class="welcome-panel">
                <div>
                    <span class="welcome-kicker">
                        SISTEM ADMINISTRASI AKADEMIK
                    </span>

                    <h2>Pengelolaan ruang dan perkuliahan JTI</h2>

                    <p>
                        Kelola data akademik, jadwal kelas, aktivitas ruang,
                        dan peminjaman dalam satu tempat.
                    </p>
                </div>

                <div class="welcome-art">
                    <i class="bi bi-buildings"></i>
                </div>
            </div>

            <div class="row g-3 mb-4" id="summaryCards"></div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="content-card today-schedule-card">

                        <div class="card-heading">
                            <div>
                                <h3>Jadwal Hari Ini</h3>
                                <p>Aktivitas perkuliahan yang tercatat</p>
                            </div>

                            <a href="<?= adminNavUrl('schedule') ?>" class="btn btn-sm btn-outline-primary">
                                Lihat jadwal &rarr;
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table id="todayTable"
                                class="display table table-striped table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Mata kuliah</th>
                                        <th>Kelas</th>
                                        <th>Ruang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </section>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

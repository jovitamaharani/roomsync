<?php
$activeAdminPage = 'bookings';
$pageTitle = 'Riwayat Peminjaman - Admin JTI';
$pageHeading = 'Riwayat Peminjaman';

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

        <div class="admin-page active" id="page-bookings">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Riwayat Peminjaman</h3>
                        <p>Catatan peminjaman ruang kelas dan status verifikasinya.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="bookingsTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Ruang</th>
                                <th>Peminjam</th>
                                <th>Detail Peminjaman</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

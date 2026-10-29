<?php
$activeAdminPage = 'activity';
$pageTitle = 'Jurnal Activity Control - Admin JTI';
$pageHeading = 'Jurnal Activity Control';

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

        <div class="admin-page active" id="page-activity">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Jurnal Activity Control</h3>
                        <p>Catatan aktivitas penggunaan ruang kelas yang dilaporkan ketua kelas dan dosen.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="activityTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Ruang</th>
                                <th>Jenis Aktivitas</th>
                                <th>Pelapor</th>
                                <th>Detail</th>
                                <th>Status Akhir</th>
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

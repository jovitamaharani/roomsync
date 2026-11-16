<?php
$activeAdminPage = 'activity';
$pageTitle = 'Jurnal Activity Control - Admin JTI';
$pageHeading = 'Jurnal Activity Control';

// Halaman ini hanya boleh dimuat lewat controller
if (!isset($reports)) {
    http_response_code(404);
    exit('Buka halaman ini lewat router: index.php?controller=history_activity_control&action=index');
}

$h    = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$hhmm = fn($t) => $t === null || $t === '' ? '-' : substr((string) $t, 0, 5);

$modeLabel  = ['offline' => 'Offline', 'hybrid' => 'Hybrid', 'online' => 'Daring'];
$toneClass  = ['success' => 'bg-success', 'warning' => 'bg-warning text-dark', 'secondary' => 'bg-secondary'];

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
                        <p class="text-muted small mb-0">
                            Menampilkan laporan <?= $h(date('d/m/Y', strtotime($from))) ?>
                            sampai <?= $h(date('d/m/Y', strtotime($to))) ?>.
                        </p>
                    </div>
                </div>

                <?php if (!empty($loadError)): ?>
                    <div class="alert alert-warning mb-3"><?= $h($loadError) ?></div>
                <?php endif; ?>

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
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <tr>
                                    <td data-order="<?= $h($r['report_date'] . ' ' . $r['start_time']) ?>">
                                        <span class="fw-semibold"><?= $h(date('d/m/Y', strtotime($r['report_date']))) ?></span><br>
                                        <small class="text-muted"><?= $h($hhmm($r['start_time'])) ?> - <?= $h($hhmm($r['end_time'])) ?></small>
                                    </td>
                                    <td>
                                        <?= $h($r['room_name']) ?><br>
                                        <small class="text-muted"><?= $h($r['building']) ?></small>
                                    </td>
                                    <td>
                                        <?= $h($r['activity_type']) ?><br>
                                        <small class="text-muted"><?= $h($modeLabel[$r['mode']] ?? $r['mode']) ?></small>
                                    </td>
                                    <td>
                                        <?= $h($r['reporter']) ?><br>
                                        <small class="text-muted"><?= $h($r['class_name']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= $h($r['course']) ?>
                                            <small class="text-muted">(<?= $h($r['kode_mk']) ?>)</small>
                                        </div>
                                        <div class="small text-muted"><?= $h($r['lecturer']) ?></div>
                                        <div class="small">
                                            Dosen masuk <?= $h($hhmm($r['lecturer_enter'])) ?>,
                                            keluar <?= $h($hhmm($r['lecturer_leave'])) ?>
                                            (<?= $h(Report::LECTURER_STATUS[$r['lecturer_attendance']] ?? '-') ?>)
                                        </div>
                                        <div class="small">
                                            Mahasiswa hadir: <?= $r['student_attendance'] === null ? '-' : (int) $r['student_attendance'] ?>
                                        </div>
                                        <?php if (trim((string) $r['description']) !== ''): ?>
                                            <details class="small mt-1">
                                                <summary>Materi dan catatan</summary>
                                                <div class="mt-1"><?= nl2br($h($r['description'])) ?></div>
                                            </details>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $h($toneClass[$r['final_tone']] ?? 'bg-secondary') ?>">
                                            <?= $h($r['final_label']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- JAVASCRIPT DATATABLES -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initActivityTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initActivityTable, 50);
                    return;
                }
                if (document.getElementById('activityTable') && !DataTable.isDataTable('#activityTable')) {
                    new DataTable('#activityTable', {
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        order: [[0, 'desc']],                         // terbaru dulu (memakai data-order)
                        columnDefs: [{ orderable: false, targets: 4 }], // kolom Detail
                        layout: {
                            topStart: {
                                buttons: [
                                    { extend: "copy",  text: '<i class="bi bi-clipboard me-1"></i>Copy' },
                                    { extend: "excel", text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel' },
                                    { extend: "pdf",   text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF' },
                                    { extend: "print", text: '<i class="bi bi-printer me-1"></i>Print' }
                                ]
                            },
                            topEnd: "search",
                            bottomStart: "info",
                            bottomEnd: "paging"
                        },
                        language: {
                            search: "Search:",
                            searchPlaceholder: "Search records...",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "Showing 0 to 0 of 0 entries",
                            zeroRecords: "No matching records found",
                            emptyTable: "Belum ada laporan pada rentang tanggal ini",
                            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                        }
                    });
                }
            }
            initActivityTable();
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
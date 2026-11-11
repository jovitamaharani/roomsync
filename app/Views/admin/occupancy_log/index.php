<?php
$activeAdminPage = 'bookings';
$pageTitle = 'Riwayat Peminjaman - Admin JTI';
$pageHeading = 'Riwayat Peminjaman';

include __DIR__ . '/../components/header.php';
include __DIR__ . '/../components/sidebar.php';

$isTrue = static fn($v): bool => in_array($v, [true, 't', 'true', 1, '1'], true);
$formatDate = static fn($v): string => $v ? date('d/m/Y', strtotime((string) $v)) : '-';
$formatTime = static fn($v): string => $v ? date('H:i', strtotime((string) $v)) : '-';

$logs = $logs ?? [];
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<main class="main-area">
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3"></div>

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
                        <tbody>
                            <?php foreach ($logs as $log):
                                $ongoing = $isTrue($log['is_ongoing'] ?? false);
                                $timeRange = $formatTime($log['start_time'] ?? null) . ' - ' . $formatTime($log['end_time'] ?? null);
                                ?>
                                <tr>
                                    <td data-order="<?= $e($log['start_time']) ?>">
                                        <div>
                                            <strong><?= $e($formatDate($log['usage_date'] ?? $log['start_time'])) ?></strong>
                                        </div>
                                        <small class="text-muted"><?= $e($timeRange) ?></small>
                                    </td>
                                    <td>
                                        <?= $e($log['room'] ?? '-') ?>
                                        <?php if (!empty($log['building'])): ?>
                                            <small class="text-muted d-block"><?= $e($log['building']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $e($log['user_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success-emphasis mb-1">Booking</span>
                                        <div><?= $e(!empty($log['purpose']) ? $log['purpose'] : '-') ?></div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge <?= $ongoing ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                            <?= $ongoing ? 'Berlangsung' : 'Selesai' ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableEl = document.getElementById('bookingsTable');
            if (typeof DataTable === 'undefined' || !tableEl) return;

            new DataTable('#bookingsTable', {
                order: [[0, 'desc']],
                pageLength: 10,
                lengthChange: false,
                autoWidth: false,
                layout: {
                    topStart: {
                        buttons: [
                            {
                                extend: "copy",
                                text: '<i class="bi bi-clipboard me-1"></i>Copy',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "excel",
                                text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "pdf",
                                text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',
                                exportOptions: { columns: ':not(:last-child)' }
                            },
                            {
                                extend: "print",
                                text: '<i class="bi bi-printer me-1"></i>Print',
                                exportOptions: { columns: ':not(:last-child)' }
                            }
                        ]
                    },
                    topEnd: "search",
                    bottomStart: "info",
                    bottomEnd: "paging"
                },
                language: {
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    search: 'Search:',
                    searchPlaceholder: 'Search records...',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                }
            });
        });
    </script>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</main>
<?php
$activeAdminPage = 'activity_types';
$pageTitle = 'Jenis Aktivitas - Admin JTI';
$pageHeading = 'Daftar Jenis Aktivitas';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

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
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-activity-types">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Jenis Aktivitas</h3>
                        <p>Manajemen data jenis aktivitas.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahActivityType">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Jenis Aktivitas
                        </button>
                    </div>
                </div>

                <!-- ALERT NOTIFIKASI FLASH -->
                <?php if (!empty($flash)): ?>
                    <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show mb-3" role="alert">
                        <i class="bi <?= ($flash['type'] ?? '') === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle' ?> me-2"></i>
                        <?= htmlspecialchars($flash['message'] ?? ''); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="activityTypesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th style="width: 320px;">ID (UUID)</th>
                                <th>Nama Jenis Aktivitas</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($activityTypes)): ?>
                                <?php foreach ($activityTypes as $item): ?>
                                    <tr>
                                        <td>
                                            <code class="text-secondary small"><?= htmlspecialchars($item['id'] ?? ''); ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($item['name'] ?? ''); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditActivityType"
                                                data-id="<?= htmlspecialchars($item['id'] ?? ''); ?>"
                                                data-name="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES); ?>">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalHapusActivityType"
                                                data-id="<?= htmlspecialchars($item['id'] ?? ''); ?>"
                                                data-name="<?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES); ?>">
                                                <i class="bi bi-trash3 me-1"></i>Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH JENIS AKTIVITAS -->
    <div class="modal fade" id="modalTambahActivityType" tabindex="-1" aria-labelledby="modalTambahActivityTypeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahActivityTypeLabel">Tambah Jenis Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahActivityType" action="index.php?controller=activity_types&action=store" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaActivityType" class="form-label">Nama Jenis Aktivitas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="namaActivityType" name="name" placeholder="Contoh: Kuliah, Praktikum, Ujian" required maxlength="50">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT JENIS AKTIVITAS -->
    <div class="modal fade" id="modalEditActivityType" tabindex="-1" aria-labelledby="modalEditActivityTypeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditActivityTypeLabel">Edit Jenis Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditActivityType" action="index.php?controller=activity_types&action=update" method="POST">
                    <input type="hidden" id="editActivityTypeId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">ID (UUID)</label>
                            <input type="text" class="form-control bg-light" id="editActivityTypeUuidDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaActivityType" class="form-label">Nama Jenis Aktivitas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editNamaActivityType" name="name" required maxlength="50">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS JENIS AKTIVITAS -->
    <div class="modal fade" id="modalHapusActivityType" tabindex="-1" aria-labelledby="modalHapusActivityTypeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusActivityTypeLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formHapusActivityType" action="index.php?controller=activity_types&action=delete" method="POST">
                    <input type="hidden" id="hapusActivityTypeId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus jenis aktivitas <strong id="hapusActivityTypeName">ini</strong>?</p>
                        <p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT DATATABLES & MODAL INTERACTION -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initActivityTypesTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initActivityTypesTable, 50);
                    return;
                }
                if (document.getElementById('activityTypesTable') && !DataTable.isDataTable('#activityTypesTable')) {
                    new DataTable('#activityTypesTable', {
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ],
                        order: [[1, 'asc']], // Urut berdasarkan nama
                        columnDefs: [
                            { orderable: false, targets: -1 } // Kolom Aksi tidak bisa disortir
                        ],
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
                            search: "Search:",
                            searchPlaceholder: "Search records...",
                            lengthMenu: "Show _MENU_ entries",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "Showing 0 to 0 of 0 entries",
                            zeroRecords: "No matching records found",
                            emptyTable: "No data available in table",
                            paginate: {
                                first: "«",
                                last: "»",
                                next: "›",
                                previous: "‹"
                            }
                        }
                    });
                }
            }
            initActivityTypesTable();

            // Pasang data ke Modal Edit saat tombol Edit diklik
            const modalEditEl = document.getElementById('modalEditActivityType');
            if (modalEditEl) {
                modalEditEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        const id = button.getAttribute('data-id') || '';
                        const name = button.getAttribute('data-name') || '';
                        document.getElementById('editActivityTypeId').value = id;
                        document.getElementById('editActivityTypeUuidDisplay').value = id;
                        document.getElementById('editNamaActivityType').value = name;
                    }
                });
            }

            // Pasang data ke Modal Hapus saat tombol Hapus diklik
            const modalHapusEl = document.getElementById('modalHapusActivityType');
            if (modalHapusEl) {
                modalHapusEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        const id = button.getAttribute('data-id') || '';
                        const name = button.getAttribute('data-name') || '';
                        document.getElementById('hapusActivityTypeId').value = id;
                        document.getElementById('hapusActivityTypeName').textContent = name ? '"' + name + '"' : 'ini';
                    }
                });
            }
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
</document_content>
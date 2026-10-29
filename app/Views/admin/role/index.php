<?php
$activeAdminPage = 'roles';
$pageTitle = 'Daftar Role - Admin JTI';
$pageHeading = 'Daftar Role';

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

        <div class="admin-page active" id="page-roles">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Role</h3>
                        <p>Manajemen data hak akses pengguna (roles) dan identifier unik (UUID).</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRole">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Role
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
                    <table id="rolesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th style="width: 320px;">ID (UUID)</th>
                                <th>Nama Role</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $item): ?>
                                    <tr>
                                        <td>
                                            <code class="text-secondary small"><?= htmlspecialchars($item['id'] ?? ''); ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($item['name'] ?? ''); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                                data-bs-toggle="modal" data-bs-target="#modalEditRole"
                                                data-id="<?= htmlspecialchars($item['id'] ?? ''); ?>"
                                                data-name="<?= htmlspecialchars($item['name'] ?? ''); ?>">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalHapusRole"
                                                data-id="<?= htmlspecialchars($item['id'] ?? ''); ?>"
                                                data-name="<?= htmlspecialchars($item['name'] ?? ''); ?>">
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

    <!-- MODAL TAMBAH ROLE (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahRole" tabindex="-1" aria-labelledby="modalTambahRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahRoleLabel">Tambah Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahRole" action="index.php?controller=role&action=store" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaRole" class="form-label">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="namaRole" name="name" placeholder="Contoh: Admin, Ketua Kelas, Headmaster" required maxlength="50">
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

    <!-- MODAL EDIT ROLE (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditRole" tabindex="-1" aria-labelledby="modalEditRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditRoleLabel">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditRole" action="index.php?controller=role&action=update" method="POST">
                    <input type="hidden" id="editRoleId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">ID (UUID)</label>
                            <input type="text" class="form-control bg-light" id="editRoleUuidDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaRole" class="form-label">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editNamaRole" name="name" required maxlength="50">
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

    <!-- MODAL HAPUS ROLE (BOOTSTRAP) -->
    <div class="modal fade" id="modalHapusRole" tabindex="-1" aria-labelledby="modalHapusRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusRoleLabel">Konfirmasi Hapus Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formHapusRole" action="index.php?controller=role&action=delete" method="POST">
                    <input type="hidden" id="hapusRoleId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus role <strong id="hapusRoleName">ini</strong>?</p>
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
            // Inisialisasi DataTables resmi dengan Buttons (Copy, Excel, PDF, Print)
            function initRolesTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initRolesTable, 50);
                    return;
                }
                if (document.getElementById('rolesTable') && !DataTable.isDataTable('#rolesTable')) {
                    new DataTable('#rolesTable', {
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ],
                        order: [[1, 'asc']], // Urut berdasarkan Nama Role
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
            initRolesTable();

            // Pasang data ke Modal Edit saat tombol Edit diklik
            const modalEditRoleEl = document.getElementById('modalEditRole');
            if (modalEditRoleEl) {
                modalEditRoleEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        const id = button.getAttribute('data-id') || '';
                        const name = button.getAttribute('data-name') || '';
                        document.getElementById('editRoleId').value = id;
                        document.getElementById('editRoleUuidDisplay').value = id;
                        document.getElementById('editNamaRole').value = name;
                    }
                });
            }

            // Pasang data ke Modal Hapus saat tombol Hapus diklik
            const modalHapusRoleEl = document.getElementById('modalHapusRole');
            if (modalHapusRoleEl) {
                modalHapusRoleEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        const id = button.getAttribute('data-id') || '';
                        const name = button.getAttribute('data-name') || '';
                        document.getElementById('hapusRoleId').value = id;
                        document.getElementById('hapusRoleName').textContent = name ? '"' + name + '"' : 'ini';
                    }
                });
            }
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

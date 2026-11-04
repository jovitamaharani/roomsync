<?php
$activeAdminPage = 'years';
$pageTitle = 'Tahun Akademik - Admin JTI';
$pageHeading = 'Tahun Akademik';

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$flash = $flash ?? ($_SESSION['flash'] ?? null);

include __DIR__ . '/../components/header.php';
include __DIR__ . '/../components/sidebar.php';
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<!-- MAIN CONTENT -->
<main class="main-area">

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <style>
        .status-pill {
            display: inline-block;
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 500;
        }
    </style>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-years">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Tahun Akademik</h3>
                        <p>Pengelolaan periode dan semester akademik Politeknik Negeri Malang.</p>
                    </div>

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Tahun Akademik
                    </button>
                </div>

                <!-- Modal Add -->
                <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form id="addForm" action="index.php?controller=academic_year&action=store" method="POST"
                            class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="addModalLabel">Tambah Tahun Akademik</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($flash) && ($flash['target'] ?? '') === 'add'): ?>
                                    <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                        role="alert">
                                        <?= e($flash['message']) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="col-form-label" for="add_name">Tahun Akademik:</label>
                                    <input type="text" class="form-control" id="add_name" name="name"
                                        placeholder="Contoh: 2026/2027" maxlength="9" required>
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label" for="add_is_active">Status:</label>
                                    <select class="form-select" id="add_is_active" name="is_active" required>
                                        <option value="" disabled selected>-- Pilih Status --</option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form id="editForm" action="index.php?controller=academic_year&action=update" method="POST"
                            class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="editModalLabel">Edit Tahun Akademik</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($flash) && ($flash['target'] ?? '') === 'edit'): ?>
                                    <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                        role="alert">
                                        <?= e($flash['message']) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <input type="hidden" name="id" id="edit_id">

                                <div class="mb-3">
                                    <label class="form-label" for="edit_name">Tahun Akademik:</label>
                                    <input type="text" class="form-control" id="edit_name" maxlength="9" name="name"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="edit_is_active">Status:</label>
                                    <select class="form-select" id="edit_is_active" name="is_active" required>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Delete -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Tutup"></button>
                            </div>
                            <form id="deleteForm" action="index.php?controller=academic_year&action=delete"
                                method="POST">
                                <input type="hidden" id="deleteIdYears" name="id">
                                <div class="modal-body">
                                    <p class="mb-2">Apakah Anda yakin ingin menghapus tahun akademik <strong
                                            id="deleteItemLabel">ini</strong>?
                                    </p>
                                    <p class="text-danger small mb-0"><i
                                            class="bi bi-exclamation-triangle me-1"></i>Tindakan ini
                                        tidak dapat dibatalkan.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="table-responsive">
                    <table id="yearsTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Tahun Akademik</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($academicYears as $item): ?>
                                <?php
                                // boolean PostgreSQL bisa datang sebagai true / 't' / 1
                                $isActive = in_array($item['is_active'], [true, 't', 'true', '1', 1], true);
                                ?>
                                <tr>
                                    <td><?= e($item['name']) ?></td>
                                    <td>
                                        <?= $isActive
                                            ? '<span class="badge status-pill text-bg-success">Aktif</span>'
                                            : '<span class="badge status-pill text-bg-secondary">Tidak Aktif</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="<?= e($item['id']) ?>" data-name="<?= e($item['name']) ?>"
                                            data-status="<?= $isActive ? '1' : '0' ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <form action="index.php?controller=academic_year&action=delete" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun akademik ini?')">
                                            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-year"
                                                data-id="<?= e($item['id']) ?>"
                                                data-label="<?= e($item['name'] . ' (' . ($isActive ? 'Aktif' : 'Tidak Aktif') . ')') ?>">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- SCRIPTS HALAMAN -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const flash = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            function clearModalAlerts(modalEl) {
                modalEl.querySelectorAll('.modal-body .alert').forEach(function (alertEl) {
                    alertEl.remove();
                });
            }

            function showModal(el) {
                if (el) bootstrap.Modal.getOrCreateInstance(el).show();
            }

            // ===== DataTables (Copy, Excel, PDF, Print) =====
            function initYearsTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initYearsTable, 50);
                    return;
                }
                if (document.getElementById('yearsTable') && !DataTable.isDataTable('#yearsTable')) {
                    new DataTable('#yearsTable', {
                        pageLength: 10,
                        lengthMenu: [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ],
                        order: [],
                        columnDefs: [
                            { orderable: false, targets: -1 }
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
            initYearsTable();

            // ===== Modal =====
            const addModalEl = document.getElementById('addModal');
            const editModalEl = document.getElementById('editModal');
            const deleteModalEl = document.getElementById('deleteModal');
            const addForm = document.getElementById('addForm');

            // Klik Edit di tabel: isi form dari data baris tersebut
            // (saat dibuka otomatis setelah gagal, relatedTarget kosong sehingga isian lama tidak tertimpa)
            editModalEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    clearModalAlerts(editModalEl); // klik Edit pada baris lain: jangan bawa alert lama
                    document.getElementById('edit_id').value = button.getAttribute('data-id') || '';
                    document.getElementById('edit_name').value = button.getAttribute('data-name') || '';
                    document.getElementById('edit_is_active').value = button.getAttribute('data-status') || '1';
                }
            });

            // delete modal
            document.addEventListener('click', (ev) => {
                const delBtn = ev.target.closest('.btn-delete-year');
                if (delBtn) {
                    document.getElementById('deleteIdYears').value = delBtn.dataset.id;
                    document.getElementById('deleteItemLabel').textContent = delBtn.dataset.label;
                    showModal(deleteModalEl);
                }
            });


            // Form Tambah dikosongkan setiap modal ditutup, agar isian gagal tidak terbawa
            addModalEl.addEventListener('hidden.bs.modal', function () {
                addForm.reset();
                clearModalAlerts(addModalEl);
            });

            editModalEl.addEventListener('hidden.bs.modal', function () {
                clearModalAlerts(editModalEl);
            });

            // ===== Pesan dari server =====
            if (flash && flash.type === 'error' && flash.target) {
                const old = flash.old || {};

                if (flash.target === 'add') {
                    document.getElementById('add_name').value = old.name || '';
                    document.getElementById('add_is_active').value = (old.is_active === undefined || old.is_active === null) ? '' : String(old.is_active);
                    showModal(addModalEl);
                } else if (flash.target === 'edit') {
                    document.getElementById('edit_id').value = old.id || '';
                    document.getElementById('edit_name').value = old.name || '';
                    document.getElementById('edit_is_active').value = (old.is_active === undefined || old.is_active === null || old.is_active === '') ? '1' : String(old.is_active);
                    showModal(editModalEl);
                }
            }
        });
    </script>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php unset($_SESSION['flash']); ?>
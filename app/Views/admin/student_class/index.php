<?php
$activeAdminPage = 'classes';
$pageTitle = 'Daftar Kelas - Admin JTI';
$pageHeading = 'Daftar Kelas';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Keadaan form sebelum gagal (untuk membuka lagi modalnya)
$oldState = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

// Isi form dipakai bersama oleh modal tambah dan modal edit
$renderFields = function () use ($academicYears, $h) { ?>
    <div class="mb-3">
        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
        <select class="form-select" name="academic_year_id" required>
            <option value="">-- Pilih tahun ajaran --</option>
            <?php foreach ($academicYears as $y): ?>
                <option value="<?= $h($y['id']); ?>">
                    <?= $h($y['name'] . ($y['is_active'] ? ' (aktif)' : '')); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" placeholder="Contoh: TI-2A" maxlength="50" required>
    </div>
<?php };

include __DIR__ . '/../components/header.php';
include __DIR__ . '/../components/sidebar.php';
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<!-- MAIN CONTENT -->
<main class="main-area">

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-classes">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Kelas Mahasiswa</h3>
                        <p>Manajemen data kelas mahasiswa dan relasi tahun ajaran.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalTambahClass" <?= $canAdd ? '' : 'disabled'; ?>>
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Kelas
                        </button>
                    </div>
                </div>

                <?php if (!$canAdd): ?>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Kelas belum bisa dibuat. Tambahkan dulu minimal satu tahun ajaran.
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="classesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Jumlah User</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $item):
                                $payload = [
                                    'id'               => $item['id'],
                                    'academic_year_id' => $item['academic_year_id'],
                                    'name'             => $item['name'],
                                ];
                            ?>
                                <tr>
                                    <td><span class="fw-semibold text-dark"><?= $h($item['name']); ?></span></td>
                                    <td data-order="<?= $h($item['academic_year']); ?>">
                                        <?= $h($item['academic_year']); ?>
                                        <?php if ($item['year_active']): ?>
                                            <span class="badge bg-success ms-1">Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $item['user_count']; ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditClass"
                                            data-class="<?= $h(json_encode($payload)); ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusClass"
                                            data-id="<?= $h($item['id']); ?>"
                                            data-label="<?= $h($item['name'] . ' (' . $item['academic_year'] . ')'); ?>">
                                            <i class="bi bi-trash3 me-1"></i>Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH KELAS -->
    <div class="modal fade" id="modalTambahClass" tabindex="-1" aria-labelledby="modalTambahClassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="index.php?controller=student_class&action=store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahClassLabel">Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <?php $renderFields(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KELAS -->
    <div class="modal fade" id="modalEditClass" tabindex="-1" aria-labelledby="modalEditClassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="index.php?controller=student_class&action=update" method="POST">
                <input type="hidden" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditClassLabel">Edit Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <?php $renderFields(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS KELAS -->
    <div class="modal fade" id="modalHapusClass" tabindex="-1" aria-labelledby="modalHapusClassLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="index.php?controller=student_class&action=delete" method="POST">
                <input type="hidden" id="hapusClassId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusClassLabel">Konfirmasi Hapus Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus kelas <strong id="hapusClassName">ini</strong>?</p>
                    <p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT DATATABLES & MODAL INTERACTION -->
    <script>
        // JSON_HEX_* mencegah isian user menutup tag <script>
        const oldState = <?= json_encode($oldState, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function fillForm(form, data) {
            Object.keys(data).forEach(function (key) {
                const el = form.elements[key];
                if (el) el.value = data[key] ?? '';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            function initTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initTable, 50);
                    return;
                }
                if (document.getElementById('classesTable') && !DataTable.isDataTable('#classesTable')) {
                    new DataTable('#classesTable', {
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        order: [[1, 'desc'], [0, 'asc']],
                        columnDefs: [{ orderable: false, targets: -1 }],
                        layout: {
                            topStart: {
                                buttons: [
                                    { extend: "copy",  text: '<i class="bi bi-clipboard me-1"></i>Copy',           exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "excel", text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "pdf",   text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF',     exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "print", text: '<i class="bi bi-printer me-1"></i>Print',            exportOptions: { columns: ':not(:last-child)' } }
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
                            paginate: { first: "«", last: "»", next: "›", previous: "‹" }
                        }
                    });
                }
            }
            initTable();

            const modalTambahEl = document.getElementById('modalTambahClass');
            const modalEditEl   = document.getElementById('modalEditClass');
            const modalHapusEl  = document.getElementById('modalHapusClass');

            // Modal tambah: kosongkan form setelah ditutup
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                modalTambahEl.querySelector('form').reset();
            });

            // Modal edit: isi form dari atribut data-class tombol Edit
            modalEditEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.class) {
                    fillForm(modalEditEl.querySelector('form'), JSON.parse(button.dataset.class));
                }
            });

            // Modal hapus
            modalHapusEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    document.getElementById('hapusClassId').value = button.dataset.id || '';
                    document.getElementById('hapusClassName').textContent =
                        button.dataset.label ? '"' + button.dataset.label + '"' : 'ini';
                }
            });

            // Simpan gagal: buka lagi modal yang tadi dipakai, lengkap dengan isiannya
            function reopenModal() {
                if (typeof bootstrap === 'undefined') {
                    setTimeout(reopenModal, 50);
                    return;
                }
                const el = oldState.modal === 'edit' ? modalEditEl : modalTambahEl;
                fillForm(el.querySelector('form'), oldState.data);
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
            if (oldState) reopenModal();
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
<?php
$activeAdminPage = 'courses';
$pageTitle = 'Daftar Mata Kuliah - Admin JTI';
$pageHeading = 'Daftar Mata Kuliah';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Keadaan form sebelum gagal (untuk membuka lagi modalnya)
$oldState = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

// Isi form dipakai bersama oleh modal tambah dan modal edit
$renderFields = function () { ?>
    <div class="mb-3">
        <label class="form-label">Kode MK <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="kode_mk" placeholder="Contoh: TIF201" maxlength="20" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Mata Kuliah <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" placeholder="Contoh: Desain dan Pemrograman Web" maxlength="150" required>
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

        <div class="admin-page active" id="page-courses">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Mata Kuliah</h3>
                        <p>Manajemen data mata kuliah Jurusan Teknologi Informasi sesuai kurikulum.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahCourse">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Mata Kuliah
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="coursesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th>Jumlah Jadwal</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $item):
                                $payload = [
                                    'id'      => $item['id'],
                                    'kode_mk' => $item['kode_mk'],
                                    'name'    => $item['name'],
                                ];
                            ?>
                                <tr>
                                    <td><code class="text-dark"><?= $h($item['kode_mk']); ?></code></td>
                                    <td><span class="fw-semibold text-dark"><?= $h($item['name']); ?></span></td>
                                    <td><?= (int) $item['schedule_count']; ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditCourse"
                                            data-course="<?= $h(json_encode($payload)); ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusCourse"
                                            data-id="<?= $h($item['id']); ?>"
                                            data-label="<?= $h($item['kode_mk'] . ' - ' . $item['name']); ?>">
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

    <!-- MODAL TAMBAH MATA KULIAH -->
    <div class="modal fade" id="modalTambahCourse" tabindex="-1" aria-labelledby="modalTambahCourseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahCourseLabel">Tambah Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=store" method="POST">
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
    </div>

    <!-- MODAL EDIT MATA KULIAH -->
    <div class="modal fade" id="modalEditCourse" tabindex="-1" aria-labelledby="modalEditCourseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditCourseLabel">Edit Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=update" method="POST">
                    <input type="hidden" name="id">
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
    </div>

    <!-- MODAL HAPUS MATA KULIAH -->
    <div class="modal fade" id="modalHapusCourse" tabindex="-1" aria-labelledby="modalHapusCourseLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusCourseLabel">Konfirmasi Hapus Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=delete" method="POST">
                    <input type="hidden" id="hapusCourseId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus mata kuliah <strong id="hapusCourseName">ini</strong>?</p>
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
                if (document.getElementById('coursesTable') && !DataTable.isDataTable('#coursesTable')) {
                    new DataTable('#coursesTable', {
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        order: [[0, 'asc']],
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

            const modalTambahEl = document.getElementById('modalTambahCourse');
            const modalEditEl   = document.getElementById('modalEditCourse');
            const modalHapusEl  = document.getElementById('modalHapusCourse');

            // Modal tambah: kosongkan form setelah ditutup
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                modalTambahEl.querySelector('form').reset();
            });

            // Modal edit: isi form dari atribut data-course tombol Edit
            modalEditEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.course) {
                    fillForm(modalEditEl.querySelector('form'), JSON.parse(button.dataset.course));
                }
            });

            // Modal hapus
            modalHapusEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    document.getElementById('hapusCourseId').value = button.dataset.id || '';
                    document.getElementById('hapusCourseName').textContent =
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
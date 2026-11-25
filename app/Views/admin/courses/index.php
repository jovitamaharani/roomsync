<?php
$activeAdminPage = 'course';
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
        <label class="form-label mb-1">Kode MK <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="kode_mk" placeholder="Contoh: TIF201" maxlength="20"
            style="height: auto; min-height: 0;" required>
    </div>
    <div class="mb-3">
        <label class="form-label mb-1">Nama Mata Kuliah <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" placeholder="Contoh: Desain dan Pemrograman Web" maxlength="150"
            style="height: auto; min-height: 0;" required>
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
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#modalImportCourse">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambahCourse">
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
                                <th class="text-start">Jumlah Jadwal</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $item):
                                $payload = [
                                    'id' => $item['id'],
                                    'kode_mk' => $item['kode_mk'],
                                    'name' => $item['name'],
                                ];
                                ?>
                                <tr>
                                    <td><code class="text-dark"><?= $h($item['kode_mk']); ?></code></td>
                                    <td><span class="fw-semibold text-dark"><?= $h($item['name']); ?></span></td>
                                    <td class="text-start"><?= (int) $item['schedule_count']; ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditCourse"
                                            data-course="<?= $h(json_encode($payload)); ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#modalHapusCourse" data-id="<?= $h($item['id']); ?>"
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
    <div class="modal fade" id="modalTambahCourse" tabindex="-1" aria-labelledby="modalTambahCourseLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahCourseLabel">Tambah Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=store" method="POST">
                    <div class="modal-body">
                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'add'): ?>
                            <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show p-3 mb-3 d-flex align-items-center justify-content-between"
                                role="alert">
                                <span><?= $h($flash['message']); ?></span>
                                <button type="button" class="btn-close position-static ms-2 p-1" data-bs-dismiss="alert"
                                    aria-label="Tutup" style="font-size: 0.75rem;"></button>
                            </div>
                        <?php endif; ?>
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
    <div class="modal fade" id="modalEditCourse" tabindex="-1" aria-labelledby="modalEditCourseLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditCourseLabel">Edit Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=update" method="POST">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'edit'): ?>
                            <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show p-3 mb-3 d-flex align-items-center justify-content-between"
                                role="alert">
                                <span><?= $h($flash['message']); ?></span>
                                <button type="button" class="btn-close position-static ms-2 p-1" data-bs-dismiss="alert"
                                    aria-label="Tutup" style="font-size: 0.75rem;"></button>
                            </div>
                        <?php endif; ?>
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
    <div class="modal fade" id="modalHapusCourse" tabindex="-1" aria-labelledby="modalHapusCourseLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusCourseLabel">Konfirmasi Hapus Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=course&action=delete" method="POST">
                    <input type="hidden" id="hapusCourseId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus mata kuliah <strong
                                id="hapusCourseName">ini</strong>?</p>
                        <p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini
                            tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL IMPOR MATA KULIAH -->
    <div class="modal fade" id="modalImportCourse" tabindex="-1" aria-labelledby="modalImportCourseLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="index.php?controller=course&action=import" method="POST" enctype="multipart/form-data"
                class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportCourseLabel">Impor Data Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($flash) && ($flash['target'] ?? '') === 'import'): ?>
                        <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show py-2 px-3 small d-flex align-items-center justify-content-between"
                            role="alert">
                            <span><?= htmlspecialchars($flash['message']); ?></span>
                            <button type="button" class="btn-close position-static ms-2 p-1" data-bs-dismiss="alert"
                                aria-label="Close" style="font-size: 0.75rem;"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info py-2 px-3 small">
                        Gunakan template resmi (<code>.xlsx</code>) dengan kolom <code>kode_mk</code> dan
                        <code>name</code>.<br>
                        <a href="assets/templates/Course_Template.xlsx" class="fw-bold alert-link text-nowrap" download>
                            Unduh Template
                        </a>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1" for="course_import_file">Pilih Berkas (.xlsx / .csv):</label>
                        <input type="file" class="form-control w-100 course-file-input" id="course_import_file"
                            name="file" accept=".xlsx,.csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah & Impor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT DATATABLES & MODAL INTERACTION -->
    <script>
        // JSON_HEX_* mencegah isian user menutup tag <script>
        const oldState = <?php echo json_encode($oldState ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const flashData = <?php echo json_encode($flash ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function fillForm(form, data) {
            if (!form || !data) return;
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
                                    { extend: "copy", text: '<i class="bi bi-clipboard me-1"></i>Copy', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "excel", text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "pdf", text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', exportOptions: { columns: ':not(:last-child)' } },
                                    { extend: "print", text: '<i class="bi bi-printer me-1"></i>Print', exportOptions: { columns: ':not(:last-child)' } }
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

            const modalImportEl = document.getElementById('modalImportCourse');
            const modalTambahEl = document.getElementById('modalTambahCourse');
            const modalEditEl = document.getElementById('modalEditCourse');
            const modalHapusEl = document.getElementById('modalHapusCourse');

            // Modal impor: kosongkan form setelah ditutup
            if (modalImportEl) {
                modalImportEl.addEventListener('hidden.bs.modal', function () {
                    const form = modalImportEl.querySelector('form');
                    if (form) form.reset();
                });
            }

            // Modal tambah: kosongkan form setelah ditutup
            if (modalTambahEl) {
                modalTambahEl.addEventListener('hidden.bs.modal', function () {
                    const form = modalTambahEl.querySelector('form');
                    if (form) form.reset();
                });
            }

            // Modal edit: isi form dari atribut data-course tombol Edit
            if (modalEditEl) {
                modalEditEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button && button.dataset.course) {
                        fillForm(modalEditEl.querySelector('form'), JSON.parse(button.dataset.course));
                    }
                });
            }

            // Modal hapus
            if (modalHapusEl) {
                modalHapusEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        const hapusId = document.getElementById('hapusCourseId');
                        const hapusName = document.getElementById('hapusCourseName');
                        if (hapusId) hapusId.value = button.dataset.id || '';
                        if (hapusName) {
                            hapusName.textContent = button.dataset.label ? '"' + button.dataset.label + '"' : 'ini';
                        }
                    }
                });
            }

            // Simpan / impor gagal: buka lagi modal yang sesuai target-nya saja
            function reopenModal() {
                if (typeof bootstrap === 'undefined') {
                    setTimeout(reopenModal, 50);
                    return;
                }

                const target = (flashData && flashData.type === 'error' && flashData.target)
                    ? flashData.target
                    : (oldState && oldState.modal ? oldState.modal : null);

                if (target === 'import') {
                    if (modalImportEl) {
                        bootstrap.Modal.getOrCreateInstance(modalImportEl).show();
                    }
                    return;
                }

                if (target === 'edit') {
                    if (modalEditEl) {
                        if (oldState && oldState.data) {
                            fillForm(modalEditEl.querySelector('form'), oldState.data);
                        }
                        bootstrap.Modal.getOrCreateInstance(modalEditEl).show();
                    }
                    return;
                }

                if (target === 'add') {
                    if (modalTambahEl) {
                        if (oldState && oldState.data) {
                            fillForm(modalTambahEl.querySelector('form'), oldState.data);
                        }
                        bootstrap.Modal.getOrCreateInstance(modalTambahEl).show();
                    }
                }
            }

            reopenModal();
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
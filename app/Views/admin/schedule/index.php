<?php
$activeAdminPage = 'schedules';
$pageTitle = 'Jadwal Mata Kuliah - Admin JTI';
$pageHeading = 'Jadwal Mata Kuliah';

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
$schedules = $schedules ?? [];
$options = $options ?? ['semesters' => [], 'courses' => [], 'lecturers' => [], 'classes' => [], 'rooms' => []];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$baseUrl = 'index.php?controller=schedule&action=';

function scheduleSelects($options, $days, $prefix)
{
    ob_start(); ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Semester" class="form-label">Semester</label>
            <select class="form-select" id="<?= $prefix ?>Semester" name="semester" required>
                <option value="" selected disabled>Pilih Semester</option>
                <?php foreach ($options['semesters'] as $o): ?>
                    <option value="<?= e($o) ?>"><?= e($o) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Hari" class="form-label">Hari</label>
            <select class="form-select" id="<?= $prefix ?>Hari" name="day_of_week" required>
                <option value="" selected disabled>Pilih Hari</option>
                <?php foreach ($days as $num => $label): ?>
                    <option value="<?= $num ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Mulai" class="form-label">Jam Mulai</label>
            <input type="time" class="form-control" id="<?= $prefix ?>Mulai" name="start_time" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Selesai" class="form-label">Jam Selesai</label>
            <input type="time" class="form-control" id="<?= $prefix ?>Selesai" name="end_time" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="<?= $prefix ?>Matkul" class="form-label">Mata Kuliah</label>
        <select class="form-select js-search" id="<?= $prefix ?>Matkul" name="course_id" placeholder="Pilih Mata Kuliah"
            required>
            <option value="" selected disabled>Pilih Mata Kuliah</option>
            <?php foreach ($options['courses'] as $o): ?>
                <option value="<?= e($o['id']) ?>"><?= e($o['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="<?= $prefix ?>Dosen" class="form-label">Dosen Pengampu</label>
        <select class="form-select js-search" id="<?= $prefix ?>Dosen" name="lecturer_id" placeholder="Pilih Dosen"
            required>
            <option value="" selected disabled>Pilih Dosen</option>
            <?php foreach ($options['lecturers'] as $o): ?>
                <option value="<?= e($o['id']) ?>"><?= e($o['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Kelas" class="form-label">Kelas</label>
            <select class="form-select js-search" id="<?= $prefix ?>Kelas" name="student_class_id" placeholder="Pilih Kelas"
                required>
                <option value="" selected disabled>Pilih Kelas</option>
                <?php foreach ($options['classes'] as $o): ?>
                    <option value="<?= e($o['id']) ?>"><?= e($o['name'] . ' (' . $o['academic_year'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="<?= $prefix ?>Ruang" class="form-label">Ruang</label>
            <select class="form-select js-search" id="<?= $prefix ?>Ruang" name="room_id" placeholder="Pilih Ruang"
                required>
                <option value="" selected disabled>Pilih Ruang</option>
                <?php foreach ($options['rooms'] as $o): ?>
                    <option value="<?= e($o['id']) ?>"><?= e($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

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

        <div class="admin-page active" id="page-schedules">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Jadwal Mata Kuliah</h3>
                        <p>Informasi jadwal perkuliahan dan penggunaan ruang kelas berdasarkan kurikulum akademik.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importSchedules" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="schedules" data-bs-toggle="modal"
                            data-bs-target="#importModal">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Jadwal
                        </button>
                    </div>
                </div>

                <div class="table-custom-filters d-flex flex-wrap gap-2 align-items-center" id="scheduleFilters">
                    <select class="form-select w-auto js-filter" id="filterHariSchedules" data-filter-col="0"
                        data-no-search aria-label="Filter hari">
                        <option value="">Semua Hari</option>
                        <?php foreach ($days as $label): ?>
                            <option value="<?= e($label) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select w-auto js-filter" id="filterKelasSchedules" data-filter-col="4"
                        aria-label="Filter kelas">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($options['classes'] as $o): ?>
                            <option value="<?= e($o['name']) ?>"><?= e($o['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-select w-auto js-filter" id="filterRuangSchedules" data-filter-col="5"
                        aria-label="Filter ruangan">
                        <option value="">Semua Ruangan</option>
                        <?php foreach ($options['rooms'] as $o): ?>
                            <option value="<?= e($o['name']) ?>"><?= e($o['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="schedulesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Kelas</th>
                                <th>Ruang</th>
                                <th>Semester</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $s): ?>
                                <?php
                                $payload = [
                                    'id' => $s['id'],
                                    'semester' => $s['semester'],
                                    'day_of_week' => (string) $s['day_of_week'],
                                    'start_time' => substr($s['start_time'], 0, 5),
                                    'end_time' => substr($s['end_time'], 0, 5),
                                    'course_id' => $s['course_id'],
                                    'lecturer_id' => $s['lecturer_id'],
                                    'student_class_id' => $s['student_class_id'],
                                    'room_id' => $s['room_id'],
                                ];
                                $hari = $days[(int) $s['day_of_week']] ?? '-';
                                ?>
                                <tr>
                                    <td><?= e($hari) ?></td>
                                    <td class="text-nowrap">
                                        <?= e(substr($s['start_time'], 0, 5) . '–' . substr($s['end_time'], 0, 5)) ?>
                                    </td>
                                    <td><?= e($s['course']) ?></td>
                                    <td><?= e($s['lecturer']) ?></td>
                                    <td><?= e($s['classroom']) ?></td>
                                    <td><?= e($s['room']) ?></td>
                                    <td><?= e($s['semester']) ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-warning text-dark btn-edit-jadwal"
                                            data-schedule="<?= e(json_encode($payload)) ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-jadwal"
                                            data-id="<?= e($s['id']) ?>"
                                            data-label="<?= e($s['course'] . ' (' . $hari . ')') ?>">
                                            <i class="bi bi-trash"></i> Hapus
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

    <!-- MODAL ADD -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Tambah Jadwal Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahJadwal" action="<?= $baseUrl ?>store" method="POST">
                    <div class="modal-body">
                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'add'): ?>
                            <div class="alert <?= $flash['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                role="alert">
                                <?= htmlspecialchars($flash['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?= scheduleSelects($options, $days, 'add') ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jadwal Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditJadwal" action="<?= $baseUrl ?>update" method="POST">
                    <input type="hidden" name="id" id="editIdJadwal">
                    <div class="modal-body">
                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'edit'): ?>
                            <div class="alert <?= $flash['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                role="alert">
                                <?= htmlspecialchars($flash['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?= scheduleSelects($options, $days, 'edit') ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DELETE -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formHapus" action="<?= $baseUrl ?>delete" method="POST">
                    <input type="hidden" id="hapusIdJadwal" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus jadwal <strong
                                id="hapusItemLabel">ini</strong>?
                        </p>
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

    <!-- Modal Import -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="index.php?controller=schedule&action=import" method="POST" enctype="multipart/form-data"
                class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Impor Data Dosen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        Gunakan template resmi agar format kolom sesuai.
                        <a href="assets/templates/template_dosen.xlsx" class="fw-bold alert-link" download>Unduh
                            Template</a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="excel_file">Pilih Berkas (.xlsx / .csv):</label>
                        <input type="file" class="form-control" id="excel_file" name="file" accept=".xlsx, .xls, .csv"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah & Impor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const flash = <?= json_encode($flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            ['addModal', 'editModal'].forEach(function (id) {
                const modalEl = document.getElementById(id);
                if (!modalEl) return;
                modalEl.addEventListener('hidden.bs.modal', function () {
                    modalEl.querySelectorAll('.alert').forEach(function (alertEl) {
                        alertEl.remove();
                    });
                });
            });

            function escapeRegex(text) {
                return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function fillForm(form, data) {
                Object.keys(data).forEach(function (name) {
                    const el = form.elements[name];
                    if (!el) return;
                    if (el.tomselect) {
                        el.tomselect.setValue(data[name], true);
                    } else {
                        el.value = data[name];
                    }
                });
            }

            function showModal(id) {
                bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
            }

            // ===== Dropdown dengan kotak pencarian (Tom Select) =====
            function initSearchableSelects() {
                if (typeof TomSelect === 'undefined') return; // aset gagal dimuat: tetap pakai <select> biasa

                // Kotak pencarian berada di dalam daftar dropdown, bukan di dalam kontrolnya
                function searchConfig(extra) {
                    return Object.assign({
                        maxOptions: null,
                        plugins: ['dropdown_input'],
                        onInitialize: function () {
                            if (this.dropdown_input) this.dropdown_input.setAttribute('placeholder', 'Cari...');
                        }
                    }, extra || {});
                }

                document.querySelectorAll('select.js-search').forEach(function (select) {
                    new TomSelect(select, searchConfig());
                });

                document.querySelectorAll('select.js-filter').forEach(function (select) {
                    const config = select.hasAttribute('data-no-search')
                        ? { maxOptions: null, allowEmptyOption: true, controlInput: null } // Hari: sedikit pilihan
                        : searchConfig({ allowEmptyOption: true });
                    new TomSelect(select, config);
                });
            }
            initSearchableSelects();

            // ===== DataTables: tombol export di baris pertama (kiri), filter di baris bawahnya =====
            function initSchedulesTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initSchedulesTable, 50);
                    return;
                }
                if (!document.getElementById('schedulesTable') || DataTable.isDataTable('#schedulesTable')) {
                    return;
                }

                const filterBar = document.getElementById('scheduleFilters');

                // Kolom kiri atas: tombol export di baris pertama, filter di baris bawahnya
                const leftBar = document.createElement('div');
                leftBar.className = 'd-flex flex-column align-items-start gap-2';
                if (filterBar) leftBar.appendChild(filterBar);
                const exportCols = { columns: ':not(:last-child)' }; // kolom Aksi tidak ikut diekspor

                const table = new DataTable('#schedulesTable', {
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    order: [],
                    columnDefs: [
                        { orderable: false, targets: -1 }
                    ],
                    buttons: [
                        { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i>Copy', exportOptions: exportCols },
                        { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', exportOptions: exportCols },
                        { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', exportOptions: exportCols },
                        { extend: 'print', text: '<i class="bi bi-printer me-1"></i>Print', exportOptions: exportCols }
                    ],
                    layout: {
                        topStart: function () { return leftBar; },
                        topEnd: 'search',
                        bottomStart: 'info',
                        bottomEnd: 'paging'
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
                    },
                    initComplete: function () {
                        // Tombol export ditaruh di baris pertama, di atas filter
                        const api = this.api();
                        const container = api.buttons().container();
                        const buttonsNode = container && container.jquery ? container[0] : container;
                        if (buttonsNode) leftBar.prepend(buttonsNode);
                    }
                });

                // Filter hari / kelas / ruangan (pencocokan persis pada kolomnya)
                if (filterBar) {
                    filterBar.querySelectorAll('select[data-filter-col]').forEach(function (select) {
                        select.addEventListener('change', function () {
                            const col = parseInt(select.dataset.filterCol, 10);
                            const value = select.value;
                            table.column(col).search(value ? '^' + escapeRegex(value) + '$' : '', true, false).draw();
                        });
                    });
                }
            }
            initSchedulesTable();

            // ===== Form Tambah: bersihkan isian (termasuk dropdown searchable) saat modal ditutup =====
            const addModalEl = document.getElementById('addModal');
            if (addModalEl) {
                addModalEl.addEventListener('hidden.bs.modal', function () {
                    const form = document.getElementById('formTambahJadwal');
                    form.reset();
                    form.querySelectorAll('select.js-search').forEach(function (select) {
                        if (select.tomselect) select.tomselect.clear(true);
                    });
                });
            }

            // ===== Edit & Hapus (delegasi event: tetap jalan setelah paging/search DataTables) =====
            document.addEventListener('click', function (ev) {
                const editBtn = ev.target.closest('.btn-edit-jadwal');
                if (editBtn) {
                    fillForm(document.getElementById('formEditJadwal'), JSON.parse(editBtn.dataset.schedule));
                    showModal('editModal');
                    return;
                }
                const delBtn = ev.target.closest('.btn-hapus-jadwal');
                if (delBtn) {
                    document.getElementById('hapusIdJadwal').value = delBtn.dataset.id;
                    document.getElementById('hapusItemLabel').textContent = delBtn.dataset.label;
                    showModal('deleteModal');
                }
            });

            // ===== Setelah validasi server gagal: buka lagi modal dengan input sebelumnya =====
            if (flash && flash.type === 'error' && flash.old && Object.keys(flash.old).length) {
                if (flash.target === 'add') {
                    fillForm(document.getElementById('formTambahJadwal'), flash.old);
                    showModal('addModal');
                } else if (flash.target === 'edit') {
                    fillForm(document.getElementById('formEditJadwal'), flash.old);
                    showModal('editModal');
                }
            }
        });
    </script>

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>
<?php
$activeAdminPage = 'schedule';
$pageTitle = 'Jadwal - Admin JTI';
$pageHeading = 'Daftar Jadwal';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Keadaan form sebelum gagal (untuk membuka lagi modalnya)
$oldState = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$hhmm = fn($t) => substr((string) $t, 0, 5);

// Dropdown relasi: field => [label, data, teks opsi]
$selects = [
    'room_id' => [
        'Ruangan',
        $options['rooms'],
        fn($r) => "{$r['building']} - {$r['name']} (kapasitas {$r['capacity']})"
    ],
    'classroom_id' => [
        'Kelas',
        $options['classes'],
        fn($c) => "{$c['name']} ({$c['academic_year']})"
    ],
    'lecturer_id' => [
        'Dosen',
        $options['lecturers'],
        fn($l) => "{$l['kode']} - {$l['name']}"
    ],
    'course_id' => [
        'Mata kuliah',
        $options['courses'],
        fn($c) => "{$c['kode_mk']} - {$c['name']}"
    ],
];

// Isi form dipakai bersama oleh modal tambah dan modal edit
$renderFields = function (bool $forEdit) use ($selects, $days, $semesters, $h) {
    foreach ($selects as $field => [$label, $rows, $text]) { ?>
        <div class="mb-3">
            <label class="form-label"><?= $label; ?> <span class="text-danger">*</span></label>
            <select class="form-select" name="<?= $field; ?>" required>
                <option value="">-- Pilih <?= strtolower($label); ?> --</option>
                <?php foreach ($rows as $row):
                    $inactive = isset($row['is_active']) && empty($row['is_active']);
                    if ($inactive && !$forEdit) {
                        continue;   // ruangan nonaktif tidak bisa dipilih untuk jadwal baru
                    } ?>
                    <option value="<?= $h($row['id']); ?>">
                        <?= $h($text($row) . ($inactive ? ' (nonaktif)' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php } ?>

    <div class="mb-3">
        <label class="form-label">Hari <span class="text-danger">*</span></label>
        <select class="form-select" name="day_of_week" required>
            <option value="">-- Pilih hari --</option>
            <?php foreach ($days as $num => $name): ?>
                <option value="<?= $num; ?>"><?= $name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="row">
        <div class="col-6 mb-3">
            <label class="form-label">Jam mulai <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="start_time" required>
        </div>
        <div class="col-6 mb-3">
            <label class="form-label">Jam selesai <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="end_time" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Semester <span class="text-danger">*</span></label>
        <select class="form-select" name="semester_id" required>
            <?php foreach ($semesters as $s): ?>
                <option value="<?= $s; ?>"><?= ucfirst($s); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<?php };

include __DIR__ . '/../../components/header.php';
include __DIR__ . '/../../components/sidebar.php';
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<!-- MAIN CONTENT -->
<main class="main-area">

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../../components/navbar.php'; ?>

    <!-- TOAST NOTIFICATION -->
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-schedules">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Jadwal</h3>
                        <p>Jadwal kuliah pada tahun ajaran aktif.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambahSchedule" <?= $canAdd ? '' : 'disabled'; ?>>
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Jadwal
                        </button>
                    </div>
                </div>

                <?php if (!$canAdd): ?>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Jadwal belum bisa dibuat. Pastikan ada ruangan aktif, kelas di tahun ajaran aktif,
                        dosen, dan mata kuliah.
                    </div>
                <?php endif; ?>

                <!-- ALERT NOTIFIKASI FLASH -->
                <?php if (!empty($flash)): ?>
                    <div class="alert <?= ($flash['type'] ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show mb-3"
                        role="alert">
                        <i
                            class="bi <?= ($flash['type'] ?? '') === 'error' ? 'bi-exclamation-triangle' : 'bi-check-circle' ?> me-2"></i>
                        <?= $h($flash['message'] ?? ''); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="schedulesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Dosen</th>
                                <th>Ruangan</th>
                                <th>Semester</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $item):
                                $payload = [
                                    'id' => $item['id'],
                                    'room_id' => $item['room_id'],
                                    'classroom_id' => $item['classroom_id'],
                                    'lecturer_id' => $item['lecturer_id'],
                                    'course_id' => $item['course_id'],
                                    'day_of_week' => $item['day_of_week'],
                                    'start_time' => $hhmm($item['start_time']),
                                    'end_time' => $hhmm($item['end_time']),
                                    'semester_id' => $item['semester_id'],
                                ];
                                $label = $item['course_name'] . ' - ' . ($days[$item['day_of_week']] ?? '-')
                                    . ' ' . $hhmm($item['start_time']) . '-' . $hhmm($item['end_time']);
                                ?>
                                <tr>
                                    <td data-order="<?= (int) $item['day_of_week']; ?>">
                                        <?= $h($days[$item['day_of_week']] ?? '-'); ?></td>
                                    <td data-order="<?= $h($hhmm($item['start_time'])); ?>">
                                        <?= $h($hhmm($item['start_time'])); ?>-<?= $h($hhmm($item['end_time'])); ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= $h($item['course_name']); ?></span><br>
                                        <small class="text-muted"><?= $h($item['kode_mk']); ?></small>
                                    </td>
                                    <td><?= $h($item['class_name']); ?></td>
                                    <td><?= $h($item['lecturer_name']); ?></td>
                                    <td><?= $h($item['building'] . ' - ' . $item['room_name']); ?></td>
                                    <td><?= $h(ucfirst($item['semester_id'])); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditSchedule"
                                            data-schedule="<?= $h(json_encode($payload)); ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#modalHapusSchedule" data-id="<?= $h($item['id']); ?>"
                                            data-label="<?= $h($label); ?>">
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

    <!-- MODAL TAMBAH JADWAL -->
    <div class="modal fade" id="modalTambahSchedule" tabindex="-1" aria-labelledby="modalTambahScheduleLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahScheduleLabel">Tambah Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahSchedule" action="index.php?controller=schedule&action=store" method="POST">
                    <div class="modal-body">
                        <?php $renderFields(false); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT JADWAL -->
    <div class="modal fade" id="modalEditSchedule" tabindex="-1" aria-labelledby="modalEditScheduleLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditScheduleLabel">Edit Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditSchedule" action="index.php?controller=schedule&action=update" method="POST">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <?php $renderFields(true); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS JADWAL -->
    <div class="modal fade" id="modalHapusSchedule" tabindex="-1" aria-labelledby="modalHapusScheduleLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusScheduleLabel">Konfirmasi Hapus Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formHapusSchedule" action="index.php?controller=schedule&action=delete" method="POST">
                    <input type="hidden" id="hapusScheduleId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus jadwal <strong
                                id="hapusScheduleName">ini</strong>?</p>
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
            function initSchedulesTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initSchedulesTable, 50);
                    return;
                }
                if (document.getElementById('schedulesTable') && !DataTable.isDataTable('#schedulesTable')) {
                    new DataTable('#schedulesTable', {
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        order: [[0, 'asc'], [1, 'asc']],   // hari, lalu jam (memakai data-order)
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
            initSchedulesTable();

            const modalTambahEl = document.getElementById('modalTambahSchedule');
            const modalEditEl = document.getElementById('modalEditSchedule');
            const modalHapusEl = document.getElementById('modalHapusSchedule');

            // Modal tambah: kosongkan form setelah ditutup
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                modalTambahEl.querySelector('form').reset();
            });

            // Modal edit: isi form dari atribut data-schedule tombol Edit
            modalEditEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.schedule) {
                    fillForm(modalEditEl.querySelector('form'), JSON.parse(button.dataset.schedule));
                }
            });

            // Modal hapus
            modalHapusEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    document.getElementById('hapusScheduleId').value = button.dataset.id || '';
                    document.getElementById('hapusScheduleName').textContent =
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
    <?php include 'app/Views/admin/components/footer.php'; ?>
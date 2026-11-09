<?php
$activeAdminPage = 'rooms';
$pageTitle = 'Daftar Ruang Kelas - Admin JTI';
$pageHeading = 'Daftar Ruang Kelas';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Keadaan form sebelum gagal (untuk membuka lagi modalnya)
$oldState = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

// Isi form dipakai bersama oleh modal tambah dan modal edit
$renderFields = function () { ?>
    <div class="mb-3">
        <label class="form-label">Nama Ruang <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" placeholder="Contoh: RT 01 atau Lab RPL 1" maxlength="100" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Gedung <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="building" placeholder="Contoh: Gedung TI" maxlength="100" required>
    </div>
    <div class="row">
        <div class="col-6 mb-3">
            <label class="form-label">Lantai <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="floor" min="-5" max="100" required>
        </div>
        <div class="col-6 mb-3">
            <label class="form-label">Kapasitas <span class="text-danger">*</span></label>
            <input type="number" class="form-control" name="capacity" min="1" max="2000" required>
        </div>
    </div>
    <div class="row">
        <div class="col-6 mb-3">
            <label class="form-label">Jumlah Kursi</label>
            <input type="number" class="form-control" name="chairs" value="0" min="0" required>
        </div>
        <div class="col-6 mb-3">
            <label class="form-label">Jumlah Meja</label>
            <input type="number" class="form-control" name="tables" value="0" min="0" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Fasilitas</label>
        <textarea class="form-control" name="facilities" rows="2" placeholder="Contoh: Proyektor, AC, Whiteboard"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-check-label">
            <input class="form-check-input me-2" type="checkbox" name="is_active" checked>Ruangan aktif
        </label>
        <div class="form-text">Ruangan nonaktif tidak bisa dipilih untuk jadwal baru.</div>
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

        <div class="admin-page active" id="page-rooms">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Ruang Kelas</h3>
                        <p>Manajemen data ruangan, gedung, kapasitas, dan status.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRoom">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Ruang
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="roomsTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama Ruang</th>
                                <th>Gedung</th>
                                <th>Lantai</th>
                                <th>Kapasitas</th>
                                <th>Kursi / Meja</th>
                                <th>Fasilitas</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>                           
                            <?php foreach ($rooms as $item):
                                $payload = [
                                    'id'         => $item['id'],
                                    'name'       => $item['name'],
                                    'building'   => $item['building'],
                                    'floor'      => (int) $item['floor'],
                                    'capacity'   => (int) $item['capacity'],
                                    'chairs'     => (int) $item['chairs'],
                                    'tables'     => (int) $item['tables'],
                                    'facilities' => ($item['facilities'] ?? '') === '-' ? '' : ($item['facilities'] ?? ''),
                                    'is_active'  => (bool) $item['is_active'],
                                ];
                                $facilities = trim((string) ($item['facilities'] ?? ''));
                            ?>
                                <tr>
                                    <td><span class="fw-semibold text-dark"><?= $h($item['name']); ?></span></td>
                                    <td><?= $h($item['building']); ?></td>
                                    <td><?= (int) $item['floor']; ?></td>
                                    <td><?= (int) $item['capacity']; ?></td>
                                    <td><?= (int) $item['chairs']; ?> / <?= (int) $item['tables']; ?></td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 220px;"
                                              title="<?= $h($facilities); ?>">
                                            <?= $facilities !== '' ? $h($facilities) : '-'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['is_active']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditRoom"
                                            data-room="<?= $h(json_encode($payload)); ?>">
                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusRoom"
                                            data-id="<?= $h($item['id']); ?>"
                                            data-label="<?= $h($item['building'] . ' - ' . $item['name']); ?>">
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

    <!-- MODAL TAMBAH RUANG -->
    <div class="modal fade" id="modalTambahRoom" tabindex="-1" aria-labelledby="modalTambahRoomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" action="index.php?controller=room&action=store" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahRoomLabel">Tambah Ruang Kelas</h5>
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

    <!-- MODAL EDIT RUANG -->
<div class="modal fade" id="modalEditRoom" tabindex="-1" aria-labelledby="modalEditRoomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content" action="index.php?controller=room&action=update" method="POST">
            <input type="hidden" name="id">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditRoomLabel">Edit Ruang Kelas</h5>
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

    <!-- MODAL HAPUS RUANG -->
    <div class="modal fade" id="modalHapusRoom" tabindex="-1" aria-labelledby="modalHapusRoomLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusRoomLabel">Konfirmasi Hapus Ruang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=room&action=delete" method="POST">
                    <input type="hidden" id="hapusRoomId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus ruang <strong id="hapusRoomName">ini</strong>?</p>
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
                if (!el) return;
                if (el.type === 'checkbox') {
                    el.checked = !!data[key];
                } else {
                    el.value = data[key] ?? '';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            function initTable() {
                if (typeof DataTable === 'undefined') {
                    setTimeout(initTable, 50);
                    return;
                }
                if (document.getElementById('roomsTable') && !DataTable.isDataTable('#roomsTable')) {
                    new DataTable('#roomsTable', {
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        order: [[1, 'asc'], [2, 'asc'], [0, 'asc']],   // gedung, lantai, nama
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

            const modalTambahEl = document.getElementById('modalTambahRoom');
            const modalEditEl   = document.getElementById('modalEditRoom');
            const modalHapusEl  = document.getElementById('modalHapusRoom');

            // Modal tambah: kembalikan ke nilai awal setelah ditutup
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                modalTambahEl.querySelector('form').reset();
            });

            // Modal edit: isi form dari atribut data-room tombol Edit
            modalEditEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.room) {
                    fillForm(modalEditEl.querySelector('form'), JSON.parse(button.dataset.room));
                }
            });

            // Modal hapus
            modalHapusEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    document.getElementById('hapusRoomId').value = button.dataset.id || '';
                    document.getElementById('hapusRoomName').textContent =
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
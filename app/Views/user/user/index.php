<?php
$activeAdminPage = 'users';
$pageTitle = 'User - Admin JTI';
$pageHeading = 'Daftar User';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Keadaan form sebelum gagal (untuk membuka lagi modalnya). Tidak pernah berisi password.
$oldState = $_SESSION['old'] ?? null;
unset($_SESSION['old']);

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);
$selfId = strtolower((string) (Auth::user()['id'] ?? ''));

// Isi form dipakai bersama oleh modal tambah dan modal edit
$renderFields = function (bool $forEdit) use ($options, $h) { ?>
    <div class="mb-3">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="name" maxlength="100" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control" name="email" maxlength="150" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Role <span class="text-danger">*</span></label>
        <select class="form-select" name="role_id" required>
            <option value="">-- Pilih role --</option>
            <?php foreach ($options['roles'] as $r): ?>
                <option value="<?= $h($r['id']); ?>"><?= $h($r['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Kelas <span class="text-muted small">(opsional)</span></label>
        <select class="form-select" name="student_class_id">
            <option value="">-- Tidak ada --</option>
            <?php foreach ($options['classes'] as $c): ?>
                <option value="<?= $h($c['id']); ?>"><?= $h($c['name'] . ' (' . $c['academic_year'] . ')'); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">
            <?= $forEdit ? 'Password Baru' : 'Password'; ?>
            <?php if (!$forEdit): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <input type="password" class="form-control" name="password" autocomplete="new-password" minlength="8" maxlength="72"
            <?= $forEdit ? '' : 'required'; ?>>
        <div class="form-text">
            <?= $forEdit ? 'Kosongkan jika tidak ingin mengganti password. ' : ''; ?>Minimal 8 karakter.
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" name="password_confirm" autocomplete="new-password" maxlength="72"
            <?= $forEdit ? '' : 'required'; ?>>
    </div>
<?php };

include __DIR__ . '/../../admin/components/header.php';
include __DIR__ . '/../../admin/components/sidebar.php';
?>

<!-- BACKDROP SIDEBAR MOBILE -->
<div class="sidebar-scrim" id="sidebarScrim"></div>

<!-- MAIN CONTENT -->
<main class="main-area">

    <!-- NAVBAR -->
    <?php include __DIR__ . '/../../admin/components/navbar.php'; ?>

    <!-- TOAST NOTIFICATION -->
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-users">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar User</h3>
                        <p>Manajemen akun pengguna.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambahUser" <?= $canAdd ? '' : 'disabled'; ?>>
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah User
                        </button>
                    </div>
                </div>

                <?php if (!$canAdd): ?>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        User belum bisa dibuat. Tambahkan dulu minimal satu role.
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
                    <table id="usersTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Kelas</th>
                                <th>Dibuat</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $item):
                                $isSelf = strtolower($item['id']) === $selfId;
                                $created = substr((string) $item['created_at'], 0, 10);
                                $payload = [
                                    'id' => $item['id'],
                                    'name' => $item['name'],
                                    'email' => $item['email'],
                                    'role_id' => $item['role_id'],
                                    'student_class_id' => $item['student_class_id'],
                                ];
                                ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= $h($item['name']); ?></span>
                                        <?php if ($isSelf): ?><span
                                                class="badge bg-secondary ms-1">Anda</span><?php endif; ?>
                                    </td>
                                    <td><?= $h($item['email']); ?></td>
                                    <td><?= $h($item['role_name']); ?></td>
                                    <td>
                                        <?= $item['class_name']
                                            ? $h($item['class_name'] . ' (' . $item['academic_year'] . ')')
                                            : '-'; ?>
                                    </td>
                                    <td data-order="<?= $h($created); ?>">
                                        <?= $created ? $h(date('d/m/Y', strtotime($created))) : '-'; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                            data-bs-toggle="modal" data-bs-target="#modalEditUser"
                                            data-user="<?= $h(json_encode($payload)); ?>">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalHapusUser"
                                            data-id="<?= $h($item['id']); ?>"
                                            data-label="<?= $h($item['name'] . ' (' . $item['email'] . ')'); ?>"
                                            <?= $isSelf ? 'disabled title="Tidak bisa menghapus akun sendiri"' : ''; ?>>Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH USER -->
    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahUserLabel">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=users&action=store" method="POST" autocomplete="off">
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
    
    <!-- MODAL EDIT USER -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditUserLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=users&action=update" method="POST" autocomplete="off">
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
    
    <!-- MODAL HAPUS USER -->
    <div class="modal fade" id="modalHapusUser" tabindex="-1" aria-labelledby="modalHapusUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusUserLabel">Konfirmasi Hapus User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="index.php?controller=users&action=delete" method="POST">
                    <input type="hidden" id="hapusUserId" name="id">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus user <strong id="hapusUserName">ini</strong>?</p>
                        <p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak
                            dapat dibatalkan.</p>
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
                if (document.getElementById('usersTable') && !DataTable.isDataTable('#usersTable')) {
                    new DataTable('#usersTable', {
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

            const modalTambahEl = document.getElementById('modalTambahUser');
            const modalEditEl = document.getElementById('modalEditUser');
            const modalHapusEl = document.getElementById('modalHapusUser');

            // Modal tambah: kosongkan form (termasuk password) setelah ditutup
            modalTambahEl.addEventListener('hidden.bs.modal', function () {
                modalTambahEl.querySelector('form').reset();
            });

            // Modal edit: isi form dari data-user, password selalu dikosongkan
            modalEditEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button && button.dataset.user) {
                    const form = modalEditEl.querySelector('form');
                    fillForm(form, JSON.parse(button.dataset.user));
                    form.elements.password.value = '';
                    form.elements.password_confirm.value = '';
                }
            });

            // Modal hapus
            modalHapusEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    document.getElementById('hapusUserId').value = button.dataset.id || '';
                    document.getElementById('hapusUserName').textContent =
                        button.dataset.label ? '"' + button.dataset.label + '"' : 'ini';
                }
            });

            // Simpan gagal: buka lagi modal yang tadi dipakai (password harus diketik ulang)
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
    <?php include __DIR__ . '/../../admin/components/footer.php'; ?>
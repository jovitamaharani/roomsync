<?php
$activeAdminPage = 'lecturers';
$pageTitle = 'Daftar Dosen - Admin JTI';
$pageHeading = 'Daftar Dosen';

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
    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- PAGE CONTENT -->
    <section class="page-content">

        <div class="admin-page active" id="page-lecturers">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Dosen</h3>
                        <p>Informasi dosen Jurusan Teknologi Informasi Politeknik Negeri Malang.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importLecturers" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Dosen
                        </button>

                        <!-- Modal Add -->
                        <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="index.php?controller=lecturer&action=store" method="POST"
                                    class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fs-5" id="addModalLabel">Tambah Dosen</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'add'): ?>
                                            <div class="alert <?= $flash['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                                role="alert">
                                                <?= htmlspecialchars($flash['message']); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <label class="col-form-label" for="kode">Kode Dosen:</label>
                                            <input type="text" class="form-control" id="kode" name="kode"
                                                placeholder="Contoh: BS" maxlength="9"
                                                value="<?= htmlspecialchars($flash['old']['kode'] ?? ''); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="col-form-label" for="name">Nama Dosen:</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Contoh: Budi Santoso, S.Kom., M.Kom." maxlength="100"
                                                value="<?= htmlspecialchars($flash['old']['name'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="index.php?controller=lecturer&action=update" method="POST"
                                    class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="modal-title fs-5" id="editModalLabel">Edit Dosen</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if (!empty($flash) && ($flash['target'] ?? '') === 'edit'): ?>
                                            <div class="alert <?= $flash['type'] === 'error' ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show"
                                                role="alert">
                                                <?= htmlspecialchars($flash['message']); ?>
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                    aria-label="Close"></button>
                                            </div>
                                        <?php endif; ?>
                                        <input type="hidden" name="id" id="edit_id">

                                        <div class="mb-3">
                                            <label class="form-label" for="edit_kode">Kode Dosen:</label>
                                            <input type="text" class="form-control" id="edit_kode" maxlength="9"
                                                name="kode" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="edit_name">Nama Dosen:</label>
                                            <input type="text" class="form-control" id="edit_name" maxlength="100"
                                                name="name" required>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Import -->
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="index.php?controller=lecturer&action=import" method="POST"
                            enctype="multipart/form-data" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Impor Data Dosen</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info py-2">
                                    Gunakan template resmi agar format kolom sesuai.
                                    <a href="assets/templates/template_dosen.xlsx" class="fw-bold alert-link"
                                        download>Unduh Template</a>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="excel_file">Pilih Berkas (.xlsx / .csv):</label>
                                    <input type="file" class="form-control" id="excel_file" name="file"
                                        accept=".xlsx, .xls, .csv" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Unggah & Impor</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="lecturersTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Kode Dosen</th>
                                <th>Nama Dosen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lecturers)): ?>
                                <?php foreach ($lecturers as $item): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($item['kode']); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($item['name']); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark me-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= htmlspecialchars($item['id']); ?>"
                                                data-kode="<?= htmlspecialchars($item['kode']); ?>"
                                                data-name="<?= htmlspecialchars($item['name']); ?>">
                                                <i class="bi bi-pencil-square me-1"></i>Edit
                                            </button>

                                            <form action="index.php?controller=lecturer&action=delete" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data dosen ini?')">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($item['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash3 me-1"></i>Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-3">Data kosong.</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- FOOTER & SCRIPTS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editModalEl = document.getElementById('editModal');
            const addModalEl = document.getElementById('addModal');

            // 1. Event klik tombol Edit di tabel (buka manual oleh user)
            if (editModalEl) {
                editModalEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (button) {
                        document.getElementById('edit_id').value = button.getAttribute('data-id') || '';
                        document.getElementById('edit_kode').value = button.getAttribute('data-kode') || '';
                        document.getElementById('edit_name').value = button.getAttribute('data-name') || '';
                    }
                });
            }

            // 2. Buka modal kembali secara otomatis JIKA ada error validasi dari server
            <?php if (!empty($flash) && !empty($flash['target']) && $flash['type'] === 'error'): ?>

                <?php if ($flash['target'] === 'add'): ?>
                    // Jika yang gagal adalah form TAMBAH:
                    if (addModalEl) {
                        new bootstrap.Modal(addModalEl).show();
                    }

                <?php elseif ($flash['target'] === 'edit'): ?>
                    // Jika yang gagal adalah form EDIT (isi kembali teks lamanya):
                    <?php if (!empty($flash['old'])): ?>
                        document.getElementById('edit_id').value = <?= json_encode($flash['old']['id'] ?? ''); ?>;
                        document.getElementById('edit_kode').value = <?= json_encode($flash['old']['kode'] ?? ''); ?>;
                        document.getElementById('edit_name').value = <?= json_encode($flash['old']['name'] ?? ''); ?>;
                    <?php endif; ?>

                    if (editModalEl) {
                        new bootstrap.Modal(editModalEl).show();
                    }
                <?php endif; ?>

            <?php endif; ?>
        });
    </script>
    <?php include __DIR__ . '/../components/footer.php'; ?>
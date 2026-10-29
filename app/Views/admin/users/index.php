<?php
$activeAdminPage = 'users';
$pageTitle = 'Kelola Pengguna - Admin JTI';
$pageHeading = 'Kelola Pengguna';

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

        <div class="admin-page active" id="page-users">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Kelola Pengguna</h3>
                        <p>Manajemen akun admin, ketua kelas, dan headmaster sistem SIKARA.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importUsers" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="users">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Pengguna
                        </button>
                    </div>
                </div>

                <!-- FILTER TOOLBAR (INLINE DENGAN DATATABLES SEARCH) -->
                <div class="table-custom-filters d-none" data-table-filter-group="users">
                    <select class="form-select" id="filterPeranUsers" data-table="users" data-col="3">
                        <option value="">Semua Peran</option>
                        <option value="Admin">Admin</option>
                        <option value="Ketua Kelas">Ketua Kelas</option>
                        <option value="Headmaster">Headmaster</option>
                    </select>
                    <select class="form-select" id="filterStatusUsers" data-table="users" data-col="4">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="usersTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>NIM / NIP / Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH USER (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahUserLabel">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahUser" action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="usernameUser" class="form-label">NIM / NIP / Username</label>
                            <input type="text" class="form-control" id="usernameUser" name="username" placeholder="Contoh: 2341720001 atau 19870101..." required>
                        </div>
                        <div class="mb-3">
                            <label for="namaUser" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="namaUser" name="nama" placeholder="Contoh: Muhammad Rizky" required>
                        </div>
                        <div class="mb-3">
                            <label for="emailUser" class="form-label">Email</label>
                            <input type="email" class="form-control" id="emailUser" name="email" placeholder="Contoh: user@polinema.ac.id" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="peranUser" class="form-label">Peran (Role)</label>
                                <select class="form-select" id="peranUser" name="peran" required>
                                    <option value="" selected disabled>Pilih Peran</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Ketua Kelas">Ketua Kelas</option>
                                    <option value="Headmaster">Headmaster</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="statusUser" class="form-label">Status Akun</label>
                                <select class="form-select" id="statusUser" name="status" required>
                                    <option value="Aktif" selected>Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="passwordUser" class="form-label">Password</label>
                            <input type="password" class="form-control" id="passwordUser" name="password" placeholder="Minimal 6 karakter" required>
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

    <!-- MODAL EDIT USER (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditUserLabel">Edit Data Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditUser" action="" method="POST">
                    <input type="hidden" id="editRowIndexUser" name="row_index">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editUsernameUser" class="form-label">NIM / NIP / Username</label>
                            <input type="text" class="form-control" id="editUsernameUser" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaUser" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editNamaUser" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmailUser" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmailUser" name="email" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editPeranUser" class="form-label">Peran (Role)</label>
                                <select class="form-select" id="editPeranUser" name="peran" required>
                                    <option value="Admin">Admin</option>
                                    <option value="Ketua Kelas">Ketua Kelas</option>
                                    <option value="Headmaster">Headmaster</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editStatusUser" class="form-label">Status Akun</label>
                                <select class="form-select" id="editStatusUser" name="status" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editPasswordUser" class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                            <input type="password" class="form-control" id="editPasswordUser" name="password" placeholder="Isi untuk mengganti password">
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

    <!-- MODAL HAPUS DATA (BOOTSTRAP) -->
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formHapus" action="" method="POST">
                    <input type="hidden" id="hapusRowIndex" name="row_index">
                    <div class="modal-body">
                        <p class="mb-2">Apakah Anda yakin ingin menghapus data <strong id="hapusItemLabel">ini</strong>?</p>
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

    <!-- FOOTER & SCRIPTS -->
    <?php include __DIR__ . '/../components/footer.php'; ?>

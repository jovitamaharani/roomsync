<?php
$activeAdminPage = 'rooms';
$pageTitle = 'Daftar Ruang Kelas - Admin JTI';
$pageHeading = 'Daftar Ruang Kelas';

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

        <div class="admin-page active" id="page-rooms">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Ruang Kelas</h3>
                        <p>Manajemen data ruangan (rooms), gedung, dan status ketersediaan.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importRooms" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="rooms">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRuang">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Ruang
                        </button>
                    </div>
                </div>

                <!-- FILTER TOOLBAR (INLINE DENGAN DATATABLES SEARCH) -->
                <div class="table-custom-filters d-none" data-table-filter-group="rooms">
                    <select class="form-select" id="filterGedungRooms" data-table="rooms" data-col="1">
                        <option value="">Semua Gedung</option>
                        <option value="Gedung TI">Gedung TI</option>
                        <option value="Gedung Sipil">Gedung Sipil</option>
                    </select>
                    <select class="form-select" id="filterStatusRooms" data-table="rooms" data-col="2">
                        <option value="">Semua Status</option>
                        <option value="KOSONG">KOSONG</option>
                        <option value="TERPAKAI">TERPAKAI</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="roomsTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama Ruang</th>
                                <th>Gedung</th>
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

    <!-- MODAL TAMBAH RUANG (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahRuang" tabindex="-1" aria-labelledby="modalTambahRuangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahRuangLabel">Tambah Ruang Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahRuang" action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaRuang" class="form-label">Nama Ruang</label>
                            <input type="text" class="form-control" id="namaRuang" name="nama" placeholder="Contoh: RT 01 atau Lab RPL 1" required>
                        </div>
                        <div class="mb-3">
                            <label for="gedungRuang" class="form-label">Gedung</label>
                            <input type="text" class="form-control" id="gedungRuang" name="gedung" placeholder="Contoh: Gedung TI" required>
                        </div>
                        <div class="mb-3">
                            <label for="statusRuang" class="form-label">Status Ketersediaan</label>
                            <select class="form-select" id="statusRuang" name="status" required>
                                <option value="KOSONG" selected>KOSONG</option>
                                <option value="TERPAKAI">TERPAKAI</option>
                            </select>
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

    <!-- MODAL EDIT RUANG (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditRuang" tabindex="-1" aria-labelledby="modalEditRuangLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditRuangLabel">Edit Ruang Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditRuang" action="" method="POST">
                    <input type="hidden" id="editRowIndexRuang" name="row_index">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNamaRuang" class="form-label">Nama Ruang</label>
                            <input type="text" class="form-control" id="editNamaRuang" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="editGedungRuang" class="form-label">Gedung</label>
                            <input type="text" class="form-control" id="editGedungRuang" name="gedung" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStatusRuang" class="form-label">Status Ketersediaan</label>
                            <select class="form-select" id="editStatusRuang" name="status" required>
                                <option value="KOSONG">KOSONG</option>
                                <option value="TERPAKAI">TERPAKAI</option>
                            </select>
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

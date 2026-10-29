<?php
$activeAdminPage = 'classes';
$pageTitle = 'Daftar Kelas - Admin JTI';
$pageHeading = 'Daftar Kelas';

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

        <div class="admin-page active" id="page-classes">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Kelas Mahasiswa</h3>
                        <p>Manajemen data kelas mahasiswa (student_class) dan relasi tahun akademik.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importClasses" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="classes">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Kelas
                        </button>
                    </div>
                </div>

                <!-- FILTER TOOLBAR (INLINE DENGAN DATATABLES SEARCH) -->
                <div class="table-custom-filters d-none" data-table-filter-group="classes">
                    <select class="form-select" id="filterTahunClasses" data-table="classes" data-col="1">
                        <option value="">Semua Tahun Akademik</option>
                        <option value="2026/2027 Ganjil">2026/2027 Ganjil</option>
                        <option value="2025/2026 Genap">2025/2026 Genap</option>
                        <option value="2025/2026 Ganjil">2025/2026 Ganjil</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="classesTable" class="display table table-striped table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Tahun Akademik</th>
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH KELAS (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="modalTambahKelasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahKelasLabel">Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahKelas" action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaKelas" class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control" id="namaKelas" name="nama_kelas" placeholder="Contoh: TI-2F" required>
                        </div>
                        <div class="mb-3">
                            <label for="tahunAkademikKelas" class="form-label">Tahun Akademik</label>
                            <select class="form-select" id="tahunAkademikKelas" name="tahun_akademik" required>
                                <option value="" selected disabled>Pilih Tahun Akademik</option>
                                <option value="2026/2027 Ganjil">2026/2027 Ganjil</option>
                                <option value="2025/2026 Genap">2025/2026 Genap</option>
                                <option value="2025/2026 Ganjil">2025/2026 Ganjil</option>
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

    <!-- MODAL EDIT KELAS (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditKelas" tabindex="-1" aria-labelledby="modalEditKelasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditKelasLabel">Edit Data Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditKelas" action="" method="POST">
                    <input type="hidden" id="editRowIndexKelas" name="row_index">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNamaKelas" class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control" id="editNamaKelas" name="nama_kelas" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTahunAkademikKelas" class="form-label">Tahun Akademik</label>
                            <select class="form-select" id="editTahunAkademikKelas" name="tahun_akademik" required>
                                <option value="2026/2027 Ganjil">2026/2027 Ganjil</option>
                                <option value="2025/2026 Genap">2025/2026 Genap</option>
                                <option value="2025/2026 Ganjil">2025/2026 Ganjil</option>
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

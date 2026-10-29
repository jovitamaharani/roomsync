<?php
$activeAdminPage = 'courses';
$pageTitle = 'Daftar Mata Kuliah - Admin JTI';
$pageHeading = 'Daftar Mata Kuliah';

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

        <div class="admin-page active" id="page-courses">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Daftar Mata Kuliah</h3>
                        <p>Manajemen data mata kuliah (courses) Jurusan Teknologi Informasi sesuai kurikulum.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importCourses" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="courses">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKursus">
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
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH MATA KULIAH (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahKursus" tabindex="-1" aria-labelledby="modalTambahKursusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahKursusLabel">Tambah Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahKursus" action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="kodeMatkul" class="form-label">Kode MK</label>
                            <input type="text" class="form-control" id="kodeMatkul" name="kode" placeholder="Contoh: TIF201" required>
                        </div>
                        <div class="mb-3">
                            <label for="namaMatkul" class="form-label">Nama Mata Kuliah</label>
                            <input type="text" class="form-control" id="namaMatkul" name="nama_matkul" placeholder="Contoh: Desain dan Pemrograman Web" required>
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

    <!-- MODAL EDIT MATA KULIAH (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditKursus" tabindex="-1" aria-labelledby="modalEditKursusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditKursusLabel">Edit Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditKursus" action="" method="POST">
                    <input type="hidden" id="editRowIndexKursus" name="row_index">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editKodeMatkul" class="form-label">Kode MK</label>
                            <input type="text" class="form-control" id="editKodeMatkul" name="kode" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNamaMatkul" class="form-label">Nama Mata Kuliah</label>
                            <input type="text" class="form-control" id="editNamaMatkul" name="nama_matkul" required>
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

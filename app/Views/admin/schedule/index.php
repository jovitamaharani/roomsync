<?php
$activeAdminPage = 'schedules';
$pageTitle = 'Jadwal Mata Kuliah - Admin JTI';
$pageHeading = 'Jadwal Mata Kuliah';

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

        <div class="admin-page active" id="page-schedules">
            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h3>Jadwal Mata Kuliah</h3>
                        <p>Informasi jadwal perkuliahan dan penggunaan ruang kelas berdasarkan kurikulum akademik.</p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <input type="file" id="importSchedules" accept=".xlsx,.xls,.csv" hidden>
                        <button class="btn btn-outline-primary" data-import="schedules">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            Impor Excel
                        </button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                            <i class="bi bi-plus-lg me-1"></i>
                            Tambah Jadwal
                        </button>
                    </div>
                </div>

                <!-- FILTER TOOLBAR (INLINE DENGAN DATATABLES SEARCH) -->
                <div class="table-custom-filters d-none" data-table-filter-group="schedules">
                    <select class="form-select" id="filterHariSchedules" data-table="schedules" data-col="0">
                        <option value="">Semua Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                    <select class="form-select" id="filterKelasSchedules" data-table="schedules" data-col="4">
                        <option value="">Semua Kelas</option>
                        <option value="TI-1A">TI-1A</option>
                        <option value="TI-1B">TI-1B</option>
                        <option value="TI-2E">TI-2E</option>
                        <option value="TI-2F">TI-2F</option>
                        <option value="TI-3F">TI-3F</option>
                        <option value="SIB-1A">SIB-1A</option>
                        <option value="SIB-2A">SIB-2A</option>
                    </select>
                    <select class="form-select" id="filterRuangSchedules" data-table="schedules" data-col="5">
                        <option value="">Semua Ruangan</option>
                        <option value="RT 01">RT 01</option>
                        <option value="RT 02">RT 02</option>
                        <option value="RT 03">RT 03</option>
                        <option value="RT 04">RT 04</option>
                        <option value="RT 05">RT 05</option>
                        <option value="Lab RPL 1">Lab RPL 1</option>
                        <option value="Lab RPL 2">Lab RPL 2</option>
                        <option value="Lab Jaringan">Lab Jaringan</option>
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
                                <th class="text-center" style="width: 170px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

    </section>

    <!-- MODAL TAMBAH JADWAL (BOOTSTRAP) -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-labelledby="modalTambahJadwalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahJadwalLabel">Tambah Jadwal Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formTambahJadwal" action="" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hariJadwal" class="form-label">Hari</label>
                                <select class="form-select" id="hariJadwal" name="hari" required>
                                    <option value="" selected disabled>Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jamJadwal" class="form-label">Jam Kuliah</label>
                                <input type="text" class="form-control" id="jamJadwal" name="jam" placeholder="Contoh: 08:00–11:30" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="matkulJadwal" class="form-label">Mata Kuliah</label>
                            <input type="text" class="form-control" id="matkulJadwal" name="mata_kuliah" placeholder="Contoh: Desain dan Pemrograman Web" required>
                        </div>
                        <div class="mb-3">
                            <label for="dosenJadwal" class="form-label">Dosen Pengampu</label>
                            <input type="text" class="form-control" id="dosenJadwal" name="dosen" placeholder="Contoh: Dimas Wahyu Wibowo, S.T., M.T." required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kelasJadwal" class="form-label">Kelas</label>
                                <input type="text" class="form-control" id="kelasJadwal" name="kelas" placeholder="Contoh: TI-2F" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ruangJadwal" class="form-label">Ruang</label>
                                <input type="text" class="form-control" id="ruangJadwal" name="ruang" placeholder="Contoh: RT 02" required>
                            </div>
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

    <!-- MODAL EDIT JADWAL (BOOTSTRAP) -->
    <div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-labelledby="modalEditJadwalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditJadwalLabel">Edit Jadwal Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formEditJadwal" action="" method="POST">
                    <input type="hidden" id="editRowIndexJadwal" name="row_index">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editHariJadwal" class="form-label">Hari</label>
                                <select class="form-select" id="editHariJadwal" name="hari" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editJamJadwal" class="form-label">Jam Kuliah</label>
                                <input type="text" class="form-control" id="editJamJadwal" name="jam" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editMatkulJadwal" class="form-label">Mata Kuliah</label>
                            <input type="text" class="form-control" id="editMatkulJadwal" name="mata_kuliah" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDosenJadwal" class="form-label">Dosen Pengampu</label>
                            <input type="text" class="form-control" id="editDosenJadwal" name="dosen" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editKelasJadwal" class="form-label">Kelas</label>
                                <input type="text" class="form-control" id="editKelasJadwal" name="kelas" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editRuangJadwal" class="form-label">Ruang</label>
                                <input type="text" class="form-control" id="editRuangJadwal" name="ruang" required>
                            </div>
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

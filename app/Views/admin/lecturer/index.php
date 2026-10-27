<div class="admin-page" id="page-lecturers">
    <div class="content-card">

        <div class="card-heading">
            <div>
                <h3>Daftar Dosen</h3>
                <p>Informasi dosen Jurusan Teknologi Informasi.</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <input type="file" id="importLecturers" accept=".xlsx,.xls,.csv" hidden>
                <button class="btn btn-outline-primary" data-import="lecturers">
                    <i class="bi bi-file-earmark-excel me-1"></i>
                    Impor Excel
                </button>
                <button class="btn btn-primary" data-add="lecturers">
                    <i class="bi bi-plus-lg me-1"></i>
                    Tambah Dosen
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="lecturersTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>NIDN</th>
                        <th>Nama Dosen</th>
                        <th>Email</th>
                        <th>Program Studi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
$(document).ready(function () {
    const $table = $('#userOccupancyTable');
    if (!$table.length) return;

    // 1. Inisialisasi Select2
    if ($.fn.select2) {
        $('#logFilterType').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Jenis Riwayat',
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        $('#logFilterRoom').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Ruangan',    
            allowClear: true
        });
    }

    // 2. Inisialisasi DataTables
    const table = $table.DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        columnDefs: [
            { targets: 6, orderable: false, searchable: false }
        ],
        dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center mt-3"ip>',
        language: {
            emptyTable: "Belum ada riwayat penggunaan ruangan pada periode ini.",
            zeroRecords: "Tidak ada riwayat aktivitas yang cocok dengan filter.",
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ riwayat',
            infoEmpty: "",
            infoFiltered: "",
            paginate: {
                previous: "Sebelumnya",
                next: "Berikutnya"
            }
        }
    });

    // 3. Pencarian Kata Kunci Global
    $('#logSearchKeyword').on('input', function () {
        table.search(this.value).draw();
    });

    // 4. Filter Jenis Transaksi (Kolom 2)
    $('#logFilterType').on('change', function () {
        const val = $(this).val();
        if (!val || val === 'ALL') {
            table.column(2).search('').draw();
        } else {
            const labelMap = {
                'booking': 'Peminjaman',
                'schedule': 'Jadwal Kuliah',
                'bubar': 'Bubar Kelas',
                'other': 'Lainnya'
            };
            table.column(2).search(labelMap[val] || val).draw();
        }
    });

    // 5. Filter Ruangan (Kolom 1)
    $('#logFilterRoom').on('change', function () {
        const val = $(this).val();
        if (!val || val === 'ALL') {
            table.column(1).search('').draw();
        } else {
            table.column(1).search(val).draw();
        }
    });

    // 6. Tombol Reset Filter
    $('#btnResetFilter').on('click', function () {
        $('#logSearchKeyword').val('');
        if ($.fn.select2) {
            $('#logFilterType').val('ALL').trigger('change.select2');
            $('#logFilterRoom').val('ALL').trigger('change.select2');
        } else {
            $('#logFilterType').val('ALL');
            $('#logFilterRoom').val('ALL');
        }
        table.search('').columns().search('').draw();
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;
    const tableEl = document.getElementById('masterScheduleTable');
    if (!$ || !$.fn.DataTable || !tableEl) return;

    // Ambil pesan dinamis dari data-attribute tabel
    const emptyMsg = tableEl.dataset.emptyMsg || 'Tidak ada jadwal perkuliahan hari ini.';

    // 1. Inisialisasi DataTables
    const table = $('#masterScheduleTable').DataTable({
        dom: 't<"schedule-dt-footer"i>',
        paging: false,
        order: [[0, 'asc']],
        autoWidth: false,
        language: {
            emptyTable: emptyMsg,
            zeroRecords: 'Tidak ada jadwal yang cocok dengan filter.',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ jadwal',
            infoEmpty: 'Tidak ada jadwal',
            infoFiltered: '(disaring dari _MAX_ jadwal)'
        }
    });

    // 2. Elemen Kontrol Filter
    const el = {
        q: document.getElementById('scheduleSearch'),
        room: document.getElementById('scheduleRoomFilter'),
        kelas: document.getElementById('scheduleClassFilter'),
        status: document.getElementById('scheduleStatusFilter'),
        reset: document.getElementById('scheduleResetBtn')
    };

    const exact = (value) => {
        return value ? '^\\s*' + value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*$' : '';
    };

    const applyFilters = () => {
        if (el.q) table.search(el.q.value);
        if (el.room) table.column(1).search(exact(el.room.value), true, false);
        if (el.kelas) table.column(4).search(exact(el.kelas.value), true, false);
        if (el.status) table.column(5).search(exact(el.status.value), true, false);
        table.draw();
    };

    if (el.q) el.q.addEventListener('input', applyFilters);
    if (el.status) el.status.addEventListener('change', applyFilters);

    // 3. Inisialisasi Searchable Select (Mendukung Select2 atau TomSelect)
    if (typeof $.fn.select2 === 'function') {
        $('.js-searchable-select').each(function () {
            const $this = $(this);
            $this.select2({
                theme: 'bootstrap-5',
                placeholder: $this.find('option:first').text() || 'Pilih opsi',
                allowClear: true,
                width: '100%'
            });
        });
        $('.js-searchable-select').on('change', applyFilters);
    } else if (window.TomSelect) {
        document.querySelectorAll('select.js-searchable-select').forEach((sel) => {
            new TomSelect(sel, {
                placeholder: sel.options.length ? sel.options[0].text : '',
                maxOptions: null,
                plugins: { clear_button: { title: 'Hapus pilihan' } },
                onChange: applyFilters
            });
        });
    } else {
        if (el.room) el.room.addEventListener('change', applyFilters);
        if (el.kelas) el.kelas.addEventListener('change', applyFilters);
    }

    // 4. Tombol Reset
    if (el.reset) {
        el.reset.addEventListener('click', function () {
            if (el.q) el.q.value = '';
            if (el.status) el.status.value = '';

            if (typeof $.fn.select2 === 'function') {
                $('#scheduleRoomFilter').val('').trigger('change.select2');
                $('#scheduleClassFilter').val('').trigger('change.select2');
            } else {
                if (el.room) {
                    if (el.room.tomselect) el.room.tomselect.clear(true);
                    else el.room.value = '';
                }
                if (el.kelas) {
                    if (el.kelas.tomselect) el.kelas.tomselect.clear(true);
                    else el.kelas.value = '';
                }
            }

            applyFilters();
        });
    }

    // Jalankan filter awal jika ada filter ruangan dari parameter URL
    if (el.room && el.room.value) {
        applyFilters();
    }
});
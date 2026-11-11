document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('logSearchKeyword');
    const typeSelect = document.getElementById('logFilterType');
    const roomSelect = document.getElementById('logFilterRoom');
    const allRows = Array.from(document.querySelectorAll('#auditLogTableBody tr.log-row'));
    const paginationNav = document.getElementById('roomPaginationNav');
    const paginationInfo = document.getElementById('logPaginationInfo');
    const tableBody = document.getElementById('auditLogTableBody');

    const pageSize = 10;
    let currentPage = 1;
    let filteredRows = [...allRows];

    // 1. Inisialisasi Select2
    if (window.jQuery && $.fn.select2) {
        // Dropdown Jenis Aktivitas
        $('#logFilterType').select2({
            width: '100%',
            minimumResultsForSearch: Infinity,
            placeholder: {
                id: 'ALL',
                text: 'Semua Jenis Riwayat'
            },
            allowClear: true
        });

        // Dropdown Ruangan 
        $('#logFilterRoom').select2({
            width: '100%',
            placeholder: {
                id: 'ALL',
                text: 'Semua Ruangan'
            },
            allowClear: true
        });

        // Trigger filter saat Select2 berubah atau di-clear
        $('#logFilterType, #logFilterRoom').on('change select2:clear', function () {
            filterAndPaginate();
        });
    }

    // 2. Logika Filter Data
    function filterAndPaginate() {
        const keyword = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedType = typeSelect ? typeSelect.value : 'ALL';
        const selectedRoom = (roomSelect ? roomSelect.value : 'ALL').toLowerCase().trim();

        filteredRows = allRows.filter(row => {
            const text = row.innerText.toLowerCase();
            const source = (row.getAttribute('data-source') || '').toLowerCase().trim();
            const room = (row.getAttribute('data-room') || '').toLowerCase().trim();

            const matchKeyword = !keyword || text.includes(keyword);
            const matchType = (selectedType === 'ALL') || (source === selectedType.toLowerCase());
            const matchRoom = (selectedRoom === 'all') || (room === selectedRoom);

            return matchKeyword && matchType && matchRoom;
        });

        currentPage = 1;
        renderView();
    }

    // 3. Render Baris & Info Pagination
    function renderView() {
        allRows.forEach(row => row.style.display = 'none');

        const existingNoData = document.getElementById('noDataRow');
        if (existingNoData) existingNoData.remove();

        const totalItems = filteredRows.length;
        const totalPages = Math.ceil(totalItems / pageSize);

        if (totalItems === 0) {
            const noData = document.createElement('tr');
            noData.id = 'noDataRow';
            noData.innerHTML = '<td colspan="6" class="text-center py-4 text-muted">Tidak ada riwayat aktivitas yang cocok dengan filter.</td>';
            tableBody.appendChild(noData);
            if (paginationNav) paginationNav.innerHTML = '';
            if (paginationInfo) paginationInfo.innerText = 'Showing 0 to 0 of 0 entries';
            return;
        }

        const start = (currentPage - 1) * pageSize;
        const end = Math.min(start + pageSize, totalItems);
        const pageRows = filteredRows.slice(start, end);
        pageRows.forEach(row => row.style.display = '');

        // Tampilkan Info Pagination
        if (paginationInfo) {
            paginationInfo.innerText = `Showing ${start + 1} to ${end} of ${totalItems} entries`;
        }

        renderPagination(totalPages);
    }

    // 4. Render Tombol Navigasi Pagination
    function renderPagination(totalPages) {
        if (!paginationNav) return;
        paginationNav.innerHTML = '';
        if (totalPages <= 1) return;

        // Tombol Sebelumnya
        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'pagination-button';
        prevBtn.innerText = 'Sebelumnya';
        prevBtn.disabled = (currentPage === 1);
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderView();
            }
        });
        paginationNav.appendChild(prevBtn);

        // Nomor Halaman
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = 'pagination-button' + (i === currentPage ? ' active' : '');
            pageBtn.innerText = i;
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                renderView();
            });
            paginationNav.appendChild(pageBtn);
        }

        // Tombol Berikutnya
        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'pagination-button';
        nextBtn.innerText = 'Berikutnya';
        nextBtn.disabled = (currentPage === totalPages);
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderView();
            }
        });
        paginationNav.appendChild(nextBtn);
    }

    // Event listener input keyword
    if (searchInput) {
        searchInput.addEventListener('input', filterAndPaginate);
    }

    // Jalankan pertama kali saat halaman dibuka
    filterAndPaginate();

    const btnReset = document.getElementById('btnResetFilter');

    if (btnReset) {
        btnReset.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';

            if (window.jQuery && $.fn.select2) {
                $('#logFilterType').val('ALL').trigger('change.select2');
                $('#logFilterRoom').val('ALL').trigger('change.select2');
            } else {
                if (typeSelect) typeSelect.value = 'ALL';
                if (roomSelect) roomSelect.value = 'ALL';
            }

            filterAndPaginate();
        });
    }
});
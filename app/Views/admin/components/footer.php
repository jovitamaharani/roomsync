<?php
$possibleJsPaths = [
    'admin.js',
    '../admin.js',
    '../../admin.js',
    'admin/admin.js',
    'assets/js/admin.js',
    '../assets/js/admin.js',
    '../../assets/js/admin.js'
];
$adminJsSrc = '../admin.js';
foreach ($possibleJsPaths as $p) {
    if (file_exists($p)) {
        $adminJsSrc = $p;
        break;
    }
}
?>
        </main>
    </div>

    <!-- JAVASCRIPT LIBRARY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables 2 JS & Extensions (https://datatables.net/) -->
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <!-- JAVASCRIPT ADMIN -->
    <script src="<?= $adminJsSrc ?>"></script>
    <script>
        // Fallback jika belum termuat
        if (typeof window.sikaraAdminLoaded === 'undefined') {
            const fallbackScript = document.createElement('script');
            fallbackScript.src = '../admin.js';
            document.body.appendChild(fallbackScript);
        }
    </script>

</body>
</html>

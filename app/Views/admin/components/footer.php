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
            <footer class="admin-footer">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <b>SIKARA</b> &middot; Jurusan Teknologi Informasi Politeknik Negeri Malang.
                    <span class="d-none d-md-inline">Sistem Informasi Ketersediaan dan Alokasi Ruang.</span>
                </p>
            </footer>

        </main>
    </div>

    <div id="toastArea" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>

    <!-- MODAL TAMBAH DATA (SIMULASI) -->
    <?php include __DIR__ . '/modal_data.php'; ?>

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

    <!-- JAVASCRIPT ADMIN (Dukungan rute maupun akses langsung) -->
    <script src="<?= $adminJsSrc ?>"></script>
    <script>
        // Fallback jika belum termuat
        if (typeof window.sikaraAdminLoaded === 'undefined') {
            const fallbackScript = document.createElement('script');
            fallbackScript.src = '../admin.js';
            document.body.appendChild(fallbackScript);
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($flash) && empty($flash['target'])): ?>
            if (typeof showToast === 'function') {
                showToast(<?= json_encode($flash['message']); ?>, <?= json_encode($flash['type'] === 'error'); ?>);
            }
        <?php endif; ?>
    });
    </script>

</body>
</html>

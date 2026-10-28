<?php
$pageHeading = $pageHeading ?? 'Ringkasan';
?>
<header class="topbar">

    <div class="d-flex align-items-center gap-3">
        <button id="sidebarToggle" class="btn btn-light d-lg-none" aria-label="Buka menu" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div>
            <small class="text-secondary">
                Jurusan Teknologi Informasi - Politeknik Negeri Malang
            </small>
            <h1 id="pageTitle"><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
    </div>

</header>

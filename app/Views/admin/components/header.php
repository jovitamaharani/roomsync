<?php
// Header Layout Admin SIKARA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulasi sesi admin tanpa pemblokiran
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'Administrator JTI',
        'email' => 'admin@sikara.test',
        'role' => 'admin'
    ];
}

$pageTitle = $pageTitle ?? 'Admin JTI - SIKARA';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS (https://datatables.net/) -->
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.dataTables.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- CSS Admin di assets/css/ (Mendukung akses dari root proyek maupun langsung dari folder admin) -->
<?php
$possibleCssPaths = [
    'assets/css/admin.css',
    '../assets/css/admin.css',
    '../../assets/css/admin.css',
    '../../../assets/css/admin.css',
    '../../../../assets/css/admin.css'
];
$adminCssPath = '../../assets/css/admin.css';
foreach ($possibleCssPaths as $p) {
    if (file_exists($p)) {
        $adminCssPath = $p;
        break;
    }
}
?>
    <link href="<?= $adminCssPath ?>" rel="stylesheet">
</head>

<body>
    <div class="admin-layout">

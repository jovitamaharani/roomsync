<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'RIMORA - Room Information and Management of Reservations and Activities';
$basePath = $basePath ?? '../';

// Resolve CSS path dynamically
$possibleCssPaths = [
    'assets/css/styles.css',
    '../../assets/css/styles.css',
    '../assets/css/styles.css',
    $basePath . 'styles.css',
    $basePath . 'assets/css/styles.css',
    'styles.css',
    '../styles.css',
    '../../styles.css',
    '../../../assets/css/styles.css'
];
$stylesCssHref = 'assets/css/styles.css';
foreach ($possibleCssPaths as $p) {
    if (file_exists($p)) {
        $stylesCssHref = $p;
        break;
    }
}

$possibleLandingCss = [
    'assets/css/landing-reference.css',
    '../../assets/css/landing-reference.css',
    '../assets/css/landing-reference.css',
    $basePath . 'landing-reference.css',
    $basePath . 'assets/css/landing-reference.css',
    'landing-reference.css',
    '../landing-reference.css',
    '../../assets/css/landing-reference.css'
];
$landingCssHref = 'assets/css/landing-reference.css';
foreach ($possibleLandingCss as $p) {
    if (file_exists($p)) {
        $landingCssHref = $p;
        break;
    }
}

date_default_timezone_set('Asia/Jakarta');

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= $stylesCssHref ?>">
    <link rel="stylesheet" href="<?= $landingCssHref ?>">
</head>

<body>
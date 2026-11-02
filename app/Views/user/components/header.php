<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'SIKARA - Sistem Informasi Ketersediaan dan Alokasi Ruang';
$basePath = $basePath ?? '../';

// Resolve CSS path dynamically
$possibleCssPaths = [
    $basePath . 'styles.css',
    $basePath . 'assets/css/styles.css',
    'styles.css',
    '../styles.css',
    '../../styles.css',
    'assets/css/styles.css',
    '../assets/css/styles.css',
    '../../assets/css/styles.css',
    '../../../assets/css/styles.css'
];
$stylesCssHref = $basePath . 'styles.css';
foreach ($possibleCssPaths as $p) {
    if (file_exists($p)) {
        $stylesCssHref = $p;
        break;
    }
}

$possibleLandingCss = [
    $basePath . 'landing-reference.css',
    $basePath . 'assets/css/landing-reference.css',
    'landing-reference.css',
    '../landing-reference.css',
    'assets/css/landing-reference.css',
    '../assets/css/landing-reference.css',
    '../../assets/css/landing-reference.css'
];
$landingCssHref = $basePath . 'landing-reference.css';
foreach ($possibleLandingCss as $p) {
    if (file_exists($p)) {
        $landingCssHref = $p;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $stylesCssHref ?>">
  <link rel="stylesheet" href="<?= $landingCssHref ?>">
</head>
<body>

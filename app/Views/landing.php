<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>landing page</h1>
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); ?>
        <p><?= htmlspecialchars($flash['message']); ?></p>
    <?php endif; ?>

    <?php if ($user): ?>
        <span><?= htmlspecialchars($user['name']); ?> (<?= htmlspecialchars($user['role']); ?>)</span>
        <form action="index.php?controller=auth&action=logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()); ?>">
            <button type="submit">Keluar</button>
        </form>
    <?php else: ?>
        <a href=" index.php?controller=auth&action=login">Masuk</a>
    <?php endif; ?>
</body>

</html>
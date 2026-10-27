<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Tahun Ajaran</title>
</head>

<body>
    <h2>Daftar Tahun Ajaran</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green'; ?>">
            <?= htmlspecialchars($flash['message']); ?>
        </p>
    <?php endif; ?>

    <a href="index.php?controller=academic_year&action=create">+ Tambah Tahun Ajaran</a>
    <br><br>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Tahun Ajaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($academicYear)): ?>
                <?php foreach ($academicYear as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?></td>
                        <td>
                            <a href="index.php?controller=academic_year&action=edit&id=<?= urlencode($item['id']); ?>">Edit</a> |
                            <a href="index.php?controller=academic_year&action=delete&id=<?= urlencode($item['id']); ?>"
                                onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Data kosong.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>
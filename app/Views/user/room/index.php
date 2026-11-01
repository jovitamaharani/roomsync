<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Ruangan</title>
</head>
<body>
    <h2>Daftar Ruangan</h2>
    <a href="index.php?controller=home&action=index">Kembali</a>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green'; ?>">
            <?= htmlspecialchars($flash['message']); ?>
        </p>
    <?php endif; ?>

    <br>
    <a href="index.php?controller=room&action=create">Tambah Ruangan...</a>
    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Gedung</th>
                <th>Lantai</th>
                <th>Kapasitas</th>
                <th>Kursi</th>
                <th>Meja</th>
                <th>Fasilitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rooms)): ?>
                <?php foreach ($rooms as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['building']); ?></td>
                        <td><?= (int) $item['floor']; ?></td>
                        <td><?= (int) $item['capacity']; ?></td>
                        <td><?= (int) $item['chairs']; ?></td>
                        <td><?= (int) $item['tables']; ?></td>
                        <td><?= htmlspecialchars($item['facilities'] ?? '-'); ?></td>
                        <td><?= $item['is_active'] ? 'Aktif' : 'Nonaktif'; ?></td>
                        <td>
                            <a href="index.php?controller=room&action=edit&id=<?= urlencode($item['id']); ?>">Edit</a> |
                            <a href="index.php?controller=room&action=delete&id=<?= urlencode($item['id']); ?>"
                               onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">Data kosong.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
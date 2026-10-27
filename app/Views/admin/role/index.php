<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Role</title>
</head>
<body>
    <h2>Daftar Role</h2>
    <a href="index.php?controller=role&action=create">+ Tambah Role</a>
    <br><br>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID (UUID)</th>
                <th>Nama Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($roles)): ?>
                <?php foreach ($roles as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id']); ?></td>
                    <td><?= htmlspecialchars($item['name']); ?></td>
                    <td>
                        <a href="index.php?controller=role&action=edit&id=<?= $item['id']; ?>">Edit</a> | 
                        <a href="index.php?controller=role&action=delete&id=<?= $item['id']; ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">Data kosong.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
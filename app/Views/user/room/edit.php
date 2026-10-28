<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Ruangan</title>
</head>
<body>
    <h2>Tambah Ruangan</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green'; ?>">
            <?= htmlspecialchars($flash['message']); ?>
        </p>
    <?php endif; ?>

    <form action="index.php?controller=room&action=update" method="POST">
        <input type="hidden", name="id" value="<?= htmlspecialchars($values['id'] ?? ''); ?>">

        <label>Nama ruangan:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($values['name'] ?? ''); ?>" placeholder="shfdsbfdhsbf" maxlength="100" required><br><br>

        <label>Gedung:</label><br>
        <input type="text" name="building" value="<?= htmlspecialchars($values['building'] ?? ''); ?>" placeholder="shfdsbfdhsbf" maxlength="100" required><br><br>

        <label>Lantai:</label><br>
        <input type="number" name="floor" value="<?= htmlspecialchars($values['floor'] ?? ''); ?>" placeholder="shfdsbfdhsbf" min="1" max="100" required><br><br>

        <label>Kapasitas:</label><br>
        <input type="number" name="capacity" value="<?= htmlspecialchars($values['capacity'] ?? ''); ?>" min="1" max="67" required><br><br>

        <label>Jumlah kursi:</label><br>
        <input type="number" name="chairs" value="<?= htmlspecialchars($values['chairs'] ?? 0); ?>" min="0" required><br><br>

        <label>Jumlah meja:</label><br>
        <input type="number" name="tables" value="<?= htmlspecialchars($values['tables'] ?? 0); ?>" min="0" required><br><br>

        <label>Fasilitas:</label><br>
        <textarea name="facilities" rows="3" cols="40"
                  placeholder="Contoh: Proyektor, AC, Whiteboard"><?= htmlspecialchars($values['facilities'] ?? ''); ?></textarea><br><br>

        <label>
            <input type="checkbox" name="is_active" <?= !empty($values['is_active']) ? 'checked' : ''; ?>> Aktif
        </label><br><br>

        <button type="submit">Simpan</button>
        <a href="index.php?controller=room&action=index">Batal</a>
    </form>
</body>
</html>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Tahun Ajaran</title>
</head>

<body>
    <h2>Edit Tahun Ajaran</h2>
    <form action="index.php?controller=class_year&action=update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($classYear['id']); ?>">

        <label>Tahun Ajaran:</label><br>
        <input type="text" name="academic_year" maxlength="9"
            value="<?= htmlspecialchars($classYear['academic_year']); ?>" required><br><br>

        <label>
            <input type="checkbox" name="is_active" <?= $classYear['is_active'] ? 'checked' : ''; ?>> Aktif
        </label><br><br>

        <button type="submit">Update</button>
        <a href="index.php?controller=class_year&action=index">Batal</a>
    </form>
</body>

</html>
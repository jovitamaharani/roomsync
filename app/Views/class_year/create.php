<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Tahun Ajaran</title>
</head>

<body>
    <h2>Tambah Tahun Ajaran</h2>
    <form action="index.php?controller=class_year&action=store" method="POST">
        <label>Tahun Ajaran:</label><br>
        <input type="text" name="academic_year" placeholder="2025/2026" maxlength="9" required><br><br>

        <label><input type="checkbox" name="is_active" checked> Aktif</label><br><br>

        <button type="submit">Simpan</button>
        <a href="index.php?controller=class_year&action=index">Batal</a>
    </form>
</body>

</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Matkul</title>
</head>
<body>
    <h2>Tambah Role Baru</h2>
    <form action="index.php?controller=courses&action=store" method="POST">
        <label>Kode Matkul</label>
        <input type="text" name="kode_mk" id="kode_mk" placeholder="Misal:PBO" required><br><br>

        <label>Matkul</label><br>
        <input type="text" name="name" placeholder="Misal: Admin" required><br><br>

        <button type="submit">Simpan</button>
        <a href="index.php?controller=courses&action=index">Batal</a>
    </form>
</body>
</html>
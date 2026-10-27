<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Role</title>
</head>
<body>
    <h2>Tambah Role Baru</h2>
    <form action="index.php?controller=role&action=store" method="POST">
        <label>Nama Role:</label><br>
        <input type="text" name="name" placeholder="Misal: Admin" required><br><br>
        
        <button type="submit">Simpan</button>
        <a href="index.php?controller=role&action=index">Batal</a>
    </form>
</body>
</html>
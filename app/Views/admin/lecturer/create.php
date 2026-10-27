<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Dosen</title>
</head>

<body>
    <h2>Tambah Dosen</h2>
    <form action="index.php?controller=lecturer&action=store" method="POST">
        <label>Kode Dosen:</label><br>
        <input type="text" name="kode" placeholder="FU" maxlength="9" required><br><br>

        <label>Nama Dosen:</label><br>
        <input type="text" name="name" placeholder="Farida Ulfa" maxlength="100" required><br><br>

        <button type="submit">Simpan</button>
        <a href="index.php?controller=lecturer&action=index">Batal</a>
    </form>
</body>

</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Role</title>
</head>
<body>
    <h2>Edit Role</h2>
    <form action="index.php?controller=role&action=update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($role['id']); ?>">
        
        <label>Nama Role:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($role['name']); ?>" required><br><br>
        
        <button type="submit">Update</button>
        <a href="index.php?controller=role&action=index">Batal</a>
    </form>
</body>
</html>
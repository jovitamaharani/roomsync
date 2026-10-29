<?php
require_once "Config/Database.php";
require_once "Config/Auth.php";
require_once "app/Models/Role.php";

class RoleController
{
    private $roleModel;

    public function __construct()
    {
        Auth::requireRole(['Admin']);
        $database = new Database();
        $db = $database->getConnection();
        $this->roleModel = new Role($db);
    }

    private function validate($name)
    {
        if ($name === '') {
            return "Nama role tidak boleh kosong.";
        }
        if (strlen($name) > 50) {
            return "Nama role maksimal 50 karakter.";
        }
        return null;
    }

    private function redirect($type = null, $message = null)
    {
        if ($type && $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
        header("Location: index.php?controller=role&action=index");
        exit;
    }

    public function index()
    {
        $roles = $this->roleModel->getAll();
        require_once "app/Views/admin/role/index.php";
    }

    public function create()
    {
        $this->index();
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');

            $error = $this->validate($name);
            if ($error) {
                $this->redirect('error', $error);
            }

            try {
                if ($this->roleModel->create($name)) {
                    $this->redirect('success', 'Data role berhasil ditambahkan.');
                } else {
                    $this->redirect('error', 'Gagal menambahkan data role.');
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Nama role sudah terdaftar.');
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function edit($id = null)
    {
        $this->index();
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $name = trim($_POST['name'] ?? '');

            if (empty($id)) {
                $this->redirect('error', 'ID role tidak valid.');
            }

            $error = $this->validate($name);
            if ($error) {
                $this->redirect('error', $error);
            }

            try {
                if ($this->roleModel->update($id, $name)) {
                    $this->redirect('success', 'Data role berhasil diperbarui.');
                } else {
                    $this->redirect('error', 'Gagal memperbarui data role.');
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23505') {
                    $this->redirect('error', 'Nama role sudah terdaftar.');
                }
                throw $e;
            }
        }
        $this->redirect();
    }

    public function delete($id = null)
    {
        $id = $id ?? ($_POST['id'] ?? ($_GET['id'] ?? null));

        if (empty($id)) {
            $this->redirect('error', 'ID role tidak valid.');
        }

        // Proteksi role Admin utama agar tidak terhapus
        $role = $this->roleModel->getById($id);
        if ($role && strtolower($role['name']) === 'admin') {
            $this->redirect('error', 'Role Admin adalah peran sistem utama dan tidak boleh dihapus.');
        }

        try {
            if ($this->roleModel->delete($id)) {
                $this->redirect('success', 'Data role berhasil dihapus.');
            } else {
                $this->redirect('error', 'Gagal menghapus data role.');
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '23503') {
                $this->redirect('error', 'Role tidak bisa dihapus karena masih digunakan oleh data pengguna.');
            }
            throw $e;
        }
    }
}

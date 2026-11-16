<?php
class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Dipakai login. Satu-satunya query yang mengambil hash password.
    public function findByEmail($email)
    {
        $query = "SELECT u.id, u.role_id, u.student_class_id, u.student_class_id AS class_id, u.name, u.email, u.photo_path, u.password, r.name as role_name
        FROM " . $this->table . " u
        JOIN roles r on r.id = u.role_id
        WHERE u.email = :email
        LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kolom password SENGAJA tidak diambil di query bawah
    private function baseSelect()
    {
        return "SELECT u.id, u.name, u.email, u.nim, u.role_id, u.student_class_id, u.created_at,
                       r.name  AS role_name,
                       sc.name AS class_name,
                       ay.name AS academic_year
                FROM users u
                JOIN roles r ON r.id = u.role_id
                LEFT JOIN student_class sc  ON sc.id = u.student_class_id
                LEFT JOIN academic_years ay ON ay.id = sc.academic_year_id";
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare($this->baseSelect() . " ORDER BY u.name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare($this->baseSelect() . " WHERE u.id = :id LIMIT 1");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFormOptions()
    {
        return [
            'roles' => $this->conn->query("SELECT id, name FROM roles ORDER BY name")
                ->fetchAll(PDO::FETCH_ASSOC),
            'classes' => $this->conn->query(
                "SELECT c.id, c.name, ay.name AS academic_year
                 FROM student_class c
                 JOIN academic_years ay ON ay.id = c.academic_year_id
                 ORDER BY ay.name DESC, c.name"
            )->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    public function create(array $d)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO users (role_id, student_class_id, name, email, nim, password)
             VALUES (:role_id, :student_class_id, :name, :email, :nim, :password)
             RETURNING id"
        );
        $stmt->execute([
            ':role_id' => $d['role_id'],
            ':student_class_id' => $d['student_class_id'],      // null jika tidak diisi
            ':name' => $d['name'],
            ':email' => $d['email'],
            ':nim' => $d['nim'],
            ':password' => password_hash($d['password'], PASSWORD_DEFAULT),
        ]);
        return $stmt->fetchColumn();
    }

    public function update($id, array $d)
    {
        $sql = "UPDATE users
                SET role_id = :role_id, student_class_id = :student_class_id, name = :name, email = :email, nim = :nim";
        $params = [
            ':id' => $id,
            ':role_id' => $d['role_id'],
            ':student_class_id' => $d['student_class_id'],
            ':name' => $d['name'],
            ':email' => $d['email'],
            ':nim' => $d['nim'],
        ];

        // Password hanya diganti jika diisi
        if ($d['password'] !== '') {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($d['password'], PASSWORD_DEFAULT);
        }

        $stmt = $this->conn->prepare($sql . " WHERE id = :id");
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // ---------- Profil milik sendiri ----------

    public function getProfile($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.name, u.email, u.no_hp, u.photo_path, u.created_at,
                    r.name  AS role_name,
                    sc.name AS class_name,
                    ay.name AS academic_year
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN student_class sc  ON sc.id = u.student_class_id
             LEFT JOIN academic_years ay ON ay.id = sc.academic_year_id
             WHERE u.id = :id
             LIMIT 1"
        );
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Hanya kolom yang boleh diubah pengguna sendiri. role_id dan student_class_id sengaja tidak ada.
    public function updateProfile($id, array $d)
    {
        $stmt = $this->conn->prepare(
            "UPDATE users SET name = :name, email = :email, no_hp = :no_hp WHERE id = :id"
        );
        $stmt->bindValue(':name', $d['name']);
        $stmt->bindValue(':email', $d['email']);
        $stmt->bindValue(':no_hp', $d['no_hp'], $d['no_hp'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getPasswordHash($id)
    {
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function updatePassword($id, $hash)
    {
        $stmt = $this->conn->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindValue(':password', $hash);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function updatePhoto($id, $path)
    {
        $stmt = $this->conn->prepare("UPDATE users SET photo_path = :photo WHERE id = :id");
        $stmt->bindValue(':photo', $path, $path === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
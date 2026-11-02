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
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.role_id, u.student_class_id AS class_id,
                    u.name, u.email, u.password, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE lower(u.email) = lower(:email)
             LIMIT 1"
        );
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kolom password SENGAJA tidak diambil di query bawah
    private function baseSelect()
    {
        return "SELECT u.id, u.name, u.email, u.role_id, u.student_class_id, u.created_at,
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
            "INSERT INTO users (role_id, student_class_id, name, email, password)
             VALUES (:role_id, :student_class_id, :name, :email, :password)
             RETURNING id"
        );
        $stmt->execute([
            ':role_id'          => $d['role_id'],
            ':student_class_id' => $d['student_class_id'],      // null jika tidak diisi
            ':name'             => $d['name'],
            ':email'            => $d['email'],
            ':password'         => password_hash($d['password'], PASSWORD_DEFAULT),
        ]);
        return $stmt->fetchColumn();
    }

    public function update($id, array $d)
    {
        $sql = "UPDATE users
                SET role_id = :role_id, student_class_id = :student_class_id,
                    name = :name, email = :email";
        $params = [
            ':id'               => $id,
            ':role_id'          => $d['role_id'],
            ':student_class_id' => $d['student_class_id'],
            ':name'             => $d['name'],
            ':email'            => $d['email'],
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
}
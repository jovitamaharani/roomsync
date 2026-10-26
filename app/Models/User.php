<?php
class User
{
    private $conn;
    private $table = "Users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function findByEmail($email)
    {
        $query = "SELECT u.id, u.role_id, u.class_id, u.name, u.email, u.password, r.name as role_name
        FROM " . $this->table . " u
        JOIN roles r on r.id = u.role_id
        WHERE u.email = :email
        LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
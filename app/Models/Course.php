<?php
class Course
{
    private $conn;
    private $table = "courses";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.kode_mk, c.name,
                    (SELECT count(*) FROM schedules s WHERE s.course_id = c.id) AS schedule_count
             FROM " . $this->table . " c
             ORDER BY c.kode_mk"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($kodeMk, $name)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (kode_mk, name) VALUES (:kode_mk, :name) RETURNING id"
        );
        $stmt->execute([':kode_mk' => $kodeMk, ':name' => $name]);
        return $stmt->fetchColumn();
    }

    public function update($id, $kodeMk, $name)
    {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET kode_mk = :kode_mk, name = :name WHERE id = :id"
        );
        $stmt->execute([':id' => $id, ':kode_mk' => $kodeMk, ':name' => $name]);
        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
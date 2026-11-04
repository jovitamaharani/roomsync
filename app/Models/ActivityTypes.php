<?php
class ActivityType
{
    private $conn;
    private $table = "activity_types";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (name) VALUES (:name) RETURNING id"
        );
        $stmt->bindValue(":name", $name);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function update($id, $name)
    {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET name = :name WHERE id = :id"
        );
        $stmt->bindValue(":id", $id);
        $stmt->bindValue(":name", $name);
        $stmt->execute();
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
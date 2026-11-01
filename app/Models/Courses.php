<?php
class Room
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
            "SELECT * FROM " . $this->table . " ORDER BY name"
        );
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

    private function params(array $d)
    {
        return [
            ':name' => $d['name'],
            ':kode_mk' => $d['kode_mk']
        ];
    }

    public function create(array $d)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (name, kode_mk) VALUES (:name, :kode_mk) RETURNING id"
        );
        $stmt->execute($this->params($d));
        return $stmt->fetchColumn();
    }

    public function update($id, array $d)
    {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET kode_mk = :kode_mk, name = :name WHERE id = :id"
        );
        $stmt->execute($this->params($d) + [':id' => $id]);
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
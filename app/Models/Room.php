<?php
class Room
{
    private $conn;
    private $table = "rooms";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->table . " ORDER BY building, floor, name"
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
            ':name'       => $d['name'],
            ':building'   => $d['building'],
            ':floor'      => (int) $d['floor'],
            ':capacity'   => (int) $d['capacity'],
            ':chairs'     => (int) $d['chairs'],
            ':tables'     => (int) $d['tables'],
            ':facilities' => $d['facilities'] !== '' ? $d['facilities'] : null,
            ':is_active'  => $d['is_active'] ? 'true' : 'false',
        ];
    }

    public function create(array $d)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . "
                (name, building, floor, capacity, chairs, tables, facilities, is_active)
             VALUES
                (:name, :building, :floor, :capacity, :chairs, :tables, :facilities, :is_active)
             RETURNING id"
        );
        $stmt->execute($this->params($d));
        return $stmt->fetchColumn();
    }

    public function update($id, array $d)
    {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . "
             SET name = :name, building = :building, floor = :floor,
                 capacity = :capacity, chairs = :chairs, tables = :tables,
                 facilities = :facilities, is_active = :is_active
             WHERE id = :id"
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
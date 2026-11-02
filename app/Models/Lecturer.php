<?php
class Lecturer
{
    private $conn;
    private $table = "lecturers";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY name DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($kode, $name)
    {
        $query = "INSERT INTO " . $this->table . "
            (kode, name)
            VALUES (:kode, :name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode", $kode);
        $stmt->bindValue(":name", $name);
        return $stmt->execute();
    }

    public function update($id, $kode, $name)
    {
        $query = "UPDATE " . $this->table . "
                SET kode = :kode,
                    name = :name
                WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":kode", $kode);
        $stmt->bindValue(":name", $name);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}

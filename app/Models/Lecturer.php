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

    public function create($kode, $name, $telepon)
    {
        $query = "INSERT INTO " . $this->table . "
            (kode, name, telepon)
            VALUES (:kode, :name, :telepon)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode", $kode);
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":telepon", $telepon);
        return $stmt->execute();
    }

    public function update($id, $kode, $name, $telepon)
    {
        $query = "UPDATE " . $this->table . "
                SET kode = :kode,
                    name = :name,
                    telepon = :telepon
                WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":kode", $kode);
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":telepon", $telepon);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollBack()
    {
        if ($this->conn->inTransaction()) {
            return $this->conn->rollBack();
        }
        return false;
    }

    public function findExistingKodes(array $kodes): array
    {
        if (empty($kodes)) {
            return [];
        }

        $uniqueKodes = array_values(array_unique($kodes));
        $placeholders = [];
        foreach ($uniqueKodes as $i => $kode) {
            $placeholders[] = ":kode_{$i}";
        }

        $sql = "SELECT kode FROM lecturers WHERE kode IN (" . implode(', ', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);

        foreach ($uniqueKodes as $i => $kode) {
            $stmt->bindValue(":kode_{$i}", $kode, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}

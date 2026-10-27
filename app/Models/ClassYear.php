<?php
class ClassYear
{
    private $conn;
    private $table = "class_years";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY academic_year DESC";
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

    public function create($academicYear, $isActive)
    {
        $query = "INSERT INTO " . $this->table . "
            (academic_year, is_active)
            VALUES (:academic_year, :is_active)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":academic_year", $academicYear);
        $stmt->bindValue(":is_active", $isActive ? 'true' : 'false');
        return $stmt->execute();
    }

    public function update($id, $academicYear, $isActive)
    {
        $query = "UPDATE " . $this->table . "
                SET academic_year = :academic_year,
                    is_active = :is_active
                WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":academic_year", $academicYear);
        $stmt->bindValue(":is_active", $isActive ? 'true' : 'false');
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

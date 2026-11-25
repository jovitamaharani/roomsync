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

    public function findExistingKodes(array $kodes)
    {
        if (empty($kodes)) {
            return [];
        }

        $normalized = array_values(array_unique(array_map(
            fn($k) => strtoupper(trim((string) $k)),
            $kodes
        )));

        $placeholders = [];
        foreach ($normalized as $i => $kode) {
            $placeholders[] = ":kode_{$i}";
        }

        $sql = "SELECT kode_mk FROM " . $this->table . " WHERE UPPER(kode_mk) IN (" . implode(', ', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);

        foreach ($normalized as $i => $kode) {
            $stmt->bindValue(":kode_{$i}", $kode, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
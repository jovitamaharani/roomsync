<?php
class StudentClass
{
    private $conn;
    private $table = "student_class";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.name, c.academic_year_id,
                    ay.name AS academic_year, ay.is_active AS year_active,
                    (SELECT count(*) FROM users u WHERE u.student_class_id = c.id) AS user_count
             FROM " . $this->table . " c
             JOIN academic_years ay ON ay.id = c.academic_year_id
             ORDER BY ay.name DESC, c.name"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAcademicYears()
    {
        $stmt = $this->conn->prepare("SELECT id, name, is_active FROM academic_years ORDER BY name DESC");
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

    private function hasSchedules($id)
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM schedules WHERE student_class_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function create($academicYearId, $name)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (academic_year_id, name)
             VALUES (:ay, :name) RETURNING id"
        );
        $stmt->execute([':ay' => $academicYearId, ':name' => $name]);
        return $stmt->fetchColumn();
    }

    public function update($id, $academicYearId, $name)
    {
        $current = $this->getById($id);
        if (!$current) {
            return false;
        }

        if (
            strtolower($current['academic_year_id']) !== strtolower($academicYearId)
            && $this->hasSchedules($id)
        ) {
            throw new DomainException('Tahun ajaran kelas tidak bisa diubah karena kelas ini sudah punya jadwal.');
        }

        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . "
             SET academic_year_id = :ay, name = :name
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id, ':ay' => $academicYearId, ':name' => $name]);
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
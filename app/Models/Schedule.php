<?php
class Schedule
{
    private $conn;
    private $table = "schedules";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function fetchAll($sql)
    {
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Semua data untuk dropdown di form
    public function getFormOptions()
    {
        return [
            'rooms' => $this->fetchAll(
                "SELECT id, name, building, floor, capacity, is_active
                 FROM rooms ORDER BY building, name"
            ),
            'classes' => $this->fetchAll(
                "SELECT c.id, c.name, ay.name AS academic_year
                 FROM student_class c
                 JOIN academic_years ay ON ay.id = c.academic_year_id
                 WHERE ay.is_active
                 ORDER BY c.name"
            ),
            'lecturers' => $this->fetchAll("SELECT id, kode, name FROM lecturers ORDER BY name"),
            'courses' => $this->fetchAll("SELECT id, kode_mk, name FROM courses ORDER BY name"),
        ];
    }

    public function getAll()
    {
        return $this->fetchAll(
            "SELECT s.id, s.room_id, s.student_class_id, s.lecturer_id, s.course_id,
                    s.day_of_week, s.start_time, s.end_time,
                    s.semester::text AS semester,
                    r.name AS room_name, r.building,
                    cl.name AS class_name,
                    l.name AS lecturer_name,
                    c.kode_mk, c.name AS course_name
             FROM schedules s
             JOIN rooms r           ON r.id  = s.room_id
             JOIN student_class cl  ON cl.id = s.student_class_id
             JOIN academic_years ay ON ay.id = cl.academic_year_id
             JOIN lecturers l       ON l.id  = s.lecturer_id
             JOIN courses c         ON c.id  = s.course_id
             WHERE ay.is_active
             ORDER BY s.day_of_week, s.start_time, r.name"
        );
    }

    private function findActiveClass($classId)
    {
        $stmt = $this->conn->prepare(
            "SELECT c.id, c.academic_year_id
             FROM student_class c
             JOIN academic_years ay ON ay.id = c.academic_year_id
             WHERE c.id = :id AND ay.is_active"
        );
        $stmt->execute([':id' => $classId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isActiveRoom($roomId)
    {
        $stmt = $this->conn->prepare("SELECT 1 FROM rooms WHERE id = :id AND is_active");
        $stmt->execute([':id' => $roomId]);
        return (bool) $stmt->fetchColumn();
    }

    // Jadwal yang bentrok: ruangan, dosen, atau kelas yang sama
    // pada hari + semester + tahun ajaran yang sama dan jamnya beririsan.
    public function findConflict($academicYearId, array $d, $excludeId = null)
    {
        $sql = "SELECT s.room_id, s.lecturer_id, s.student_class_id,
                       s.start_time, s.end_time, c.name AS course_name
                FROM schedules s
                JOIN student_class cl ON cl.id = s.student_class_id
                JOIN courses c        ON c.id  = s.course_id
                WHERE cl.academic_year_id = :ay
                  AND s.semester = :semester
                  AND s.day_of_week = :dow
                  AND s.start_time < :end_time
                  AND :start_time < s.end_time
                  AND (s.room_id = :room OR s.lecturer_id = :lecturer OR s.student_class_id = :class)";

        $params = [
            ':ay' => $academicYearId,
            ':semester' => $d['semester'],
            ':dow' => $d['day_of_week'],
            ':end_time' => $d['end_time'],
            ':start_time' => $d['start_time'],
            ':room' => $d['room_id'],
            ':lecturer' => $d['lecturer_id'],
            ':class' => $d['student_class_id'],
        ];

        if ($excludeId) {              // dipakai saat update
            $sql .= " AND s.id <> :exclude";
            $params[':exclude'] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql . " LIMIT 1");
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function conflictMessage(array $c, array $d)
    {
        if (strtolower($c['room_id']) === strtolower($d['room_id'])) {
            $what = 'Ruangan';
        } elseif (strtolower($c['lecturer_id']) === strtolower($d['lecturer_id'])) {
            $what = 'Dosen';
        } else {
            $what = 'Kelas';
        }
        return sprintf(
            "%s sudah terpakai pada jadwal %s (%s-%s).",
            $what,
            $c['course_name'],
            substr($c['start_time'], 0, 5),
            substr($c['end_time'], 0, 5)
        );
    }

    public function createIfFree(array $d)
    {
        $this->conn->beginTransaction();
        try {
            // Kunci ringan supaya dua admin yang menyimpan bersamaan tidak sama-sama lolos
            $this->conn->query("SELECT pg_advisory_xact_lock(hashtext('schedules'))");

            $class = $this->findActiveClass($d['student_class_id']);
            if (!$class) {
                throw new DomainException('Kelas tidak ditemukan atau bukan dari tahun ajaran aktif.');
            }
            if (!$this->isActiveRoom($d['room_id'])) {
                throw new DomainException('Ruangan tidak ditemukan atau tidak aktif.');
            }

            $conflict = $this->findConflict($class['academic_year_id'], $d);
            if ($conflict) {
                throw new DomainException($this->conflictMessage($conflict, $d));
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO " . $this->table . "
                    (room_id, student_class_id, lecturer_id, course_id,
                     day_of_week, start_time, end_time, semester)
                 VALUES
                    (:room_id, :student_class_id, :lecturer_id, :course_id,
                     :day_of_week, :start_time, :end_time, :semester)
                 RETURNING id"
            );
            $stmt->execute([
                ':room_id' => $d['room_id'],
                ':student_class_id' => $d['student_class_id'],
                ':lecturer_id' => $d['lecturer_id'],
                ':course_id' => $d['course_id'],
                ':day_of_week' => $d['day_of_week'],
                ':start_time' => $d['start_time'],
                ':end_time' => $d['end_time'],
                ':semester' => $d['semester'],
            ]);
            $id = $stmt->fetchColumn();

            $this->conn->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function updateIfFree($id, array $d)
    {
        $this->conn->beginTransaction();
        try {
            $this->conn->query("SELECT pg_advisory_xact_lock(hashtext('schedules'))");

            $stmt = $this->conn->prepare("SELECT room_id FROM " . $this->table . " WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$current) {
                throw new DomainException('Jadwal tidak ditemukan.');
            }

            $class = $this->findActiveClass($d['student_class_id']);
            if (!$class) {
                throw new DomainException('Kelas tidak ditemukan atau bukan dari tahun ajaran aktif.');
            }

            // Ruangan nonaktif boleh dipertahankan, tapi tidak boleh dipilih sebagai ruangan baru
            $roomChanged = strtolower($current['room_id']) !== strtolower($d['room_id']);
            if ($roomChanged && !$this->isActiveRoom($d['room_id'])) {
                throw new DomainException('Ruangan tidak ditemukan atau tidak aktif.');
            }

            $conflict = $this->findConflict($class['academic_year_id'], $d, $id);
            if ($conflict) {
                throw new DomainException($this->conflictMessage($conflict, $d));
            }

            $stmt = $this->conn->prepare(
                "UPDATE " . $this->table . "
                 SET room_id = :room_id, student_class_id = :student_class_id,
                     lecturer_id = :lecturer_id, course_id = :course_id,
                     day_of_week = :day_of_week, start_time = :start_time,
                     end_time = :end_time, semester = :semester
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $id,
                ':room_id' => $d['room_id'],
                ':student_class_id' => $d['student_class_id'],
                ':lecturer_id' => $d['lecturer_id'],
                ':course_id' => $d['course_id'],
                ':day_of_week' => $d['day_of_week'],
                ':start_time' => $d['start_time'],
                ':end_time' => $d['end_time'],
                ':semester' => $d['semester'],
            ]);

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
<?php
class Schedule
{
    private $conn;
    private $table = "schedules";

    public const SEMESTERS = ['Ganjil', 'Genap'];

    private $refTables = ['courses', 'lecturers', 'student_class', 'rooms'];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function fetchAll($query)
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindData($stmt, array $d)
    {
        $stmt->bindValue(":semester", $d['semester']);
        $stmt->bindValue(":day_of_week", (int) $d['day_of_week'], PDO::PARAM_INT);
        $stmt->bindValue(":start_time", $d['start_time']);
        $stmt->bindValue(":end_time", $d['end_time']);
        $stmt->bindValue(":course_id", $d['course_id']);
        $stmt->bindValue(":lecturer_id", $d['lecturer_id']);
        $stmt->bindValue(":student_class_id", $d['student_class_id']);
        $stmt->bindValue(":room_id", $d['room_id']);
    }

    private function selectJoin()
    {
        return "SELECT s.id, s.semester,
                    s.day_of_week, s.start_time, s.end_time,
                    s.course_id, co.name AS course,
                    s.lecturer_id, l.name AS lecturer,
                    s.student_class_id, sc.name AS classroom,
                    ay.name AS academic_year,
                    s.room_id, rm.name AS room
                FROM " . $this->table . " s
                JOIN courses co ON co.id = s.course_id
                JOIN lecturers l ON l.id = s.lecturer_id
                JOIN student_class sc ON sc.id = s.student_class_id
                JOIN academic_years ay ON ay.id = sc.academic_year_id
                JOIN rooms rm ON rm.id = s.room_id";
    }

    // Jadwal hari ini untuk tampilan user, lengkap dengan status waktu.
    public function getToday()
    {
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $date = $now->format('Y-m-d');
        $time = $now->format('H:i:s');

        $stmt = $this->conn->prepare(
            "SELECT s.id, s.start_time, s.end_time,
                co.kode_mk, co.name AS course,
                l.name  AS lecturer,
                sc.name AS classroom,
                rm.name AS room, rm.building
         FROM schedules s
         JOIN courses co        ON co.id = s.course_id
         JOIN lecturers l       ON l.id  = s.lecturer_id
         JOIN student_class sc  ON sc.id = s.student_class_id
         JOIN academic_years ay ON ay.id = sc.academic_year_id
         JOIN rooms rm          ON rm.id = s.room_id
         WHERE ay.is_active
           AND s.day_of_week = :dow
           AND NOT EXISTS (
               SELECT 1 FROM schedule_exceptions e
               WHERE e.schedule_id = s.id
                 AND e.date = :date
                 AND e.type::text IN ('cancelled', 'moved')
           )
         ORDER BY s.start_time, rm.name"
        );
        $stmt->bindValue(':dow', (int) $now->format('N'), PDO::PARAM_INT);
        $stmt->bindValue(':date', $date);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['status'] = $time < $r['start_time'] ? 'Belum'
                : ($time < $r['end_time'] ? 'Sedang' : 'Selesai');
        }
        unset($r);
        return $rows;
    }

    public function getAll()
    {
        return $this->fetchAll(
            $this->selectJoin() . " ORDER BY s.day_of_week ASC, s.start_time ASC, rm.name ASC"
        );
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare($this->selectJoin() . " WHERE s.id = :id LIMIT 1");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOptions()
    {
        return [
            'semesters' => self::SEMESTERS,
            'courses' => $this->fetchAll("SELECT id, name FROM courses ORDER BY name"),
            'lecturers' => $this->fetchAll("SELECT id, name FROM lecturers ORDER BY name"),
            'classes' => $this->fetchAll(
                "SELECT sc.id, sc.name, ay.name AS academic_year
                FROM student_class sc
                JOIN academic_years ay ON ay.id = sc.academic_year_id
                ORDER BY ay.name DESC, sc.name"
            ),
            'rooms' => $this->fetchAll("SELECT id, name FROM rooms WHERE is_active = TRUE ORDER BY name"),
        ];
    }

    public function refExists($table, $id)
    {
        if (!in_array($table, $this->refTables, true)) {
            return false;
        }
        $query = "SELECT 1 FROM " . $table . " WHERE id = :id";
        if ($table === 'rooms') {
            $query .= " AND is_active = TRUE";
        }
        $stmt = $this->conn->prepare($query . " LIMIT 1");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function findConflicts(array $d, $excludeId = null)
    {
        $query = "SELECT (s.room_id = :room_id) AS room_conflict,
                        (s.lecturer_id = :lecturer_id) AS lecturer_conflict,
                        (s.student_class_id = :student_class_id) AS class_conflict
                FROM " . $this->table . " s
                JOIN student_class sc ON sc.id = s.student_class_id
                WHERE s.semester = :semester
                    AND s.day_of_week = :day_of_week
                    AND s.start_time < :end_time
                    AND s.end_time > :start_time
                    AND sc.academic_year_id = (
                        SELECT academic_year_id FROM student_class WHERE id = :year_class_id
                    )
                    AND (s.room_id = :room_id2
                        OR s.lecturer_id = :lecturer_id2
                        OR s.student_class_id = :student_class_id2)";
        if ($excludeId) {
            $query .= " AND s.id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":room_id", $d['room_id']);
        $stmt->bindValue(":room_id2", $d['room_id']);
        $stmt->bindValue(":lecturer_id", $d['lecturer_id']);
        $stmt->bindValue(":lecturer_id2", $d['lecturer_id']);
        $stmt->bindValue(":student_class_id", $d['student_class_id']);
        $stmt->bindValue(":student_class_id2", $d['student_class_id']);
        $stmt->bindValue(":year_class_id", $d['student_class_id']);
        $stmt->bindValue(":semester", $d['semester']);
        $stmt->bindValue(":day_of_week", (int) $d['day_of_week'], PDO::PARAM_INT);
        $stmt->bindValue(":start_time", $d['start_time']);
        $stmt->bindValue(":end_time", $d['end_time']);
        if ($excludeId) {
            $stmt->bindValue(":exclude_id", $excludeId);
        }
        $stmt->execute();

        $isTrue = function ($v) {
            return in_array($v, [true, 't'], true);
        };

        $messages = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($isTrue($row['room_conflict'])) {
                $messages['room'] = 'Ruang sudah dipakai pada hari dan jam tersebut.';
            }
            if ($isTrue($row['lecturer_conflict'])) {
                $messages['lecturer'] = 'Dosen sudah mengajar pada hari dan jam tersebut.';
            }
            if ($isTrue($row['class_conflict'])) {
                $messages['class'] = 'Kelas sudah punya jadwal pada hari dan jam tersebut.';
            }
        }
        return array_values($messages);
    }

    public function create(array $d)
    {
        $query = "INSERT INTO " . $this->table . "
            (semester, day_of_week, start_time, end_time, course_id, lecturer_id, student_class_id, room_id)
            VALUES
            (:semester, :day_of_week, :start_time, :end_time, :course_id, :lecturer_id, :student_class_id, :room_id)";
        $stmt = $this->conn->prepare($query);
        $this->bindData($stmt, $d);
        return $stmt->execute();
    }

    public function update($id, array $d)
    {
        $query = "UPDATE " . $this->table . "
            SET semester = :semester,
                day_of_week = :day_of_week,
                start_time = :start_time,
                end_time = :end_time,
                course_id = :course_id,
                lecturer_id = :lecturer_id,
                student_class_id = :student_class_id,
                room_id = :room_id
            WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->bindData($stmt, $d);
        $stmt->bindValue(":id", $id);
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
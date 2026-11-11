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

    public function getAll()
    {
        return $this->fetchAll(
            "SELECT * FROM vw_schedules ORDER BY day_of_week ASC, start_time ASC, room ASC"
        );
    }

    public function getToday($semester = null)
    {
        $query = "SELECT * FROM vw_today_schedules";
        if ($semester) {
            $query .= " WHERE semester = :semester";
        }
        $query .= " ORDER BY start_time ASC, room ASC";

        $stmt = $this->conn->prepare($query);
        if ($semester) {
            $stmt->bindValue(":semester", $semester);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM vw_schedules WHERE id = :id LIMIT 1");
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

    public function getFilterOptions()
    {
        $rooms = array_column(
            $this->fetchAll("SELECT name FROM rooms WHERE is_active = TRUE"),
            'name'
        );
        $classes = array_column(
            $this->fetchAll(
                "SELECT DISTINCT sc.name
             FROM student_class sc
             JOIN academic_years ay ON ay.id = sc.academic_year_id
             WHERE ay.is_active = TRUE"
            ),
            'name'
        );
        natsort($rooms);    // "RT 2" sebelum "RT 10"
        natsort($classes);

        return ['rooms' => array_values($rooms), 'classes' => array_values($classes)];
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
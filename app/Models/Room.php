<?php
class Room
{
    private $conn;
    private $table = "rooms";
    private $viewStatus = "vw_room_status";
    private $todaySchedules;

 public function __construct($db)
{
    $this->conn = $db;

    $now   = "(NOW() AT TIME ZONE 'Asia/Jakarta')";
    $today = "{$now}::date";
    $time  = "{$now}::time";

    // Jadwal kuliah hari ini yang benar-benar berlangsung, diambil dari vw_today_schedules
    $this->todaySchedules = "
        SELECT t.room_id, t.course, t.kode_mk, t.lecturer,
               t.classroom AS class_name, t.start_time, t.end_time
        FROM vw_today_schedules t
        WHERE t.academic_year_active = TRUE
          AND t.exception_type IS NULL
          AND t.day_of_week = EXTRACT(ISODOW FROM {$now})::int
          AND t.semester::text = CASE
                WHEN EXTRACT(MONTH FROM {$now}) BETWEEN 2 AND 7 THEN 'Genap'
                ELSE 'Ganjil' END
    ";

    $this->viewStatus = "(
    SELECT r.*,
           cur.course     AS current_course,
           cur.lecturer   AS current_lecturer,
           cur.class_name AS current_class,
           cur.start_time AS current_start,
           cur.end_time   AS current_end,
           bk.purpose     AS current_purpose,
           prv.free_from  AS free_from,
           nxt.free_until AS free_until,
           CASE WHEN cur.room_id IS NOT NULL OR bk.room_id IS NOT NULL
                THEN 'terpakai' ELSE 'kosong' END AS status
    FROM rooms r
    LEFT JOIN LATERAL (
        SELECT ts.*
        FROM ({$this->todaySchedules}) ts
        WHERE ts.room_id = r.id
          AND {$time} >= ts.start_time AND {$time} < ts.end_time
        ORDER BY ts.start_time
        LIMIT 1
    ) cur ON TRUE
    LEFT JOIN LATERAL (
        SELECT b.room_id, b.purpose
        FROM bookings b
        WHERE b.room_id = r.id
          AND b.date = {$today}
          AND {$time} >= b.start_time AND {$time} < b.end_time
          AND b.status::text = 'approved'
        ORDER BY b.start_time
        LIMIT 1
    ) bk ON TRUE
    LEFT JOIN LATERAL (
        SELECT MAX(x.end_time) AS free_from
        FROM (
            SELECT ts.end_time FROM ({$this->todaySchedules}) ts
            WHERE ts.room_id = r.id AND ts.end_time <= {$time}
            UNION ALL
            SELECT b.end_time FROM bookings b
            WHERE b.room_id = r.id AND b.date = {$today}
              AND b.status::text = 'approved' AND b.end_time <= {$time}
        ) x
    ) prv ON TRUE
    LEFT JOIN LATERAL (
        SELECT MIN(y.start_time) AS free_until
        FROM (
            SELECT ts.start_time FROM ({$this->todaySchedules}) ts
            WHERE ts.room_id = r.id AND ts.start_time > {$time}
            UNION ALL
            SELECT b.start_time FROM bookings b
            WHERE b.room_id = r.id AND b.date = {$today}
              AND b.status::text = 'approved' AND b.start_time > {$time}
        ) y
    ) nxt ON TRUE
)";
}

    public function getAll()
    {
        $query = "
            SELECT * 
            FROM {$this->viewStatus} AS rs
            WHERE is_active = true 
            ORDER BY building, floor, name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($keyword)
    {
        $query = "
            SELECT * 
            FROM {$this->viewStatus} AS rs
            WHERE is_active = true 
              AND (name ILIKE :kw OR building ILIKE :kw OR facilities ILIKE :kw)
            ORDER BY building, floor, name
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':kw', '%' . trim($keyword) . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats()
    {
        $query = "
            SELECT 
                COUNT(*) AS total,
                COUNT(*) FILTER (WHERE status = 'kosong') AS kosong,
                COUNT(*) FILTER (WHERE status = 'terpakai') AS terpakai,
                COUNT(DISTINCT building) AS gedung
            FROM {$this->viewStatus}
            WHERE is_active = true
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'kosong' => (int) ($row['kosong'] ?? 0),
            'terpakai' => (int) ($row['terpakai'] ?? 0),
            'gedung' => (int) ($row['gedung'] ?? 1)
        ];
    }

    public function getPreview($limit = 3)
    {
        $query = "
            SELECT 
                r.id,
                r.name,
                UPPER(r.status) AS status,
                r.capacity,
                COALESCE(ts.course, '') AS subject,
                CASE 
                    WHEN ts.start_time IS NOT NULL 
                    THEN TO_CHAR(ts.start_time, 'HH24:MI') || ' - ' || TO_CHAR(ts.end_time, 'HH24:MI')
                    ELSE ''
                END AS time
            FROM {$this->viewStatus} r
            LEFT JOIN ({$this->todaySchedules}) ts 
                   ON ts.room_id = r.id 
                  AND (NOW() AT TIME ZONE 'Asia/Jakarta')::time BETWEEN ts.start_time AND ts.end_time
            WHERE r.is_active = true
            ORDER BY 
                CASE WHEN r.status = 'terpakai' THEN 1 ELSE 2 END,
                r.floor ASC, 
                r.name ASC
            LIMIT :limit
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->viewStatus} WHERE id = :id LIMIT 1");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function params(array $d)
    {
        return [
            ':name' => $d['name'],
            ':building' => $d['building'],
            ':floor' => (int) $d['floor'],
            ':capacity' => (int) $d['capacity'],
            ':chairs' => (int) $d['chairs'],
            ':tables' => (int) $d['tables'],
            ':facilities' => $d['facilities'] !== '' ? $d['facilities'] : null,
            ':is_active' => $d['is_active'] ? 'true' : 'false',
            ':image_path' => $d['image_path']?? null,
        ];
    }

    public function create(array $d)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . "
                (name, building, floor, capacity, chairs, tables, facilities, is_active, image_path)
             VALUES
                (:name, :building, :floor, :capacity, :chairs, :tables, :facilities, :is_active, :image_path)
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
                 facilities = :facilities, is_active = :is_active, image_path = :image_path
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


public function getWeeklySchedules()
{
    $stmt = $this->conn->prepare(
        "SELECT s.room_id,
                s.day_of_week,
                to_char(s.start_time, 'HH24:MI') AS start_time,
                to_char(s.end_time,   'HH24:MI') AS end_time,
                co.name    AS course,
                co.kode_mk AS code,
                l.name     AS lecturer,
                sc.name    AS classroom
         FROM schedules s
         JOIN courses co       ON co.id = s.course_id
         JOIN lecturers l      ON l.id  = s.lecturer_id
         JOIN student_class sc ON sc.id = s.student_class_id
         JOIN academic_years ay ON ay.id = sc.academic_year_id
         WHERE ay.is_active = TRUE
        AND s.semester::text = CASE
        WHEN EXTRACT(MONTH FROM (NOW() AT TIME ZONE 'Asia/Jakarta')) BETWEEN 1 AND 6
        THEN 'Genap' ELSE 'Ganjil' END
         ORDER BY s.day_of_week ASC, s.start_time ASC"
    );
    $stmt->execute();

    $byRoom = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $byRoom[$row['room_id']][] = [
            'day'       => (int) $row['day_of_week'],
            'start'     => $row['start_time'],
            'end'       => $row['end_time'],
            'course'    => $row['course'],
            'code'      => $row['code'],
            'lecturer'  => $row['lecturer'],
            'classroom' => $row['classroom'],
        ];
    }
    return $byRoom;
}
}
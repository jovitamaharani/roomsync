<?php

class OccupancyLog
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getHistory(string $from, string $to, ?string $source = null, int $limit = 5000): array
    {
        $sql = "
        WITH all_logs AS (  
            -- 1. Riwayat dari vw_occupancy_logs (kolom room_id wajib di-select)
            SELECT 
                o.id::text AS id,
                o.room_id::text AS room_id,
                o.room,
                o.building,
                o.user_name,
                o.user_role,
                o.source,
                o.course,
                o.classroom,
                o.lecturer,
                o.purpose,
                o.start_time,
                o.end_time,
                o.usage_date,
                o.is_ongoing,
                CASE 
                    WHEN o.is_ongoing THEN 'Berlangsung'
                    ELSE 'Selesai'
                END AS status_label
            FROM vw_occupancy_logs o

            UNION ALL

            -- 2. Riwayat dari bookings yang belum check-in
            SELECT 
                b.id::text AS id,
                b.room_id::text AS room_id,
                rm.name AS room,
                rm.building,
                u.name AS user_name,
                r.name AS user_role,
                'booking' AS source,
                NULL AS course,
                sc.name AS classroom,
                NULL AS lecturer,
                b.purpose,
                (b.date + b.start_time)::timestamp AS start_time,
                (b.date + b.end_time)::timestamp AS end_time,
                b.date AS usage_date,
                FALSE AS is_ongoing,
                CASE 
                    WHEN b.status = 'confirmed' THEN 'Terkonfirmasi'
                    WHEN b.status = 'cancelled' THEN 'Dibatalkan'
                    WHEN b.status = 'no_show'   THEN 'Tidak Hadir'
                    ELSE 'Terkonfirmasi'
                END AS status_label
            FROM bookings b
            JOIN rooms rm ON rm.id = b.room_id
            JOIN users u ON u.id = b.user_id
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN student_class sc ON sc.id = u.student_class_id
            WHERE NOT EXISTS (
                SELECT 1 FROM occupancy_logs ol WHERE ol.booking_id = b.id
            )
        )
        SELECT *
        FROM all_logs
        WHERE usage_date BETWEEN :from_date::date AND :to_date::date
    ";

        if ($source !== null) {
            $sql .= " AND LOWER(TRIM(source)) = :source";
        }

        $sql .= " ORDER BY start_time DESC LIMIT :row_limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':from_date', $from);
        $stmt->bindValue(':to_date', $to);
        $stmt->bindValue(':row_limit', $limit, PDO::PARAM_INT);
        if ($source !== null) {
            $stmt->bindValue(':source', strtolower(trim($source)));
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomNames(): array
    {
        $stmt = $this->conn->query("SELECT name FROM rooms");
        $names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
        natcasesort($names);

        return array_values($names);
    }
}
<?php

class OccupancyLog
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getHistory(string $from, string $to, int $limit = 5000): array
    {
        $stmt = $this->conn->prepare("
        SELECT id, room, building, user_name, user_role, source,
               course, classroom, lecturer, purpose,
               start_time, end_time, usage_date, is_ongoing, duration_minutes
        FROM vw_occupancy_logs
        WHERE usage_date BETWEEN :from_date::date AND :to_date::date
          AND LOWER(TRIM(source)) = 'booking'
        ORDER BY start_time DESC
        LIMIT :row_limit
    ");
        $stmt->bindValue(':from_date', $from);
        $stmt->bindValue(':to_date', $to);
        $stmt->bindValue(':row_limit', $limit, PDO::PARAM_INT);
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
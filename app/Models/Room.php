<?php
class Room
{
    private $conn;
    private $table = "rooms";
    private $viewStatus = "vw_room_status";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "
            SELECT * 
            FROM {$this->viewStatus} 
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
            FROM {$this->viewStatus}
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
            LEFT JOIN vw_today_schedules ts 
                   ON ts.room_id = r.id 
                  AND CURRENT_TIME BETWEEN ts.start_time AND ts.end_time
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
}
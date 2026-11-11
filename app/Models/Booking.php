<?php

class Booking
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $this->conn->exec("CALL sp_sync_expired_bookings()");

        $stmt = $this->conn->query("
            SELECT 
                vb.*,
                vb.room AS room_name,
                vb.booked_by AS user_name
            FROM vw_bookings vb
            ORDER BY vb.date DESC, vb.start_time DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(string $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT 
                vb.*,
                vb.room AS room_name,
                vb.booked_by AS user_name
            FROM vw_bookings vb 
            WHERE vb.id = :id::uuid 
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getActiveRooms(): array
    {
        $stmt = $this->conn->query("
            SELECT id, name, building, floor, capacity 
            FROM vw_room_status 
            WHERE is_active = TRUE
        ");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        usort($rooms, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
        return $rooms;
    }

    public function getAvailableRooms(string $date, string $startTime, string $endTime, ?string $excludeBookingId = null): array
    {
        $dayOfWeek = (int) date('N', strtotime($date));

        $sql = "
        SELECT r.id, r.name, r.building, r.floor, r.capacity
        FROM rooms r
        WHERE r.is_active = TRUE
          -- 1. Jam Kosong: Tidak ada jadwal kuliah rutin, KECUALI jika jadwal tersebut
          --    sudah dinyatakan kosong/dibatalkan pada tanggal ini lewat schedule_exceptions
          AND r.id NOT IN (
              SELECT s.room_id 
              FROM schedules s
              WHERE s.day_of_week = :day_of_week
                AND (s.start_time < :end_time::time AND s.end_time > :start_time::time)
                AND NOT EXISTS (
                    SELECT 1 
                    FROM schedule_exceptions se
                    WHERE se.schedule_id = s.id
                      AND se.date = :date::date
                      AND se.type IN ('cancelled', 'rescheduled')
                )
          )
          -- 2. Tidak sedang dibooking orang lain yang terkonfirmasi
          AND r.id NOT IN (
              SELECT b.room_id 
              FROM bookings b
              WHERE b.date = :date::date
                AND b.status = 'confirmed'
                AND (b.start_time < :end_time::time AND b.end_time > :start_time::time)
                " . ($excludeBookingId ? "AND b.id != :exclude_id::uuid" : "") . "
          )
    ";

        $stmt = $this->conn->prepare($sql);
        $params = [
            ':day_of_week' => $dayOfWeek,
            ':date' => $date,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
        ];

        if ($excludeBookingId) {
            $params[':exclude_id'] = $excludeBookingId;
        }

        $stmt->execute($params);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        usort($rooms, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));
        return $rooms;
    }
    public function getRoomSlots(string $roomId, string $date): array
    {
        $stmt = $this->conn->prepare("
            SELECT slot_no, start_time, end_time, is_free, is_past
            FROM fn_room_slots(:room_id::uuid, :date::date)
            ORDER BY slot_no
        ");
        $stmt->execute([
            'room_id' => $roomId,
            'date' => $date
        ]);

        $isTrue = fn($v) => in_array($v, [true, 't', 'true', 1, '1'], true);

        return array_map(fn($row) => [
            'slot_no' => (int) $row['slot_no'],
            'start' => substr((string) $row['start_time'], 0, 5),
            'end' => substr((string) $row['end_time'], 0, 5),
            'is_free' => $isTrue($row['is_free']),
            'is_past' => $isTrue($row['is_past'])
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function isSlotRange(string $startTime, string $endTime): bool
    {
        $stmt = $this->conn->prepare("
            SELECT
                (SELECT slot_no FROM time_slots WHERE start_time = :start_time::time) AS first_slot,
                (SELECT slot_no FROM time_slots WHERE end_time = :end_time::time) AS last_slot
        ");
        $stmt->execute([
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            && $row['first_slot'] !== null
            && $row['last_slot'] !== null
            && (int) $row['last_slot'] >= (int) $row['first_slot'];
    }

    public function isRoomAvailable(string $roomId, string $date, string $startTime, string $endTime, ?string $excludeBookingId = null): bool
    {
        $stmt = $this->conn->prepare("
            SELECT fn_is_room_available(
                :room_id::uuid,
                :date::date,
                :start_time::time,
                :end_time::time,
                :exclude_id::uuid
            )
        ");

        $stmt->execute([
            'room_id' => $roomId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'exclude_id' => $excludeBookingId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(string $roomId, string $userId, string $date, string $startTime, string $endTime, string $purpose): string
    {
        $stmt = $this->conn->prepare("
            CALL sp_create_booking(
                :room_id::uuid,
                :user_id::uuid,
                :date::date,
                :start_time::time,
                :end_time::time,
                :purpose,
                NULL
            )
        ");

        $stmt->execute([
            'room_id' => $roomId,
            'user_id' => $userId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => $purpose
        ]);

        return (string) $stmt->fetchColumn();
    }

    public function updateByUser(string $bookingId, string $userId, string $roomId, string $date, string $startTime, string $endTime, string $purpose): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE bookings
            SET room_id = :room_id::uuid,
                date = :date::date,
                start_time = :start_time::time,
                end_time = :end_time::time,
                purpose = :purpose
            WHERE id = :id::uuid
              AND user_id = :user_id::uuid
              AND status = 'confirmed'
        ");

        $stmt->execute([
            'id' => $bookingId,
            'user_id' => $userId,
            'room_id' => $roomId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => $purpose
        ]);

        return $stmt->rowCount() > 0;
    }

    public function cancel(string $bookingId, string $userId, bool $isAdmin = false): void
    {
        $stmt = $this->conn->prepare("
            CALL sp_cancel_booking(:booking_id::uuid, :user_id::uuid, :is_admin::boolean)
        ");

        $stmt->bindValue('booking_id', $bookingId);
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('is_admin', $isAdmin, PDO::PARAM_BOOL);
        $stmt->execute();
    }
}
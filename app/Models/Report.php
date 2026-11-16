<?php
/**
 * Laporan Activity Control untuk kuliah sesuai jadwal.
 *
 *  - Pengisi : mahasiswa (termasuk Ketua Kelas) dari kelas jadwal tersebut (dicek di server).
 *  - Admin   : hanya melihat (getAdminList). Tidak ada validasi.
 *
 * Semua waktu dihitung di PHP dengan zona Asia/Jakarta (bukan dari klien dan
 * bukan dari CURRENT_TIMESTAMP database yang berjalan di UTC).
 */
class Report
{
    private $conn;

    // Role yang boleh mengisi jurnal (huruf kecil; dibandingkan tanpa membedakan huruf besar/kecil).
    // Syarat tambahan: user punya kelas (student_class_id) dan hanya boleh melaporkan sesi kelasnya sendiri.
    public const REPORTER_ROLES = ['mahasiswa', 'ketua kelas'];

    // Sesi yang boleh dilaporkan: hari ini dan N-1 hari ke belakang
    public const WINDOW_DAYS = 7;

    // Nilai enum report_mode => label
    public const MODES = [
        'offline' => 'Offline / Luring di Ruang',
        'hybrid' => 'Hybrid (Zoom Bersama di Kelas)',
        'online' => 'Daring / Online',
    ];

    // Nilai kolom reports.lecturer_attendance => label
    public const LECTURER_STATUS = [
        'hadir' => 'Hadir Tepat Waktu',
        'hadir_asisten' => 'Hadir dengan Asisten',
        'izin_penugasan' => 'Penugasan Mandiri (Dosen Izin)',
    ];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ------------------------------------------------------------------
    // Pembantu umum
    // ------------------------------------------------------------------

    private function now(): DateTime
    {
        return new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    }

    // Boleh mengisi jurnal: mahasiswa (termasuk Ketua Kelas) yang terdaftar pada sebuah kelas
    public static function canReport(array $user): bool
    {
        $role = mb_strtolower(trim((string) ($user['role'] ?? '')));
        return in_array($role, self::REPORTER_ROLES, true) && !empty($user['class_id']);
    }

    // Semester mengikuti aturan hub user: Sep-Feb ganjil, Mar-Agu genap
    private static function semesterFor(int $month): string
    {
        return ($month >= 6 || $month <= 1) ? 'ganjil' : 'genap';
    }

    // Mengembalikan [sedangBerlangsung, sudahBerakhir]
    private function timeState(string $date, string $start, string $end, DateTime $now): array
    {
        $today = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');
        $inProgress = $date === $today && $nowTime >= $start && $nowTime < $end;
        $ended = $date < $today || ($date === $today && $nowTime >= $end);
        return [$inProgress, $ended];
    }

    private function lock(string $key): void
    {
        $stmt = $this->conn->prepare("SELECT pg_advisory_xact_lock(hashtext(:k))");
        $stmt->execute([':k' => 'activity:' . $key]);
    }

    // ------------------------------------------------------------------
    // Data pendukung form
    // ------------------------------------------------------------------

    public function getActivityTypes(): array
    {
        return $this->conn->query("SELECT id, name FROM activity_types ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClassName(?string $classId): ?string
    {
        if (!$classId) {
            return null;
        }
        $stmt = $this->conn->prepare("SELECT name FROM student_class WHERE id = :id");
        $stmt->execute([':id' => $classId]);
        $name = $stmt->fetchColumn();
        return $name === false ? null : $name;
    }

    // ------------------------------------------------------------------
    // Sesi kuliah yang boleh dilaporkan oleh user ini
    // ------------------------------------------------------------------

    /**
     * @param bool $onlyNeedingReport true: hanya sesi yang belum dilaporkan
     */
    public function getSessions(array $user, bool $onlyNeedingReport = false): array
    {
        $sessions = $this->buildSessions($user);
        if ($onlyNeedingReport) {
            $sessions = array_values(array_filter($sessions, function ($s) {
                return !$s['reported'];
            }));
        }
        return $sessions;
    }

    // Dipakai controller: memastikan sesi yang dikirim form memang diizinkan untuk user ini
    public function findSession(array $user, string $key): ?array
    {
        foreach ($this->buildSessions($user) as $s) {
            if ($s['key'] === $key) {
                return $s;
            }
        }
        return null;
    }

    private function buildSessions(array $user): array
    {
        if (!self::canReport($user)) {
            return [];   // hanya mahasiswa yang terdaftar pada sebuah kelas
        }

        $now = $this->now();
        $to = $now->format('Y-m-d');
        $from = (clone $now)->modify('-' . (self::WINDOW_DAYS - 1) . ' days')->format('Y-m-d');
        $classId = $user['class_id'];

        $stmt = $this->conn->prepare(
            "SELECT s.id, s.day_of_week, s.start_time, s.end_time, s.semester::text AS semester,
                    s.room_id, rm.name AS room_name, rm.building, rm.capacity,
                    co.name AS course, co.kode_mk,
                    l.name  AS lecturer,
                    sc.name AS class_name
             FROM schedules s
             JOIN rooms rm          ON rm.id = s.room_id
             JOIN courses co        ON co.id = s.course_id
             JOIN lecturers l       ON l.id  = s.lecturer_id
             JOIN student_class sc  ON sc.id = s.student_class_id
             JOIN academic_years ay ON ay.id = sc.academic_year_id
             WHERE s.student_class_id = :class_id
               AND ay.is_active"
        );
        $stmt->execute([':class_id' => $classId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$schedules) {
            return [];
        }

        $ids = array_column($schedules, 'id');
        $in = implode(',', array_fill(0, count($ids), '?'));

        // Tanggal yang dibatalkan/dipindah lewat pengecualian jadwal
        $stmt = $this->conn->prepare(
            "SELECT schedule_id, date::text AS date
             FROM schedule_exceptions
             WHERE schedule_id IN ($in)
               AND date BETWEEN ? AND ?
               AND type::text IN ('cancelled', 'moved')"
        );
        $stmt->execute(array_merge($ids, [$from, $to]));
        $cancelled = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $cancelled[$r['schedule_id'] . '|' . $r['date']] = true;
        }

        // Sesi yang sudah dilaporkan
        $stmt = $this->conn->prepare(
            "SELECT schedule_id, report_date::text AS report_date
             FROM reports
             WHERE schedule_id IN ($in)
               AND report_date BETWEEN ? AND ?"
        );
        $stmt->execute(array_merge($ids, [$from, $to]));
        $reported = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $reported[$r['schedule_id'] . '|' . $r['report_date']] = true;
        }

        $nowTime = $now->format('H:i:s');
        $sessions = [];
        $period = new DatePeriod(new DateTime($from), new DateInterval('P1D'), (new DateTime($to))->modify('+1 day'));
        foreach ($period as $day) {
            $date = $day->format('Y-m-d');
            $dow = (int) $day->format('N');
            $sem = self::semesterFor((int) $day->format('n'));

            foreach ($schedules as $s) {
                if ((int) $s['day_of_week'] !== $dow) {
                    continue;
                }
                if ($s['semester'] !== null && strtolower($s['semester']) !== $sem) {
                    continue;
                }
                if (isset($cancelled[$s['id'] . '|' . $date])) {
                    continue;
                }
                if ($date === $to && $nowTime < $s['start_time']) {
                    continue;   // sesi hari ini yang belum dimulai
                }

                [$inProgress, $ended] = $this->timeState($date, $s['start_time'], $s['end_time'], $now);

                $sessions[] = [
                    'key' => 's:' . $s['id'] . ':' . $date,
                    'schedule_id' => $s['id'],
                    'date' => $date,
                    'start' => substr($s['start_time'], 0, 5),
                    'end' => substr($s['end_time'], 0, 5),
                    'start_full' => $s['start_time'],
                    'end_full' => $s['end_time'],
                    'room_id' => $s['room_id'],
                    'room' => $s['room_name'],
                    'building' => $s['building'],
                    'capacity' => (int) $s['capacity'],
                    'title' => $s['course'],
                    'code' => $s['kode_mk'],
                    'lecturer' => $s['lecturer'],
                    'class' => $s['class_name'],
                    'in_progress' => $inProgress,
                    'ended' => $ended,
                    'reported' => isset($reported[$s['id'] . '|' . $date]),
                ];
            }
        }

        // Tanggal terbaru dulu, lalu jam mulai
        usort($sessions, function ($a, $b) {
            return ($b['date'] <=> $a['date']) ?: strcmp($a['start_full'], $b['start_full']);
        });
        return $sessions;
    }

    // ------------------------------------------------------------------
    // Log okupansi per sesi
    // ------------------------------------------------------------------

    // Cari log sesi ini (dikunci untuk update di dalam transaksi)
    private function findLog(array $session): ?array
    {
        $next = (new DateTime($session['date']))->modify('+1 day')->format('Y-m-d');
        $stmt = $this->conn->prepare(
            "SELECT id, end_time::text AS end_time
             FROM occupancy_logs
             WHERE schedule_id = :sid
               AND start_time >= :d_start
               AND start_time <  :d_next
             ORDER BY start_time DESC
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([
            ':sid' => $session['schedule_id'],
            ':d_start' => $session['date'] . ' 00:00:00',
            ':d_next' => $next . ' 00:00:00',
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function insertLog(array $session, string $userId, string $endTs): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO occupancy_logs (room_id, user_id, schedule_id, start_time, end_time)
             VALUES (:room_id, :user_id, :schedule_id, :start_time, :end_time)"
        );
        $stmt->execute([
            ':room_id' => $session['room_id'],
            ':user_id' => $userId,
            ':schedule_id' => $session['schedule_id'],
            ':start_time' => $session['date'] . ' ' . $session['start_full'],
            ':end_time' => $endTs,
        ]);
    }

    // ------------------------------------------------------------------
    // Simpan laporan
    // ------------------------------------------------------------------

    /**
     * @throws DomainException jika sesi ini sudah dilaporkan
     */
    public function saveReport(array $session, array $user, array $d): void
    {
        $this->conn->beginTransaction();
        try {
            $this->lock($session['key']);

            $stmt = $this->conn->prepare(
                "SELECT 1 FROM reports WHERE schedule_id = :sid AND report_date = :d"
            );
            $stmt->execute([':sid' => $session['schedule_id'], ':d' => $session['date']]);
            if ($stmt->fetchColumn()) {
                throw new DomainException('Sesi ini sudah dilaporkan.');
            }

            // Waktu siswa bubar: waktu tombol Bubar jika ada, jika tidak jam selesai jadwal
            $log = $this->findLog($session);
            if ($log && $log['end_time'] !== null) {
                $studentLeaving = substr($log['end_time'], 11, 8);
            } elseif ($session['ended']) {
                $studentLeaving = $session['end_full'];
            } else {
                $studentLeaving = null;
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO reports
                    (schedule_id, user_id, activity_type_id, mode, description, report_date,
                     lecturer_enter, lecturer_leave, student_leaving, student_attendance, lecturer_attendance)
                 VALUES
                    (:schedule_id, :user_id, :activity_type_id, CAST(:mode AS report_mode), :description,
                     :report_date, :lecturer_enter, :lecturer_leave, :student_leaving,
                     :student_attendance, :lecturer_attendance)"
            );
            $stmt->execute([
                ':schedule_id' => $session['schedule_id'],
                ':user_id' => $user['id'],
                ':activity_type_id' => $d['activity_type_id'],
                ':mode' => $d['mode'],
                ':description' => $d['description'],
                ':report_date' => $session['date'],
                ':lecturer_enter' => $d['lecturer_enter'],
                ':lecturer_leave' => $d['lecturer_leave'],
                ':student_leaving' => $studentLeaving,
                ':student_attendance' => (int) $d['student_attendance'],
                ':lecturer_attendance' => $d['lecturer_attendance'],
            ]);

            // Sesi yang sudah berakhir tanpa log (tidak ada yang menekan Bubar): catat log penggunaan
            // sesuai jam jadwal agar riwayat penggunaan ruangan lengkap.
            if ($session['ended'] && $log === null) {
                $this->insertLog($session, $user['id'], $session['date'] . ' ' . $session['end_full']);
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    // ------------------------------------------------------------------
    // Mahasiswa Bubar: akhiri pemakaian ruangan sekarang juga
    // ------------------------------------------------------------------

    /**
     * @return string jam bubar (HH:MM)
     * @throws DomainException jika sesi tidak sedang berlangsung atau sudah dibubarkan
     */
    public function dismiss(array $session, array $user): string
    {
        $this->conn->beginTransaction();
        try {
            $this->lock($session['key']);

            $now = $this->now();
            [$inProgress] = $this->timeState($session['date'], $session['start_full'], $session['end_full'], $now);
            if (!$inProgress) {
                throw new DomainException('Bubar hanya bisa dikonfirmasi untuk sesi yang sedang berlangsung.');
            }

            $nowTs = $now->format('Y-m-d H:i:s');
            $log = $this->findLog($session);

            if ($log && $log['end_time'] !== null && $log['end_time'] <= $nowTs) {
                throw new DomainException('Sesi ini sudah dibubarkan sebelumnya.');
            }

            if ($log) {
                $stmt = $this->conn->prepare("UPDATE occupancy_logs SET end_time = :t WHERE id = :id");
                $stmt->execute([':t' => $nowTs, ':id' => $log['id']]);
            } else {
                $this->insertLog($session, $user['id'], $nowTs);
            }

            // Jika laporan sudah ada, perbarui jam siswa bubar
            $stmt = $this->conn->prepare(
                "UPDATE reports SET student_leaving = :t WHERE schedule_id = :sid AND report_date = :d"
            );
            $stmt->execute([
                ':t' => $now->format('H:i:s'),
                ':sid' => $session['schedule_id'],
                ':d' => $session['date'],
            ]);

            $this->conn->commit();
            return $now->format('H:i');
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    // ------------------------------------------------------------------
    // Admin: hanya membaca
    // ------------------------------------------------------------------

    /**
     * Semua laporan pada rentang tanggal (Y-m-d), terbaru dulu.
     * Menambahkan kolom final_label dan final_tone (cara sesi berakhir).
     */
    public function getAdminList(string $from, string $to): array
    {
        $stmt = $this->conn->prepare(
            "SELECT r.id, r.report_date::text AS report_date,
                    s.start_time, s.end_time,
                    rm.name AS room_name, rm.building,
                    co.kode_mk, co.name AS course,
                    l.name  AS lecturer,
                    sc.name AS class_name,
                    u.name  AS reporter,
                    act.name AS activity_type,
                    r.mode::text AS mode,
                    r.description,
                    r.lecturer_enter, r.lecturer_leave, r.student_leaving,
                    r.student_attendance, r.lecturer_attendance
             FROM reports r
             JOIN schedules s       ON s.id   = r.schedule_id
             JOIN rooms rm          ON rm.id  = s.room_id
             JOIN courses co        ON co.id  = s.course_id
             JOIN lecturers l       ON l.id   = s.lecturer_id
             JOIN student_class sc  ON sc.id  = s.student_class_id
             JOIN users u           ON u.id   = r.user_id
             JOIN activity_types act ON act.id = r.activity_type_id
             WHERE r.report_date BETWEEN :from AND :to
             ORDER BY r.report_date DESC, s.start_time DESC, rm.name"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $now = $this->now();
        foreach ($rows as &$r) {
            [, $ended] = $this->timeState($r['report_date'], $r['start_time'], $r['end_time'], $now);
            $leaving = $r['student_leaving'];

            if ($leaving !== null && substr($leaving, 0, 5) < substr($r['end_time'], 0, 5)) {
                $r['final_label'] = 'Bubar lebih awal ' . substr($leaving, 0, 5);
                $r['final_tone'] = 'warning';
            } elseif ($ended || $leaving !== null) {
                $r['final_label'] = 'Selesai sesuai jadwal';
                $r['final_tone'] = 'success';
            } else {
                $r['final_label'] = 'Sedang berlangsung';
                $r['final_tone'] = 'secondary';
            }
        }
        unset($r);

        return $rows;
    }
}
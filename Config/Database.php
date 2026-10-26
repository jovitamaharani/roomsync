<?php
class Database {
    private $host = "aws-0-ap-southeast-1.pooler.supabase.com";
    private $port = "5432";
    private $db   = "postgres";
    private $user = "postgres.fgliuztmcniiftqprnck";
    private $pass = "polinemajosjis";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->db}", 
                $this->user, 
                $this->pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }

        return $this->conn;
    }
}
<?php
    class Database{
    private $host = "aws-0-ap-southeast-1.pooler.supabase.com";
    private $port = "5432";
    private $db   = "postgres";
    private $user = "postgres.fgliuztmcniiftqprnck";
    private $pass = "polinemajosjis";
    public $conn;

        public function getConnection(){
            $this->conn = null;
            try{
                $this->conn = new PDO("pgsql:host=" . $this->host . ";dbname=" . $this->db, $this->user, $this->pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }    
            catch(PDOException $exception){
                http_response_code(500);
                echo "Koneksi gagal...". $exception->getMessage();
            }
            return $this->conn;
        }
    }
?>
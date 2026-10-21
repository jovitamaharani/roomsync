<?php
    class Database{
        private $host = "";
        private $db_name = "";
        private $username = "";
        private $password = "";
        public $conn;

        public function getConnection(){
            $this->conn = null;
            try{
                $this->conn = new PDO("pgsql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }    
            catch(PDOException $exception){
                echo "Koneksi gagal...". $exception->getMessage();
            }
            return $this->conn;
        }
    }
?>
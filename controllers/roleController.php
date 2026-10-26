<?php
    class roleController{
    private $conn;
    private $table_name = "roles";
    public function __construct($db){
        $this->conn = $db;
    }
    public function read(){
        $stmt = $this->conn->prepare("SELECT id, role FROM $this->table_name");
    }
    }
?>
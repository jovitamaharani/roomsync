<?php
    class scheduleException{
    private $conn;
    private $table = "academic_year";

    public function __construct($db){
        $this->conn = $db;
    }
    }
?>
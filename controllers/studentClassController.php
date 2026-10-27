<?php
class ClassController{
    private $conn;
    private $table_name = "class";

    public function __construct($db) {
        $this->conn = $db;
    }
    public function read(){
        $stmt = $this->conn->prepare("SELECT id, class_year_id, name FROM " . $this->table_name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
?>
<?php

class Database {
    private $host = "localhost";
    private $db_name = "Rekrutacja";
    private $username = "root";
    private $password = "123";
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name;charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Błąd połączenia: " . $e->getMessage());
        }
    }
    public function getConnection() {
        return $this->conn;
    }
}

?>
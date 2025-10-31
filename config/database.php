<?php
class Database {
    private $host = "localhost";
    private $db_name = "sports_management";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            // Log error instead of displaying it
            error_log("Connection error: " . $exception->getMessage());
            // You can show a generic error message to users
            die("Database connection failed. Please try again later.");
        }
        return $this->conn;
    }
}
?>
<?php
class Database {
    private $host = "localhost";      // Database server
    private $db_name = "lostfounddb"; // Your DB name
    private $username = "root";       // Default XAMPP/WAMP user
    private $password = "";           // Default is empty
    public $conn;

    // Get DB connection
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>


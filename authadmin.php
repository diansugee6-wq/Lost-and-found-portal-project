<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfounddb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin table exists, if not create it
$tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($tableCheck->num_rows == 0) {
    // Create admin table
    $sql = "CREATE TABLE admin_users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql)) {
        // Insert default admin user
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admin_users (username, password, email) 
                VALUES ('admin', '$defaultPassword', 'admin@lostandfound.com')";
        $conn->query($sql);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header("Location: admindashboard.php");
            exit();
        } else {
            header("Location: loginadmin.html?error=invalid");
            exit();
        }
    } else {
        header("Location: loginadmin.html?error=invalid");
        exit();
    }
} else {
    header("Location: loginadmin.html");
    exit();
}

$conn->close();
?>
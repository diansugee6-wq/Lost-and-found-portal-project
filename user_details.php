<?php
// create_database.php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS lostfounddb";
if ($conn->query($sql) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("lostfounddb");

// SQL to create user_details table
$sql = "CREATE TABLE IF NOT EXISTS user_details (
    id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(250) DEFAULT NULL,
    email VARCHAR(100) NOT NULL,
    nic VARCHAR(20) NOT NULL,
    address_line1 VARCHAR(150) NOT NULL,
    address_line2 VARCHAR(150) DEFAULT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql)) {
    echo "Table user_details created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert sample data
$sql = "INSERT INTO user_details (full_name, username, email, nic, address_line1, address_line2, contact_number, password) VALUES
('Okithma De Silva', NULL, 'chamanisilva7312@gmail.com', '200309810234', 'No. 180, Waduramulla Watta', 'Panadura', '07771011548', '$2y$10$6JcfTR5L.GruWY45502tdO8/zFSVX/UJcRfzy6zbCLYcz7ZiIGcKC'),
('shashini nethmika', 'shashini2003', 'wowcdesilva2003@gmail.com', '200309810237', 'no 180,waduramulla watta', 'panadura', '0887539430', '$2y$10$Ohlm2sT2LbaP/w.7Riwp7eqCCG6LND5FHt1l1dZfS6RiOwBmYys6y')";

if ($conn->query($sql)) {
    echo "Sample data inserted successfully<br>";
} else {
    echo "Error inserting data: " . $conn->error . "<br>";
}

$conn->close();
echo "Database setup complete! <a href='admindashboard.php'>Go to Admin Dashboard</a>";
?>
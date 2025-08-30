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

// We use the existing user_details table and role column (role=1 for admin)
// Ensure there is at least one admin user (siteadmin) in user_details
$adminCheck = $conn->prepare("SELECT id FROM user_details WHERE role = 1 LIMIT 1");
$adminCheck->execute();
$adminResult = $adminCheck->get_result();
if ($adminResult->num_rows == 0) {
    // Insert default admin into user_details
    $defaultPassword = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO user_details (full_name, username, email, nic, address_line1, contact_number, password, status, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 1)");
    $full = 'Site Admin'; $uname = 'siteadmin'; $emailAddr = 'admin@lostandfound.com'; $nic = '1111111111'; $addr = 'Admin Address'; $contact = '0000000000';
    $stmt->bind_param('sssssss', $full, $uname, $emailAddr, $nic, $addr, $contact, $defaultPassword);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Get admin user from user_details with role=1
    $stmt = $conn->prepare("SELECT id, username, password FROM user_details WHERE username = ? AND role = 1 LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set both admin-specific and generic session keys used by the app
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            // Generic keys expected by admin pages
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = 1; // role=1 -> admin
            $_SESSION['last_activity'] = time();
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
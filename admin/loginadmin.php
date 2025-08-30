<?php
session_start();
require_once 'configure.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username = :username LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admindashboard.php");
            exit();
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "No admin found with that username.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
</head>
<body>
    <h2>Admin Login</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <p style="color:red;"><?= $message ?></p>
</body>
</html>

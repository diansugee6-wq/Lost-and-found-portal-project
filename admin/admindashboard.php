<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginadmin.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Fetch all users
$users = $db->query("SELECT * FROM user_details")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all reported items
$items = $db->query("SELECT * FROM reported_items")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 5px; }
    </style>
</head>
<body>
    <h2>Welcome, <?= $_SESSION['admin_username'] ?> (Admin)</h2>
    <a href="logoutadmin.php">Logout</a>

    <h3>User Details</h3>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Action</th></tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['username'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><a href="delete_user.php?id=<?= $user['id'] ?>">Delete</a></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Reported Items</h3>
    <table>
        <tr><th>ID</th><th>Item Name</th><th>Description</th><th>Action</th></tr>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?= $item['item_name'] ?></td>
                <td><?= $item['description'] ?></td>
                <td><a href="delete_item.php?id=<?= $item['id'] ?>">Delete</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

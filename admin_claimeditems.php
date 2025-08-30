<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: loginuser.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT * FROM claimed_items ORDER BY claimed_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$claimed = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claimed Items</title>
</head>
<body>
    <h2>Claimed Items</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>Item ID</th>
            <th>Item Name</th>
            <th>Reported By</th>
            <th>Claimed By</th>
            <th>Claimer ID</th>
            <th>Contact Number</th>
            <th>Image</th>
            <th>Claimed At</th>
        </tr>
        <?php foreach ($claimed as $row): ?>
        <tr>
            <td><?= $row['item_id'] ?></td>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= htmlspecialchars($row['reported_by']) ?></td>
            <td><?= htmlspecialchars($row['claimed_by']) ?></td>
            <td><?= htmlspecialchars($row['claimer_id']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td>
                <?php if ($row['image_path']): ?>
                    <img src="<?= htmlspecialchars($row['image_path']) ?>" width="100">
                <?php endif; ?>
            </td>
            <td><?= $row['claimed_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    img {
        border-radius: 8px;
        object-fit: cover;
    }       
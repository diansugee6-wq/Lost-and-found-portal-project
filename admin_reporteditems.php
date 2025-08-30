<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
    header("Location: loginuser.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get all reported items with user info
$sql = "SELECT r.item_id, r.item_name, r.category, r.description, 
               r.lost_date, r.lost_location, r.reported_at, 
               r.image_path,
               u.full_name, u.username, u.email 
        FROM reported_items r
        JOIN user_details u ON r.user_id = u.id
        ORDER BY r.reported_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reported Items</title>
</head>
<body>
<h2>Reported Items</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Description</th>
        <th>Lost Date</th>
        <th>Lost Location</th>
        <th>Reported On</th>
        <th>Reported By</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <?php foreach ($reports as $report): ?>
    <tr>
        <td><?= $report['item_id'] ?></td>
        <td>
            <?php if (!empty($report['image_path'])): ?>
                <img src="<?= htmlspecialchars($report['image_path']) ?>" width="100" height="100" style="object-fit:cover;border-radius:8px;">
            <?php else: ?>
                No Image
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($report['item_name']) ?></td>
        <td><?= htmlspecialchars($report['category']) ?></td>
        <td><?= nl2br(htmlspecialchars($report['description'])) ?></td>
        <td><?= $report['lost_date'] ?></td>
        <td><?= htmlspecialchars($report['lost_location']) ?></td>
        <td><?= $report['reported_at'] ?></td>
        <td><?= htmlspecialchars($report['full_name']) ?> (<?= htmlspecialchars($report['username']) ?>)</td>
        <td><?= htmlspecialchars($report['email']) ?></td>
        <td>
            <form action="claim_item.php" method="get">
                <input type="hidden" name="item_id" value="<?= $report['item_id'] ?>">
                <input type="hidden" name="item_name" value="<?= htmlspecialchars($report['item_name']) ?>">
                <input type="hidden" name="reported_by" value="<?= htmlspecialchars($report['full_name']) ?>">
                <button type="submit">Claim</button>
            </form>
        </td>
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
    h2 {
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
</style>
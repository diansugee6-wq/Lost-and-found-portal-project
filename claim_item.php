<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: loginuser.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    // Fetch the reported item details
    $sql = "SELECT r.item_id, r.item_name, r.description, r.image_path, 
                   u.full_name AS reported_by
            FROM reported_items r
            JOIN user_details u ON r.user_id = u.id
            WHERE r.item_id = :item_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':item_id' => $item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Item not found!");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claimed_by     = $_POST['claimed_by'];
    $claimer_id     = $_POST['claimer_id'];
    $contact_number = $_POST['contact_number'];

    // Insert into claimed_items
    $insert = $conn->prepare("INSERT INTO claimed_items 
        (item_id, item_name, reported_by, claimed_by, claimer_id, contact_number, image_path) 
        VALUES (:item_id, :item_name, :reported_by, :claimed_by, :claimer_id, :contact_number, :image_path)");
    $insert->execute([
        ':item_id' => $item['item_id'],
        ':item_name' => $item['item_name'],
        ':reported_by' => $item['reported_by'],
        ':claimed_by' => $claimed_by,
        ':claimer_id' => $claimer_id,
        ':contact_number' => $contact_number,
        ':image_path' => $item['image_path']
    ]);

    // Delete from reported_items
    $delete = $conn->prepare("DELETE FROM reported_items WHERE item_id = :item_id");
    $delete->execute([':item_id' => $item['item_id']]);

    header("Location: admin_claimeditems.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Claim Item</title>
</head>
<body>
    <h2>Claim Item</h2>
    <p><strong>Item Name:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></p>
    <?php if ($item['image_path']): ?>
        <p><img src="<?= htmlspecialchars($item['image_path']) ?>" width="200"></p>
    <?php endif; ?>

    <form method="post">
        <label>Claimed By:</label>
        <input type="text" name="claimed_by" required><br><br>

        <label>Claimer ID:</label>
        <input type="text" name="claimer_id" required><br><br>

        <label>Contact Number:</label>
        <input type="text" name="contact_number" required><br><br>

        <button type="submit">Confirm Claim</button>
    </form>
</body>
</html>

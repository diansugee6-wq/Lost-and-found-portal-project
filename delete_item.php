<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: loginadmin.php");
    exit();
}

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM reported_items WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
}

header("Location: admindashboard.php");
exit();
?>

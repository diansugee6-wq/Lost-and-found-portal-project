<?php
// --- DB connection ---
$conn = new mysqli("localhost", "root", "", "lostfounddb");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch ONLY Lost Items
$sql = "SELECT id, item_name, description, status, reported_by, date_reported 
        FROM items 
        WHERE status = 'Lost'
        ORDER BY date_reported DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lost Items</title>
  <style>
    body { margin:0; font-family: Arial, sans-serif; background:#f4f6fa; }
    .sidebar {
        width: 220px; background:#2c3e50; color:white; height:100vh; position:fixed; top:0; left:0; padding:20px;
    }
    .sidebar h2 { text-align:center; margin-bottom:30px; }
    .sidebar a { display:block; padding:10px; color:white; text-decoration:none; margin:8px 0; border-radius:5px; }
    .sidebar a:hover { background:#34495e; }
    .main { margin-left:240px; padding:20px; }
    h1 { color:#333; }
    table { width:100%; border-collapse:collapse; margin-top:20px; background:white; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    th, td { padding:12px; border:1px solid #ddd; text-align:left; }
    th { background:#2c3e50; color:white; }
    tr:nth-child(even) { background:#f9f9f9; }
    .logout { margin-top:20px; text-align:center; }
    .logout a { color:#e74c3c; text-decoration:none; font-weight:bold; }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="lost_items.php">Lost Items</a>
    <a href="found_items.php">Found Items</a>
    <a href="users.php">Users</a>
    <a href="claims.php">Claims</a>
    <div class="logout"><a href="loginadmin.html">Logout</a></div>
  </div>

  <div class="main">
    <h1>Lost Items</h1>
    <p>Here are all the items reported as <b>Lost</b>:</p>

    <table>
      <tr>
        <th>ID</th>
        <th>Item Name</th>
        <th>Description</th>
        <th>Status</th>
        <th>Reported By</th>
        <th>Date Reported</th>
      </tr>
      <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['item_name']}</td>
                      <td>{$row['description']}</td>
                      <td>{$row['status']}</td>
                      <td>{$row['reported_by']}</td>
                      <td>{$row['date_reported']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No Lost Items found</td></tr>";
      }
      ?>
    </table>
  </div>
</body>
</html>
<?php $conn->close(); ?>

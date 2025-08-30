<?php
// --- DB connection ---
$conn = new mysqli("localhost", "root", "", "lostfounddb");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch claims with user + item details
$sql = "SELECT c.id, i.item_name, u.full_name, u.email, c.claim_status, c.claim_date
        FROM claims c
        JOIN items i ON c.item_id = i.id
        JOIN user_details u ON c.user_id = u.id
        ORDER BY c.claim_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Claims</title>
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
    <h1>Claims</h1>
    <p>All claims submitted by users for lost/found items:</p>

    <table>
      <tr>
        <th>Claim ID</th>
        <th>Item</th>
        <th>User</th>
        <th>Email</th>
        <th>Status</th>
        <th>Claim Date</th>
      </tr>
      <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['item_name']}</td>
                      <td>{$row['full_name']}</td>
                      <td>{$row['email']}</td>
                      <td>{$row['claim_status']}</td>
                      <td>{$row['claim_date']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No claims found</td></tr>";
      }
      ?>
    </table>
  </div>
</body>
</html>
<?php $conn->close(); ?>

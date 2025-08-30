<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Claimed Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --yellow:#fbb117; --white:#fff; --gray:#f5f5f5; --black1:#222; }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--gray); color: var(--black1); min-height:100vh; }
        .container { display:flex; min-height:100vh; }
        /* Sidebar (shared) */
        .navigation { width:280px; background:var(--yellow); border-left:10px solid var(--yellow); overflow:hidden; height:100vh; position:fixed; left:0; top:0; box-shadow:0 0 15px rgba(0,0,0,0.1); }
        .navigation ul { margin:0; padding:0; }
        .navigation ul li { list-style:none; }
        .navigation ul li a { display:flex; align-items:center; padding:20px 15px; color:var(--black1); text-decoration:none; transition:.3s; border-top-left-radius:40px; border-bottom-left-radius:40px; }
        .navigation ul li a:hover { background-color:#ffffff33; }
        .navigation ul li a.active { background-color: var(--white); }
        .navigation ul li a .icon { min-width:50px; text-align:center; font-size:20px; }
        .navigation ul li a .title { font-size:16px; font-weight:500; }
        .logo-link { display:flex; flex-direction:column; align-items:center; gap:8px; padding:30px 15px; color:var(--black1); text-decoration:none; border-bottom:2px solid rgba(0,0,0,0.1); }
        .logo-link .icon img { width:100px; height:100px; object-fit:contain; display:block; }
        .logo-text { font-size:22px; font-weight:bold; text-align:center; }
        /* Main content */
        .main-content { flex:1; margin-left:280px; padding:25px; }
        .dashboard-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; padding-bottom:15px; border-bottom:2px solid var(--yellow); }
        .dashboard-title { font-size:28px; font-weight:700; }
        .toolbar { display:flex; gap:10px; align-items:center; }
        .search-input { padding:10px 14px; border:1px solid #ddd; border-radius:8px; width:280px; font-size:14px; background:#fff; }
        .stats { display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
        .badge { background:#fff; border:1px solid #eee; border-radius:8px; padding:8px 12px; font-size:13px; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
        /* Table */
        .table-container { background:#fff; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.08); overflow:hidden; }
        .table-header { padding:16px 20px; background:#f9f9f9; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
        .table-title { font-size:18px; font-weight:600; }
        table { width:100%; border-collapse:collapse; min-width:1000px; }
        th { background:var(--yellow); color:#111; text-align:left; padding:14px 16px; font-weight:600; font-size:14px; }
        td { padding:12px 16px; border-bottom:1px solid #eee; font-size:14px; vertical-align:top; }
        tr:hover { background:#fafafa; }
        .thumb { width:80px; height:80px; object-fit:cover; border-radius:8px; background:#f2f2f2; }
        .muted { color:#666; }
        .nowrap { white-space:nowrap; }
        /* Responsive */
        @media (max-width: 1100px) { .navigation{width:230px;} .main-content{margin-left:230px;} }
        @media (max-width: 900px) { .container{flex-direction:column;} .navigation{width:100%; height:auto; position:relative;} .main-content{margin-left:0;} }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="navigation">
            <a href="#" class="logo-link">
                <span class="icon"><img src="logo2.png" alt="Logo"></span>
                <span class="logo-text">Lost & Found Admin</span>
            </a>
            <ul>
                <li><a href="admindashboard.php"><span class="icon"><i class="fas fa-chart-line"></i></span><span class="title">Dashboard</span></a></li>
                <li><a href="admin_reporteditems.php"><span class="icon"><i class="fas fa-clipboard-list"></i></span><span class="title">Item Reports</span></a></li>
                <li><a href="admin_claimeditems.php" class="active"><span class="icon"><i class="fas fa-check-circle"></i></span><span class="title">Item Claims</span></a></li>
                <li><a href="home.php"><span class="icon"><i class="fas fa-home"></i></span><span class="title">Back to Home</span></a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Claimed Items</h1>
                <div class="toolbar">
                    <input id="filterInput" type="text" class="search-input" placeholder="Filter by item, claimed by, or reporter..." />
                </div>
            </div>

            <div class="stats">
                <div class="badge">Total claimed: <strong><?= count($claimed) ?></strong></div>
                <?php if (!empty($claimed)): ?>
                  <div class="badge">Latest: <strong><?= htmlspecialchars(date('Y-m-d H:i', strtotime($claimed[0]['claimed_at']))) ?></strong></div>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-box-open"></i> Claimed Items List</div>
                </div>
                <div style="overflow-x:auto;">
                    <table id="claimsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Item Name</th>
                                <th>Reported By</th>
                                <th>Claimed By</th>
                                <th>Claimer ID</th>
                                <th>Contact</th>
                                <th>Claimed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($claimed as $row): ?>
                            <tr>
                                <td class="nowrap"><?= (int)$row['item_id'] ?></td>
                                <td>
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img class="thumb" src="<?= htmlspecialchars($row['image_path']) ?>" alt="Item">
                                    <?php else: ?>
                                        <span class="muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['item_name']) ?></td>
                                <td><?= htmlspecialchars($row['reported_by']) ?></td>
                                <td><?= htmlspecialchars($row['claimed_by']) ?></td>
                                <td><?= htmlspecialchars($row['claimer_id']) ?></td>
                                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                <td class="nowrap"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['claimed_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
      // Simple client-side filter across key columns
      (function(){
        const input = document.getElementById('filterInput');
        const table = document.getElementById('claimsTable');
        if (!input || !table) return;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const idx = { item: 2, reportedBy: 3, claimedBy: 4 };
        input.addEventListener('input', function(){
          const q = this.value.trim().toLowerCase();
          rows.forEach(tr => {
            const cells = tr.children;
            const hay = [cells[idx.item], cells[idx.reportedBy], cells[idx.claimedBy]]
              .map(td => td ? td.textContent.toLowerCase() : '').join(' ');
            tr.style.display = hay.includes(q) ? '' : 'none';
          });
        });
      })();
    </script>
</body>
</html>

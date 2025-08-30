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
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin • Reported Items</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --yellow:#fbb117; --white:#fff; --gray:#f5f5f5; --black1:#222; }
            * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { background:var(--gray); color:var(--black1); min-height:100vh; }
            .container { display:flex; min-height:100vh; }
            /* Sidebar */
            .navigation { width:280px; background:var(--yellow); border-left:10px solid var(--yellow); overflow:hidden; height:100vh; position:fixed; left:0; top:0; box-shadow:0 0 15px rgba(0,0,0,0.1); }
            .navigation ul { margin:0; padding:0; }
            .navigation ul li { list-style:none; }
            .navigation ul li a { display:flex; align-items:center; padding:20px 15px; color:var(--black1); text-decoration:none; transition:.3s; border-top-left-radius:40px; border-bottom-left-radius:40px; }
            .navigation ul li a:hover { background:#ffffff33; }
            .navigation ul li a.active { background:var(--white); }
            .navigation ul li a .icon { min-width:50px; text-align:center; font-size:20px; }
            .navigation ul li a .title { font-size:16px; font-weight:500; }
            .logo-link { display:flex; flex-direction:column; align-items:center; gap:8px; padding:30px 15px; color:var(--black1); text-decoration:none; border-bottom:2px solid rgba(0,0,0,0.1); }
            .logo-link .icon img { width:100px; height:100px; object-fit:contain; display:block; }
            .logo-text { font-size:22px; font-weight:bold; text-align:center; }
            /* Main */
            .main-content { flex:1; margin-left:280px; padding:25px; }
            .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; padding-bottom:15px; border-bottom:2px solid var(--yellow); }
            .title { font-size:28px; font-weight:700; }
            .toolbar { display:flex; gap:10px; align-items:center; }
            .search-input { padding:10px 14px; border:1px solid #ddd; border-radius:8px; width:300px; background:#fff; }
            /* Table */
            .table-container { background:#fff; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.08); overflow:hidden; }
            .table-header { padding:16px 20px; background:#f9f9f9; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
            .table-title { font-size:18px; font-weight:600; }
            table { width:100%; border-collapse:collapse; min-width:1100px; }
            th { background:var(--yellow); color:#111; text-align:left; padding:14px 16px; font-weight:600; font-size:14px; }
            td { padding:12px 16px; border-bottom:1px solid #eee; font-size:14px; vertical-align:top; }
            tr:hover { background:#fafafa; }
            .thumb { width:80px; height:80px; object-fit:cover; border-radius:8px; background:#f2f2f2; }
            .muted { color:#666; }
            .nowrap { white-space:nowrap; }
            .btn { background:var(--yellow); border:none; padding:8px 12px; border-radius:6px; cursor:pointer; font-weight:600; }
            .btn:hover { filter:brightness(0.98); }
            @media (max-width:1100px){ .navigation{width:230px;} .main-content{margin-left:230px;} }
            @media (max-width:900px){ .container{flex-direction:column;} .navigation{width:100%; height:auto; position:relative;} .main-content{margin-left:0;} }
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
                <li><a href="usermanagement.php"><span class="icon"><i class="fas fa-users"></i></span><span class="title">User Management</span></a></li>
                <li><a href="admin_reporteditems.php" class="active"><span class="icon"><i class="fas fa-clipboard-list"></i></span><span class="title">Item Reports</span></a></li>
                <li><a href="admin_claimeditems.php"><span class="icon"><i class="fas fa-check-circle"></i></span><span class="title">Item Claims</span></a></li>
                <li><a href="settings.php"><span class="icon"><i class="fas fa-cog"></i></span><span class="title">Settings</span></a></li>
                <li><a href="/Lost-and-found-portal-project/home.php"><span class="icon"><i class="fas fa-home"></i></span><span class="title">Back to Home</span></a></li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <div class="header">
                <div class="title">Reported Items</div>
                <div class="toolbar">
                    <input type="text" id="filterInput" class="search-input" placeholder="Filter by item, category, or reporter...">
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title"><i class="fas fa-list"></i> Reports List</div>
                </div>
                <div style="overflow-x:auto;">
                    <table id="reportsTable">
                        <thead>
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
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td class="nowrap"><?= (int)$report['item_id'] ?></td>
                                <td>
                                    <?php
                                        $imagePathRaw = isset($report['image_path']) ? trim((string)$report['image_path']) : '';
                                        $webPath = '';
                                        if ($imagePathRaw !== '') {
                                            if (preg_match('~^(?:https?:)?//|^data:~i', $imagePathRaw)) {
                                                $webPath = $imagePathRaw;
                                            } else {
                                                $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                                                if ($imagePathRaw[0] === '/') {
                                                    $webPath = $basePath . $imagePathRaw;
                                                } else {
                                                    $webPath = $basePath . '/' . $imagePathRaw;
                                                }
                                            }
                                        }
                                        $hasImage = false;
                                        if ($webPath !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $webPath)) {
                                            $absWeb = ($webPath[0] === '/') ? $webPath : ('/' . $webPath);
                                            $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . str_replace('/', DIRECTORY_SEPARATOR, $absWeb);
                                            $hasImage = file_exists($fsPath);
                                        } elseif ($webPath !== '') {
                                            $hasImage = true; // assume remote/data present
                                        }
                                    ?>
                                    <?php if ($hasImage): ?>
                                        <img class="thumb" src="<?= htmlspecialchars($webPath) ?>" alt="Item image">
                                    <?php else: ?>
                                        <span class="muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string)$report['item_name']) ?></td>
                                <td><?= htmlspecialchars((string)$report['category']) ?></td>
                                <td><?= nl2br(htmlspecialchars((string)$report['description'])) ?></td>
                                <td class="nowrap"><?= !empty($report['lost_date']) ? htmlspecialchars($report['lost_date']) : '—' ?></td>
                                <td><?= htmlspecialchars((string)$report['lost_location']) ?></td>
                                <td class="nowrap"><?= htmlspecialchars($report['reported_at']) ?></td>
                                <td><?= htmlspecialchars((string)$report['full_name']) ?> (<?= htmlspecialchars((string)$report['username']) ?>)</td>
                                <td><?= htmlspecialchars((string)$report['email']) ?></td>
                                <td>
                                    <form action="claim_item.php" method="get" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?= (int)$report['item_id'] ?>">
                                        <input type="hidden" name="item_name" value="<?= htmlspecialchars((string)$report['item_name']) ?>">
                                        <input type="hidden" name="reported_by" value="<?= htmlspecialchars((string)$report['full_name']) ?>">
                                        <button type="submit" class="btn">Claim</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Client-side filter for quick find across item, category, reporter
        (function(){
            const input = document.getElementById('filterInput');
            const table = document.getElementById('reportsTable');
            if (!input || !table) return;
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const idx = { item: 2, category: 3, reporter: 8 };
            input.addEventListener('input', function(){
                const q = this.value.trim().toLowerCase();
                rows.forEach(tr => {
                    const cells = tr.children;
                    const hay = [cells[idx.item], cells[idx.category], cells[idx.reporter]]
                        .map(td => td ? td.textContent.toLowerCase() : '').join(' ');
                    tr.style.display = hay.includes(q) ? '' : 'none';
                });
            });
        })();
    </script>
</body>
</html>

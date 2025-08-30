<?php
session_start();
require_once 'configure.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
        header("Location: loginuser.php");
        exit();
}

$database = new Database();
$conn = $database->getConnection();

$item = null;
if (isset($_GET['item_id'])) {
        $item_id = $_GET['item_id'];

        // Fetch the reported item details
        $sql = "SELECT r.item_id, r.item_name, r.description, r.image_path, r.lost_date, r.lost_location,
                                     u.full_name AS reported_by
                        FROM reported_items r
                        JOIN user_details u ON r.user_id = u.id
                        WHERE r.item_id = :item_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':item_id' => $item_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) { die("Item not found!"); }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $item) {
        $claimed_by     = trim($_POST['claimed_by']);
        $claimer_id     = trim($_POST['claimer_id']);
        $contact_number = trim($_POST['contact_number']);

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Claim Item</title>
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
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding-bottom:12px; border-bottom:2px solid var(--yellow); }
        .title { font-size:26px; font-weight:700; }
        .card { background:#fff; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.08); padding:20px; }
        .item-preview { display:flex; gap:18px; align-items:flex-start; margin-bottom:16px; }
        .thumb { width:160px; height:160px; background:#f2f2f2; object-fit:cover; border-radius:10px; }
        .muted { color:#666; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 18px; margin-top:6px; }
        .grid label { font-weight:600; font-size:14px; color:#333; }
        .grid input { padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px; background:#fff; }
        .actions { display:flex; justify-content:flex-end; margin-top:14px; }
        .btn { background:var(--yellow); border:none; padding:10px 16px; border-radius:8px; font-weight:700; cursor:pointer; }
        .btn:hover { filter:brightness(0.98); }
        @media (max-width:900px){ .grid{grid-template-columns:1fr;} .thumb{width:120px;height:120px;} .main-content{margin-left:0;} .navigation{position:relative;width:100%;height:auto;} }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="navigation">
            <a href="#" class="logo-link">
                <span class="icon"><img src="logo2.png" alt="Logo"></span>
                <span class="logo-text">Lost & Found Admin</span></a>
            <ul>
                <li><a href="admindashboard.php"><span class="icon"><i class="fas fa-chart-line"></i></span><span class="title">Dashboard</span></a></li>
                <li><a href="admin_reporteditems.php"><span class="icon"><i class="fas fa-clipboard-list"></i></span><span class="title">Item Reports</span></a></li>
                <li><a href="admin_claimeditems.php" class="active"><span class="icon"><i class="fas fa-check-circle"></i></span><span class="title">Item Claims</span></a></li>
                <li><a href="/Lost-and-found-portal-project/home.php"><span class="icon"><i class="fas fa-home"></i></span><span class="title">Back to Home</span></a></li>
            </ul>
        </div>

        <!-- Main -->
        <div class="main-content">
            <div class="header">
                <div class="title">Confirm Claim</div>
            </div>

            <div class="card">
                <?php if ($item): ?>
                    <div class="item-preview">
                        <?php
                            $imagePathRaw = isset($item['image_path']) ? trim((string)$item['image_path']) : '';
                            $webPath = '';
                            if ($imagePathRaw !== '') {
                                if (preg_match('~^(?:https?:)?//|^data:~i', $imagePathRaw)) { $webPath = $imagePathRaw; }
                                else {
                                    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                                    $webPath = ($imagePathRaw[0] === '/') ? ($basePath . $imagePathRaw) : ($basePath . '/' . $imagePathRaw);
                                }
                            }
                            $hasImage = false;
                            if ($webPath !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $webPath)) {
                                $absWeb = ($webPath[0] === '/') ? $webPath : ('/' . $webPath);
                                $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . str_replace('/', DIRECTORY_SEPARATOR, $absWeb);
                                $hasImage = file_exists($fsPath);
                            } elseif ($webPath !== '') { $hasImage = true; }
                        ?>
                        <?php if ($hasImage): ?>
                            <img class="thumb" src="<?= htmlspecialchars($webPath) ?>" alt="Item" />
                        <?php else: ?>
                            <div class="thumb"></div>
                        <?php endif; ?>
                        <div>
                            <div style="font-size:18px;font-weight:700;"><?= htmlspecialchars($item['item_name']) ?></div>
                            <div class="muted" style="margin:6px 0;">Reported by: <?= htmlspecialchars($item['reported_by']) ?></div>
                            <div class="muted">Lost: <?= !empty($item['lost_date']) ? htmlspecialchars($item['lost_date']) : '—' ?> • <?= htmlspecialchars((string)$item['lost_location']) ?></div>
                            <div style="margin-top:10px;line-height:1.5;"><?= nl2br(htmlspecialchars($item['description'])) ?></div>
                        </div>
                    </div>

                    <form method="post" class="grid">
                        <div>
                            <label>Claimed By</label>
                            <input type="text" name="claimed_by" required>
                        </div>
                        <div>
                            <label>Claimer ID</label>
                            <input type="text" name="claimer_id" required>
                        </div>
                        <div>
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" required>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn"><i class="fas fa-check"></i> Confirm Claim</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="muted">No item selected.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

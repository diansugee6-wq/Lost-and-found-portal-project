<?php
session_start();

// At the top of your file, after session_start()
$fullName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Include your existing configuration file
include 'configure.php';

// Create database connection using your Database class
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed. Please check your configure.php file.");
}

// Handle search and filter functionality
$search_query = "";
$category_filter = "";
$status_filter = "";
$date_from = "";
$date_to = "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'reported_desc';
$where_conditions = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $where_conditions[] = "(r.item_name LIKE :search OR r.description LIKE :search OR r.lost_location LIKE :search)";
    $params[':search'] = '%' . $search_query . '%';
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = $_GET['category'];
    $where_conditions[] = "r.category = :category";
    $params[':category'] = $category_filter;
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = $_GET['status'];
    $where_conditions[] = "r.status = :status";
    $params[':status'] = $status_filter;
}

// Date range filtering (by lost_date)
if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
  $date_from = $_GET['date_from'];
  $where_conditions[] = "r.lost_date >= :date_from";
  $params[':date_from'] = $date_from;
}
if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
  $date_to = $_GET['date_to'];
  $where_conditions[] = "r.lost_date <= :date_to";
  $params[':date_to'] = $date_to;
}

// Build the WHERE clause
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// ✅ Sorting
$order_by = "r.reported_at DESC";
switch ($sort) {
  case 'reported_asc':
    $order_by = 'r.reported_at ASC';
    break;
  case 'lost_desc':
    $order_by = 'r.lost_date DESC';
    break;
  case 'lost_asc':
    $order_by = 'r.lost_date ASC';
    break;
  case 'reported_desc':
  default:
    $order_by = 'r.reported_at DESC';
}

// ✅ Fetch reported items + reporter info (JOIN with user_details)
$query = "SELECT r.*, u.username, u.full_name, u.contact_number
      FROM reported_items r
      JOIN user_details u ON r.user_id = u.id
      $where_clause
      ORDER BY $order_by";

$stmt = $conn->prepare($query);

// Bind parameters if any
if (!empty($params)) {
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique categories for filter dropdown
$category_query = "SELECT DISTINCT category FROM reported_items WHERE category IS NOT NULL AND category != ''";
$category_stmt = $conn->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Missing Items - Lost & Found</title>
    <style>
    :root { 
      --yellow: #fbb117; 
      --yellow-light: #fdd017; 
      --yellow-dark: #d69e00; 
    }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }

        nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: absolute;
      top: 0;
      width: 100%;
      padding: 10px 20px;
      z-index: 1000;
    }

    nav a img {
      height: 50px;
    }

    nav ul {
      list-style-type: none;
      margin: 0;
      padding: 0;
      display: flex;
    }

    nav li {
      margin-left: 20px;
    }

    nav li a {
      display: block;
      color: black;
      text-align: center;
      padding: 10px 14px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    nav li a:hover {
      background-color: #ffffff33;
      border-radius: 4px;
    }

    nav li a.active {
      background-color: white;
      color: black;
      border-radius: 4px;
    }

    .nav-left {
      display: flex;
      align-items: center;
    }

    .nav-right {
      display: flex;
      align-items: center;
      margin-right: 100px;
    }

    .nav-right li {
      margin-left: 20px;
    }

    .nav-right li a {
      border: 1px solid black;
      padding: 8px 16px;
      border-radius: 4px;
    }

    .nav-right li a:hover {
      background-color: white;
    }

    .user-greeting a {
      pointer-events: auto !important;
      cursor: pointer !important;
      position: relative;
      z-index: 1000;
      text-decoration: none;
      color: inherit;
      transition: opacity 0.3s ease, transform 0.2s ease;
    }

    .user-greeting a:hover {
      opacity: 0.8;
      transform: scale(1.02);
      text-decoration: underline;
    }

    .user-greeting a:active {
      transform: scale(0.98);
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #ffffff33;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
      z-index: 1;
    }

    .dropdown-content a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }

    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    .cover-photo {
      width: 100vw;
      max-width: 100vw;
      min-width: 0;
      display: block;
    }

    .border-wrapper {
      padding: 15px;
      border: 15px solid;
      border-image: repeating-linear-gradient(
        45deg,
        black 0 10px,
        yellow 10px 20px
      ) 30;
      background-color: white;
    }

    .main-content {
      padding: 40px 20px;
      background-color: white;
    }

    .content-wrapper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      gap: 40px;

    }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px;
        }
        
    .header {
      background: linear-gradient(to bottom right, var(--yellow-light), var(--yellow));
      color: #111;
            text-align: center;
            padding: 40px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
    .search-filter-section {
      background: white;
      padding: 25px;
      border-radius: 14px;
      margin-bottom: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      border: 1px solid #eee;
    }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }
        
  .form-group { flex: 1; min-width: 200px; }
  .form-group.narrow { flex: 0.6; min-width: 160px; }
  .form-group.actions { flex: 0 0 auto; display: flex; gap: 10px; align-items: flex-end; }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--yellow);
      box-shadow: 0 0 0 3px rgba(251, 177, 23, 0.15);
    }
        
    .btn {
      background: linear-gradient(to bottom right, var(--yellow-light), var(--yellow));
      color: #111;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
      transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
        }
        
        .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 18px rgba(251, 177, 23, 0.25);
        }
        
    .btn-clear {
      background: transparent;
      color: var(--yellow);
      border: 2px solid var(--yellow);
    }

    .results-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 6px 4px 20px;
      color: #555;
    }
    .filter-chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .chip {
      background: #fff7e0;
      color: #7a5200;
      border: 1px solid #ffe29c;
      padding: 6px 10px;
      border-radius: 16px;
      font-size: 12px;
    }
    .chip a { color: inherit; text-decoration: none; margin-left: 6px; }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .item-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .item-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .item-image {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="50" y="50" text-anchor="middle" dy=".3em" fill="%23999" font-size="12">No Image</text></svg>');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .item-content {
            padding: 20px;
        }
        
        .item-name {
            font-size: 1.3em;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
    .item-category {
      display: inline-block;
      background: rgba(251, 177, 23, 0.15);
      color: #7a5200;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .item-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .item-details {
            border-top: 1px solid #f0f0f0;
            padding-top: 15px;
            font-size: 0.9em;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            color: #777;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-found {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-claimed {
            background: #d4edda;
            color: #155724;
        }
        
        .no-items {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-items img {
            width: 120px;
            height: 120px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
    .back-btn {
      position: fixed;
      top: 20px;
      left: 20px;
      background: linear-gradient(to bottom right, var(--yellow-light), var(--yellow));
      color: #111;
      padding: 10px 15px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 600;
      transition: box-shadow 0.3s;
    }
        
    .back-btn:hover {
      box-shadow: 0 6px 14px rgba(251, 177, 23, 0.3);
    }
        
    @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .form-group {
                min-width: 100%;
            }

      .form-group.actions { align-items: center; }
            
            .items-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
    <div class="nav-left">
      <a href="home.php"><img src="logo2.png" alt="Company logo" /></a>
      <ul>
        <li><a class="active" href="home.php">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="javascript:void(0)" onclick="triggerAction('reportModal')">Report Items</a></li>
        <li><a href="javascript:void(0)" onclick="triggerAction('searchSection')">Claim Missing</a></li>
        <li><a href="contactus.php">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <span class="user-greeting" id="userGreeting">
        <a href="profile.php">Welcome, <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>!</a>
      </span>
      <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="profile.php">My Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="dropdown">
            <a href="javascript:void(0)">Login</a>
            <div class="dropdown-content">
              <a href="loginuser.php">User</a>
              <a href="loginadmin.php">Admin</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="javascript:void(0)">Sign Up</a>
            <div class="dropdown-content">
              <a href="signupuser.php">User</a>
              <a href="signupadmin.php">Admin</a>
            </div>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>
  <section class="border-wrapper">
      <div class="content-wrapper">
      <div class="container">
        <div class="header">
            <h1>🔍 Claim Missing Items</h1>
            <p>Browse through reported lost items and help reunite them with their owners</p>
        </div>
        
        <div class="search-filter-section">
      <form method="GET" class="filter-form" id="filtersForm">
        <div class="form-group">
                    <label for="search">Search Items</label>
          <input type="text" id="search" name="search" placeholder="Search by name, description, or location..." value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat_row): ?>
                            <option value="<?php echo htmlspecialchars($cat_row['category']); ?>" <?php echo ($category_filter == $cat_row['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat_row['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
        <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="found" <?php echo ($status_filter == 'found') ? 'selected' : ''; ?>>Found</option>
                        <option value="claimed" <?php echo ($status_filter == 'claimed') ? 'selected' : ''; ?>>Claimed</option>
                    </select>
                </div>
        <div class="form-group narrow">
          <label for="date_from">Lost From</label>
          <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
        </div>
        <div class="form-group narrow">
          <label for="date_to">Lost To</label>
          <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
        </div>
        <div class="form-group narrow">
          <label for="sort">Sort</label>
          <select id="sort" name="sort">
            <option value="reported_desc" <?php echo ($sort=='reported_desc')?'selected':''; ?>>Newest reports</option>
            <option value="reported_asc" <?php echo ($sort=='reported_asc')?'selected':''; ?>>Oldest reports</option>
            <option value="lost_desc" <?php echo ($sort=='lost_desc')?'selected':''; ?>>Lost date: newest</option>
            <option value="lost_asc" <?php echo ($sort=='lost_asc')?'selected':''; ?>>Lost date: oldest</option>
          </select>
        </div>
        <div class="form-group actions">
          <button type="submit" class="btn">🔍 Search</button>
          <a href="claimmissing.php" class="btn btn-clear">Clear</a>
        </div>
            </form>
        </div>

    <div class="results-bar">
      <div><strong><?php echo count($result); ?></strong> result(s)</div>
      <div class="filter-chips">
        <?php if ($search_query !== ''): ?>
          <span class="chip">Search: <?php echo htmlspecialchars($search_query); ?> <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_diff_key($_GET, ['search'=>1])); ?>">×</a></span>
        <?php endif; ?>
        <?php if ($category_filter !== ''): ?>
          <span class="chip">Category: <?php echo htmlspecialchars($category_filter); ?> <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_diff_key($_GET, ['category'=>1])); ?>">×</a></span>
        <?php endif; ?>
        <?php if ($status_filter !== ''): ?>
          <span class="chip">Status: <?php echo htmlspecialchars($status_filter); ?> <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_diff_key($_GET, ['status'=>1])); ?>">×</a></span>
        <?php endif; ?>
        <?php if ($date_from !== ''): ?>
          <span class="chip">From: <?php echo htmlspecialchars($date_from); ?> <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_diff_key($_GET, ['date_from'=>1])); ?>">×</a></span>
        <?php endif; ?>
        <?php if ($date_to !== ''): ?>
          <span class="chip">To: <?php echo htmlspecialchars($date_to); ?> <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>?<?php echo http_build_query(array_diff_key($_GET, ['date_to'=>1])); ?>">×</a></span>
        <?php endif; ?>
      </div>
    </div>
        
        <?php if (count($result) > 0): ?>
            <div class="items-grid">
        <?php foreach($result as $row): ?>
          <div class="item-card">
            <?php
              $imagePathRaw = isset($row['image_path']) ? trim((string)$row['image_path']) : '';
              $webPath = '';
              if ($imagePathRaw !== '') {
                if (preg_match('~^(?:https?:)?//|^data:~i', $imagePathRaw)) {
                  // Absolute URL or data URI
                  $webPath = $imagePathRaw;
                } else {
                  // Normalize to project-relative URL
                  $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                  if ($imagePathRaw[0] === '/') {
                    // Convert root-relative '/uploads/...' to '/<project>/uploads/...'
                    $webPath = $basePath . $imagePathRaw;
                  } else {
                    // Already relative like 'uploads/...'
                    $webPath = $basePath . '/' . $imagePathRaw;
                  }
                }
              }

              // Build filesystem path for existence check when possible
              $hasImage = false;
              if ($webPath !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $webPath)) {
                $absWeb = ($webPath[0] === '/') ? $webPath : ('/' . $webPath);
                $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . str_replace('/', DIRECTORY_SEPARATOR, $absWeb);
                $hasImage = file_exists($fsPath);
              } elseif ($webPath !== '') {
                // Can't verify remote/data URIs, assume present
                $hasImage = true;
              }
            ?>
            <?php if ($hasImage): ?>
              <div class="item-image" style="background-image:url('<?php echo htmlspecialchars($webPath); ?>');"></div>
            <?php else: ?>
              <div class="item-image"></div>
            <?php endif; ?>
                        
                        <div class="item-content">
                            <div class="item-name"><?php echo htmlspecialchars($row['item_name']); ?></div>
                            
                            <?php if (!empty($row['category'])): ?>
                                <span class="item-category"><?php echo htmlspecialchars($row['category']); ?></span>
                            <?php endif; ?>
                            
                            <div class="item-description">
                                <?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 150))); ?>
                                <?php if (strlen($row['description']) > 150) echo '...'; ?>
                            </div>
                            
                            <div class="item-details">
                                <div class="detail-row">
                                    <span class="detail-label">Lost Date:</span>
                                    <span class="detail-value"><?php echo date('M d, Y', strtotime($row['lost_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['lost_location']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Reported:</span>
                                    <span class="detail-value"><?php echo date('M d, Y g:i A', strtotime($row['reported_at'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Reported by:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($row['username']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Contact Number:</span>
                                    <span class="detail-value">
                                        <?php echo !empty($row['contact_number']) ? htmlspecialchars($row['contact_number']) : 'Not available'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-items">
                <div style="font-size:4em; margin-bottom:20px;">📭</div>
                <h3>No items found</h3>
                <p>There are currently no reported items matching your search criteria.</p>
                <?php if (!empty($search_query) || !empty($category_filter) || !empty($status_filter)): ?>
                    <br>
                    <a href="claimmissing.php" class="btn">View All Items</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
      </div>
      </div>
    </section>
    <script>
    // Auto-submit filters and debounced search
    (function(){
      const form = document.getElementById('filtersForm');
      if (!form) return;

      const debounce = (fn, wait=400) => {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, args), wait); };
      };

      const search = document.getElementById('search');
      const category = document.getElementById('category');
      const status = document.getElementById('status');
      const dateFrom = document.getElementById('date_from');
      const dateTo = document.getElementById('date_to');
      const sort = document.getElementById('sort');

      if (search) {
        search.addEventListener('input', debounce(() => form.requestSubmit()));
      }
      [category, status, dateFrom, dateTo, sort].forEach(el => {
        if (el) el.addEventListener('change', () => form.requestSubmit());
      });
    })();

    // Trigger action based on login status
    function triggerAction(targetId) {
      const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
      if (!isLoggedIn) {
        window.location.href = 'loginuser.php';
      } else if (targetId === 'searchSection') {
        // This page already shows the search section.
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        openModal(targetId);
      }
    }

  </script>
</body>
</html>

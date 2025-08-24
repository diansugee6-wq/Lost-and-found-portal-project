<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: loginadmin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lostfounddb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle item actions
$message = '';
$error = '';

// Handle status change for lost items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_lost_status'])) {
    $itemId = $conn->real_escape_string($_POST['item_id']);
    $newStatus = $conn->real_escape_string($_POST['new_status']);
    
    $sql = "UPDATE lost_items SET status = '$newStatus' WHERE id = $itemId";
    
    if ($conn->query($sql)) {
        $message = "Lost item status updated successfully!";
    } else {
        $error = "Error updating lost item status: " . $conn->error;
    }
}

// Handle status change for found items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_found_status'])) {
    $itemId = $conn->real_escape_string($_POST['item_id']);
    $newStatus = $conn->real_escape_string($_POST['new_status']);
    
    $sql = "UPDATE found_items SET status = '$newStatus' WHERE id = $itemId";
    
    if ($conn->query($sql)) {
        $message = "Found item status updated successfully!";
    } else {
        $error = "Error updating found item status: " . $conn->error;
    }
}

// Handle item deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_item'])) {
    $itemId = $conn->real_escape_string($_POST['item_id']);
    $itemType = $conn->real_escape_string($_POST['item_type']);
    
    if ($itemType == 'lost') {
        $sql = "DELETE FROM lost_items WHERE id = $itemId";
    } else {
        $sql = "DELETE FROM found_items WHERE id = $itemId";
    }
    
    if ($conn->query($sql)) {
        $message = "Item deleted successfully!";
    } else {
        $error = "Error deleting item: " . $conn->error;
    }
}

// Handle search
$searchTerm = "";
$lostItems = [];
$foundItems = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = $conn->real_escape_string($_POST['search']);
    $lostSql = "SELECT li.*, ud.full_name, ud.contact_number 
                FROM lost_items li 
                JOIN user_details ud ON li.user_id = ud.id 
                WHERE li.item_name LIKE '%$searchTerm%' OR 
                      li.category LIKE '%$searchTerm%' OR 
                      li.description LIKE '%$searchTerm%' OR 
                      li.location LIKE '%$searchTerm%' OR
                      ud.full_name LIKE '%$searchTerm%'
                ORDER BY li.created_at DESC";
    
    $foundSql = "SELECT fi.*, ud.full_name, ud.contact_number 
                 FROM found_items fi 
                 JOIN user_details ud ON fi.user_id = ud.id 
                 WHERE fi.item_name LIKE '%$searchTerm%' OR 
                       fi.category LIKE '%$searchTerm%' OR 
                       fi.description LIKE '%$searchTerm%' OR 
                       fi.location LIKE '%$searchTerm%' OR
                       ud.full_name LIKE '%$searchTerm%'
                 ORDER BY fi.created_at DESC";
} else {
    $lostSql = "SELECT li.*, ud.full_name, ud.contact_number 
                FROM lost_items li 
                JOIN user_details ud ON li.user_id = ud.id 
                ORDER BY li.created_at DESC";
    
    $foundSql = "SELECT fi.*, ud.full_name, ud.contact_number 
                 FROM found_items fi 
                 JOIN user_details ud ON fi.user_id = ud.id 
                 ORDER BY fi.created_at DESC";
}

// Get lost items
$result = $conn->query($lostSql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $lostItems[] = $row;
    }
}

// Get found items
$result = $conn->query($foundSql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $foundItems[] = $row;
    }
}

// Get statistics
$totalLost = 0;
$pendingLost = 0;
$approvedLost = 0;
$foundLost = 0;

$totalFound = 0;
$pendingFound = 0;
$approvedFound = 0;
$returnedFound = 0;

// Lost items stats
$sql = "SELECT COUNT(*) as total FROM lost_items";
$result = $conn->query($sql);
if ($result) {
    $totalLost = $result->fetch_assoc()['total'];
}

$sql = "SELECT COUNT(*) as count FROM lost_items WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result) {
    $pendingLost = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM lost_items WHERE status = 'approved'";
$result = $conn->query($sql);
if ($result) {
    $approvedLost = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM lost_items WHERE status = 'found'";
$result = $conn->query($sql);
if ($result) {
    $foundLost = $result->fetch_assoc()['count'];
}

// Found items stats
$sql = "SELECT COUNT(*) as total FROM found_items";
$result = $conn->query($sql);
if ($result) {
    $totalFound = $result->fetch_assoc()['total'];
}

$sql = "SELECT COUNT(*) as count FROM found_items WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result) {
    $pendingFound = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM found_items WHERE status = 'approved'";
$result = $conn->query($sql);
if ($result) {
    $approvedFound = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM found_items WHERE status = 'returned'";
$result = $conn->query($sql);
if ($result) {
    $returnedFound = $result->fetch_assoc()['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Reports - Lost&Found.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --yellow: #fbb117;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --dark-gray: #333;
            --green: #4CAF50;
            --red: #f44336;
            --blue: #2196F3;
            --orange: #FF9800;
            --purple: #9C27B0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--gray);
            color: var(--black1);
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .navigation {
            width: 280px;
            background: var(--yellow);
            border-left: 10px solid var(--yellow);
            overflow: hidden;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .navigation ul {
            margin: 0;
            padding: 0;
        }

        .navigation ul li {
            list-style: none;
        }

        .navigation ul li a {
            display: flex;
            align-items: center;
            padding: 20px 15px;
            color: var(--black1);
            text-decoration: none;
            transition: 0.3s;
            border-top-left-radius: 40px;
            border-bottom-left-radius: 40px;
        }

        .navigation ul li a:hover {
            background-color: #ffffff33;
        }

        .navigation ul li a.active {
            background-color: var(--white);
        }

        .navigation ul li a .icon {
            min-width: 50px;
            text-align: center;
            font-size: 20px;
        }

        .navigation ul li a .title {
            font-size: 16px;
            font-weight: 500;
        }

        .logo-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 30px 15px;
            color: var(--black1);
            text-decoration: none;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .logo-link .icon img {
            display: block;
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 25px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--yellow);
        }

        .dashboard-title {
            font-size: 32px;
            color: var(--black1);
            font-weight: 700;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .search-input {
            padding: 12px 18px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search-btn {
            background: var(--yellow);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #f9a602;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--black1);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: var(--black1);
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 16px;
        }

        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tab {
            padding: 12px 24px;
            cursor: pointer;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            border-bottom: 3px solid var(--yellow);
            color: var(--black1);
        }

        .tab:hover {
            background-color: #f9f9f9;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Item Table */
        .table-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .table-header {
            padding: 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--black1);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--yellow);
            color: var(--black1);
        }

        .btn-success {
            background: var(--green);
            color: white;
        }

        .btn-danger {
            background: var(--red);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .item-table th {
            background-color: var(--yellow);
            color: var(--black1);
            text-align: left;
            padding: 18px;
            font-weight: 600;
            font-size: 16px;
        }

        .item-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        .item-table tr:hover {
            background-color: #f9f9f9;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .status-found {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .status-returned {
            background-color: #e2e3e5;
            color: #383d41;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .action-btn {
            background: var(--yellow);
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 8px;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: #f9a602;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: var(--blue);
            color: white;
        }

        .btn-delete {
            background: var(--red);
            color: white;
        }

        .btn-status {
            background: var(--orange);
            color: white;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        .user-name {
            font-weight: 600;
        }

        .user-contact {
            color: #666;
            font-size: 13px;
        }

        /* Message alerts */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 1100px) {
            .navigation {
                width: 230px;
            }
            .main-content {
                margin-left: 230px;
            }
        }

        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }
            .navigation {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .search-container {
                width: 100%;
            }
            .search-input {
                width: 100%;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .logout-btn {
            background: #ff4d4d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: 10px;
        }

        .logout-btn:hover {
            background: #ff3333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--black1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="navigation">
            <a href="#" class="logo-link">
                <span class="icon">
                    <img src="logo2.png" alt="Logo">
                </span>
                <span class="logo-text">Lost & Found Admin</span>
            </a>
            <ul>
                <li>
                    <a href="admindashboard.php">
                        <span class="icon"><i class="fas fa-chart-line"></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="usermanagement.php">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span class="title">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="title">Item Reports</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class="fas fa-search"></i></span>
                        <span class="title">Item Claims</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon"><i class="fas fa-cog"></i></span>
                        <span class="title">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="home.html">
                        <span class="icon"><i class="fas fa-home"></i></span>
                        <span class="title">Back to Home</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Item Reports Management</h1>
                <div style="display: flex; align-items: center;">
                    <div class="user-welcome">
                        <div class="user-avatar">A</div>
                        <div>Admin User</div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="search-container">
                <input type="text" class="search-input" name="search" placeholder="Search items by name, category, or location..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-search"></i></div>
                    <div class="stat-number"><?php echo $totalLost + $totalFound; ?></div>
                    <div class="stat-label">Total Reports</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                    <div class="stat-number"><?php echo $pendingLost + $pendingFound; ?></div>
                    <div class="stat-label">Pending Reviews</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?php echo $approvedLost + $approvedFound; ?></div>
                    <div class="stat-label">Approved Items</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-box-open"></i></div>
                    <div class="stat-number"><?php echo $foundLost + $returnedFound; ?></div>
                    <div class="stat-label">Resolved Cases</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" data-tab="lost">Lost Items (<?php echo $totalLost; ?>)</div>
                <div class="tab" data-tab="found">Found Items (<?php echo $totalFound; ?>)</div>
            </div>

            <!-- Lost Items Tab -->
            <div class="tab-content active" id="lost-tab">
                <div class="table-container fade-in">
                    <div class="table-header">
                        <div class="table-title">Lost Items Reports</div>
                        <div class="action-buttons">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Location</th>
                                <th>Lost Date</th>
                                <th>Reported By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($lostItems)): ?>
                                <?php foreach ($lostItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                                        <td><?php echo htmlspecialchars($item['location']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($item['lost_date'])); ?></td>
                                        <td class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($item['full_name']); ?></span>
                                            <span class="user-contact"><?php echo htmlspecialchars($item['contact_number']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo $item['status']; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="action-btn btn-edit view-item" data-id="<?php echo $item['id']; ?>" data-type="lost">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $item['status'] == 'approved' ? 'pending' : 'approved'; ?>">
                                                <button type="submit" name="update_lost_status" class="action-btn btn-status">
                                                    <i class="fas fa-<?php echo $item['status'] == 'approved' ? 'times' : 'check'; ?>"></i> 
                                                    <?php echo $item['status'] == 'approved' ? 'Unapprove' : 'Approve'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="item_type" value="lost">
                                                <button type="submit" name="delete_item" class="action-btn btn-delete" 
                                                        onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 30px;">
                                        No lost items found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Found Items Tab -->
            <div class="tab-content" id="found-tab">
                <div class="table-container fade-in">
                    <div class="table-header">
                        <div class="table-title">Found Items Reports</div>
                        <div class="action-buttons">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    <table class="item-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Location</th>
                                <th>Found Date</th>
                                <th>Reported By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($foundItems)): ?>
                                <?php foreach ($foundItems as $item): ?>
                                    <tr>
                                        <td><?php echo $item['id']; ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                                        <td><?php echo htmlspecialchars($item['location']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($item['found_date'])); ?></td>
                                        <td class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($item['full_name']); ?></span>
                                            <span class="user-contact"><?php echo htmlspecialchars($item['contact_number']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-<?php echo $item['status']; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="action-btn btn-edit view-item" data-id="<?php echo $item['id']; ?>" data-type="found">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="new_status" value="<?php echo $item['status'] == 'approved' ? 'pending' : 'approved'; ?>">
                                                <button type="submit" name="update_found_status" class="action-btn btn-status">
                                                    <i class="fas fa-<?php echo $item['status'] == 'approved' ? 'times' : 'check'; ?>"></i> 
                                                    <?php echo $item['status'] == 'approved' ? 'Unapprove' : 'Approve'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="item_type" value="found">
                                                <button type="submit" name="delete_item" class="action-btn btn-delete" 
                                                        onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 30px;">
                                        No found items found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Item Modal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Item Details</h2>
            <div id="modalContent">
                <!-- Item details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the selected tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // View item functionality
        document.querySelectorAll('.view-item').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const itemType = this.getAttribute('data-type');
                
                // In a real implementation, you would fetch item details via AJAX
                // For now, we'll show a simple alert
                alert('View details for ' + itemType + ' item ID: ' + itemId + 
                      '\nThis would show complete item details in a modal.');
            });
        });

        // Modal functionality
        const modal = document.getElementById('itemModal');
        const span = document.getElementsByClassName('close')[0];

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Add some interactive effects
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.08)';
            });
        });
    </script>
</body>
</html>
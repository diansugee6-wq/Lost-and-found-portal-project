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

// Handle claim actions
$message = '';
$error = '';

// Handle claim status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_claim_status'])) {
    $claimId = $conn->real_escape_string($_POST['claim_id']);
    $newStatus = $conn->real_escape_string($_POST['new_status']);
    $adminNotes = $conn->real_escape_string($_POST['admin_notes'] ?? '');
    
    $resolvedDate = $newStatus == 'approved' || $newStatus == 'rejected' ? ', resolved_date = CURDATE()' : '';
    
    $sql = "UPDATE item_claims SET status = '$newStatus', admin_notes = '$adminNotes' $resolvedDate WHERE id = $claimId";
    
    if ($conn->query($sql)) {
        $message = "Claim status updated successfully!";
        
        // If claim is approved, update the item status as well
        if ($newStatus == 'approved') {
            // Get the item details
            $itemSql = "SELECT item_id, item_type FROM item_claims WHERE id = $claimId";
            $itemResult = $conn->query($itemSql);
            
            if ($itemResult && $itemResult->num_rows > 0) {
                $itemData = $itemResult->fetch_assoc();
                $itemId = $itemData['item_id'];
                $itemType = $itemData['item_type'];
                
                // Update item status based on type
                if ($itemType == 'lost') {
                    $updateItemSql = "UPDATE lost_items SET status = 'found' WHERE id = $itemId";
                } else {
                    $updateItemSql = "UPDATE found_items SET status = 'returned' WHERE id = $itemId";
                }
                
                $conn->query($updateItemSql);
            }
        }
    } else {
        $error = "Error updating claim status: " . $conn->error;
    }
}

// Handle search
$searchTerm = "";
$claims = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = $conn->real_escape_string($_POST['search']);
    $sql = "SELECT ic.*, ud.full_name, ud.email, ud.contact_number, 
                   CASE 
                     WHEN ic.item_type = 'lost' THEN li.item_name 
                     ELSE fi.item_name 
                   END as item_name,
                   CASE 
                     WHEN ic.item_type = 'lost' THEN li.description 
                     ELSE fi.description 
                   END as item_description
            FROM item_claims ic
            JOIN user_details ud ON ic.user_id = ud.id
            LEFT JOIN lost_items li ON ic.item_type = 'lost' AND ic.item_id = li.id
            LEFT JOIN found_items fi ON ic.item_type = 'found' AND ic.item_id = fi.id
            WHERE ud.full_name LIKE '%$searchTerm%' OR 
                  ud.email LIKE '%$searchTerm%' OR 
                  ic.claim_description LIKE '%$searchTerm%' OR
                  ic.status LIKE '%$searchTerm%'
            ORDER BY ic.created_at DESC";
} else {
    $sql = "SELECT ic.*, ud.full_name, ud.email, ud.contact_number, 
                   CASE 
                     WHEN ic.item_type = 'lost' THEN li.item_name 
                     ELSE fi.item_name 
                   END as item_name,
                   CASE 
                     WHEN ic.item_type = 'lost' THEN li.description 
                     ELSE fi.description 
                   END as item_description
            FROM item_claims ic
            JOIN user_details ud ON ic.user_id = ud.id
            LEFT JOIN lost_items li ON ic.item_type = 'lost' AND ic.item_id = li.id
            LEFT JOIN found_items fi ON ic.item_type = 'found' AND ic.item_id = fi.id
            ORDER BY ic.created_at DESC";
}

// Get claims
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $claims[] = $row;
    }
}

// Get statistics
$totalClaims = 0;
$pendingClaims = 0;
$underReviewClaims = 0;
$approvedClaims = 0;
$rejectedClaims = 0;

$sql = "SELECT COUNT(*) as total FROM item_claims";
$result = $conn->query($sql);
if ($result) {
    $totalClaims = $result->fetch_assoc()['total'];
}

$sql = "SELECT COUNT(*) as count FROM item_claims WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result) {
    $pendingClaims = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM item_claims WHERE status = 'under_review'";
$result = $conn->query($sql);
if ($result) {
    $underReviewClaims = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM item_claims WHERE status = 'approved'";
$result = $conn->query($sql);
if ($result) {
    $approvedClaims = $result->fetch_assoc()['count'];
}

$sql = "SELECT COUNT(*) as count FROM item_claims WHERE status = 'rejected'";
$result = $conn->query($sql);
if ($result) {
    $rejectedClaims = $result->fetch_assoc()['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Claims - Lost&Found.com</title>
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

        /* Claims Table */
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

        .claims-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .claims-table th {
            background-color: var(--yellow);
            color: var(--black1);
            text-align: left;
            padding: 18px;
            font-weight: 600;
            font-size: 16px;
        }

        .claims-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        .claims-table tr:hover {
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

        .status-under_review {
            background-color: #d1ecf1;
            color: #0c5460;
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

        .btn-view {
            background: var(--blue);
            color: white;
        }

        .btn-approve {
            background: var(--green);
            color: white;
        }

        .btn-reject {
            background: var(--red);
            color: white;
        }

        .btn-review {
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

        .item-info {
            display: flex;
            flex-direction: column;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .item-desc {
            color: #666;
            font-size: 13px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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

        /* Modal Styles */
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
            margin: 5% auto;
            padding: 25px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close {
            color: #aaa;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--black1);
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 24px;
            color: var(--black1);
            margin-bottom: 10px;
        }

        .modal-section {
            margin-bottom: 20px;
        }

        .modal-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--black1);
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 12px;
        }

        .detail-label {
            width: 150px;
            font-weight: 600;
            color: #666;
        }

        .detail-value {
            flex: 1;
        }

        .proof-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
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
                    <a href="itemreports.php">
                        <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="title">Item Reports</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
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
                <h1 class="dashboard-title">Item Claims Management</h1>
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
                <input type="text" class="search-input" name="search" placeholder="Search claims by user, item, or status..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-number"><?php echo $totalClaims; ?></div>
                    <div class="stat-label">Total Claims</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number"><?php echo $pendingClaims; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-search"></i></div>
                    <div class="stat-number"><?php echo $underReviewClaims; ?></div>
                    <div class="stat-label">Under Review</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?php echo $approvedClaims; ?></div>
                    <div class="stat-label">Approved Claims</div>
                </div>
            </div>

            <!-- Claims Table -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <div class="table-title">Item Claims</div>
                    <div class="action-buttons">
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <table class="claims-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Claimant</th>
                            <th>Item Details</th>
                            <th>Item Type</th>
                            <th>Claim Description</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($claims)): ?>
                            <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td><?php echo $claim['id']; ?></td>
                                    <td class="user-info">
                                        <span class="user-name"><?php echo htmlspecialchars($claim['full_name']); ?></span>
                                        <span class="user-contact"><?php echo htmlspecialchars($claim['email']); ?></span>
                                        <span class="user-contact"><?php echo htmlspecialchars($claim['contact_number']); ?></span>
                                    </td>
                                    <td class="item-info">
                                        <span class="item-name"><?php echo htmlspecialchars($claim['item_name']); ?></span>
                                        <span class="item-desc"><?php echo htmlspecialchars(substr($claim['item_description'], 0, 100)); ?>...</span>
                                    </td>
                                    <td><?php echo ucfirst($claim['item_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($claim['claim_description'], 0, 50)); ?>...</td>
                                    <td><?php echo date('M j, Y', strtotime($claim['created_at'])); ?></td>
                                    <td>
                                        <span class="status-<?php echo $claim['status']; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($claim['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-view view-claim" data-id="<?php echo $claim['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($claim['status'] == 'pending'): ?>
                                            <button class="action-btn btn-review review-claim" data-id="<?php echo $claim['id']; ?>">
                                                <i class="fas fa-search"></i> Review
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px;">
                                    No item claims found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Claim Detail Modal -->
    <div id="claimModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title">Claim Details</h2>
            </div>
            <div id="modalContent">
                <!-- Claim details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // View claim functionality
        document.querySelectorAll('.view-claim').forEach(button => {
            button.addEventListener('click', function() {
                const claimId = this.getAttribute('data-id');
                
                // Show loading state
                document.getElementById('modalContent').innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: var(--yellow);"></i>
                        <p>Loading claim details...</p>
                    </div>
                `;
                
                // Show modal
                document.getElementById('claimModal').style.display = 'block';
                
                // In a real implementation, you would fetch claim details via AJAX
                // For this example, we'll simulate a delay and show sample data
                setTimeout(() => {
                    document.getElementById('modalContent').innerHTML = `
                        <div class="modal-section">
                            <h3 class="modal-section-title">Claim Information</h3>
                            <div class="detail-row">
                                <div class="detail-label">Claim ID:</div>
                                <div class="detail-value">${claimId}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value"><span class="status-pending">Pending</span></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Submitted:</div>
                                <div class="detail-value">${new Date().toLocaleDateString()}</div>
                            </div>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title">Claimant Details</h3>
                            <div class="detail-row">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value">John Doe</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Email:</div>
                                <div class="detail-value">john.doe@example.com</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Phone:</div>
                                <div class="detail-value">+1 (555) 123-4567</div>
                            </div>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title">Item Details</h3>
                            <div class="detail-row">
                                <div class="detail-label">Item Type:</div>
                                <div class="detail-value">Lost</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Item Name:</div>
                                <div class="detail-value">iPhone 12 Pro</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Description:</div>
                                <div class="detail-value">Black iPhone 12 Pro with a blue case. Lost somewhere in the city center.</div>
                            </div>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title">Claim Description</h3>
                            <div class="detail-value">
                                <p>This is my iPhone that I lost last week. I can provide the IMEI number and purchase receipt.</p>
                            </div>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title">Proof Details</h3>
                            <div class="proof-details">
                                <p>IMEI: 123456789012345, Purchase date: 2023-01-15</p>
                            </div>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title">Update Claim Status</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="claim_id" value="${claimId}">
                                <div class="form-group">
                                    <label for="new_status">Status:</label>
                                    <select name="new_status" id="new_status" required>
                                        <option value="pending">Pending</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="admin_notes">Admin Notes:</label>
                                    <textarea name="admin_notes" id="admin_notes" placeholder="Add notes about this claim..."></textarea>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-danger" onclick="document.getElementById('claimModal').style.display='none'">Cancel</button>
                                    <button type="submit" name="update_claim_status" class="btn btn-success">Update Status</button>
                                </div>
                            </form>
                        </div>
                    `;
                }, 800);
            });
        });

        // Review claim functionality
        document.querySelectorAll('.review-claim').forEach(button => {
            button.addEventListener('click', function() {
                const claimId = this.getAttribute('data-id');
                alert('Review claim ID: ' + claimId + '\nThis would open a review form in a complete implementation.');
            });
        });

        // Modal functionality
        const modal = document.getElementById('claimModal');
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
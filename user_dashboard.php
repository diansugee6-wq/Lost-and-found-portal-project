<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: loginuser.html");
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

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM user_details WHERE id = $user_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "User not found!";
    header("Location: loginuser.html");
    exit();
}

// Get user's lost items
$lost_items_sql = "SELECT * FROM lost_items WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$lost_items_result = $conn->query($lost_items_sql);
$lost_items = [];
if ($lost_items_result && $lost_items_result->num_rows > 0) {
    while($row = $lost_items_result->fetch_assoc()) {
        $lost_items[] = $row;
    }
}

// Get user's found items
$found_items_sql = "SELECT * FROM found_items WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$found_items_result = $conn->query($found_items_sql);
$found_items = [];
if ($found_items_result && $found_items_result->num_rows > 0) {
    while($row = $found_items_result->fetch_assoc()) {
        $found_items[] = $row;
    }
}

// Get user's claims
$claims_sql = "SELECT * FROM item_claims WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$claims_result = $conn->query($claims_sql);
$claims = [];
if ($claims_result && $claims_result->num_rows > 0) {
    while($row = $claims_result->fetch_assoc()) {
        $claims[] = $row;
    }
}

// Get statistics
$total_lost_items = 0;
$total_found_items = 0;
$total_claims = 0;

$sql = "SELECT COUNT(*) as total FROM lost_items WHERE user_id = $user_id";
$result = $conn->query($sql);
if ($result) {
    $total_lost_items = $result->fetch_assoc()['total'];
}

$sql = "SELECT COUNT(*) as total FROM found_items WHERE user_id = $user_id";
$result = $conn->query($sql);
if ($result) {
    $total_found_items = $result->fetch_assoc()['total'];
}

$sql = "SELECT COUNT(*) as total FROM item_claims WHERE user_id = $user_id";
$result = $conn->query($sql);
if ($result) {
    $total_claims = $result->fetch_assoc()['total'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Lost&Found.com</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --yellow: #fbb117;
            --white: #fff;
            --gray: #f5f5f5;
            --black1: #222;
            --dark-gray: #333;
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

        /* Tables */
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

        .view-all-btn {
            background: var(--yellow);
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--black1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .data-table th {
            background-color: var(--yellow);
            color: var(--black1);
            text-align: left;
            padding: 18px;
            font-weight: 600;
            font-size: 16px;
        }

        .data-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-found {
            background-color: #cce5ff;
            color: #004085;
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
                <span class="logo-text">Lost & Found Portal</span>
            </a>
            <ul>
                <li>
                    <a href="user_dashboard.php" class="active">
                        <span class="icon"><i class="fas fa-chart-line"></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="report_lost_item.php">
                        <span class="icon"><i class="fas fa-plus-circle"></i></span>
                        <span class="title">Report Lost Item</span>
                    </a>
                </li>
                <li>
                    <a href="report_found_item.php">
                        <span class="icon"><i class="fas fa-search-plus"></i></span>
                        <span class="title">Report Found Item</span>
                    </a>
                </li>
                <li>
                    <a href="my_items.php">
                        <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="title">My Items</span>
                    </a>
                </li>
                <li>
                    <a href="my_claims.php">
                        <span class="icon"><i class="fas fa-handshake"></i></span>
                        <span class="title">My Claims</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <span class="icon"><i class="fas fa-user-cog"></i></span>
                        <span class="title">Profile Settings</span>
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
                <h1 class="dashboard-title">User Dashboard</h1>
                <div style="display: flex; align-items: center;">
                    <div class="user-welcome">
                        <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                        <div>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-search"></i></div>
                    <div class="stat-number"><?php echo $total_lost_items; ?></div>
                    <div class="stat-label">Lost Items Reported</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number"><?php echo $total_found_items; ?></div>
                    <div class="stat-label">Found Items Reported</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-handshake"></i></div>
                    <div class="stat-number"><?php echo $total_claims; ?></div>
                    <div class="stat-label">Claims Submitted</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-number"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>

            <!-- Recent Lost Items -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <div class="table-title">Recently Reported Lost Items</div>
                    <a href="my_lost_items.php" class="view-all-btn">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Lost Date</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lost_items)): ?>
                            <?php foreach ($lost_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['lost_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px;">
                                    You haven't reported any lost items yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Found Items -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <div class="table-title">Recently Reported Found Items</div>
                    <a href="my_found_items.php" class="view-all-btn">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Found Date</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($found_items)): ?>
                            <?php foreach ($found_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($item['found_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px;">
                                    You haven't reported any found items yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Claims -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <div class="table-title">Recent Claims</div>
                    <a href="my_claims.php" class="view-all-btn">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Type</th>
                            <th>Item ID</th>
                            <th>Submitted On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($claims)): ?>
                            <?php foreach ($claims as $claim): ?>
                                <tr>
                                    <td><?php echo ucfirst($claim['item_type']); ?></td>
                                    <td>#<?php echo $claim['item_id']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($claim['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $claim['status']; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($claim['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px;">
                                    You haven't submitted any claims yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
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
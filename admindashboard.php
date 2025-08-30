<?php
session_start();



// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    // If not logged in, or not an admin
    header("Location: loginuser.php");
    exit();
}



require_once 'configure.php';

// Create DB connection using your Database class
$database = new Database();
$conn = $database->getConnection();

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $userId = intval($_POST['user_id']);
    $sql = "DELETE FROM user_details WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([':id' => $userId])) {
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: admindashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting user.";
    }
}

// Handle search
$searchTerm = "";
$users = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchTerm = trim($_POST['search']);
    $sql = "SELECT * FROM user_details 
            WHERE user_role = 0 AND (
                full_name LIKE :term OR 
                username LIKE :term OR 
                email LIKE :term OR 
                nic LIKE :term OR 
                contact_number LIKE :term
            )
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':term' => "%$searchTerm%"]);
} else {
    $sql = "SELECT * FROM user_details WHERE user_role = 0 ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get statistics
$totalUsers = 0;
$todayUsers = 0;
$weekUsers = 0;

$stmt = $conn->query("SELECT COUNT(*) as total FROM user_details");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as today FROM user_details WHERE DATE(created_at) = CURDATE()");
$todayUsers = $stmt->fetch(PDO::FETCH_ASSOC)['today'];

$stmt = $conn->query("SELECT COUNT(*) as week FROM user_details WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())");
$weekUsers = $stmt->fetch(PDO::FETCH_ASSOC)['week'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost&Found.com</title>
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

        /* User Table */
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

        .export-btn {
            background: var(--yellow);
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        .user-table th {
            background-color: var(--yellow);
            color: var(--black1);
            text-align: left;
            padding: 18px;
            font-weight: 600;
            font-size: 16px;
        }

        .user-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #eee;
        }

        .user-table tr:hover {
            background-color: #f9f9f9;
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

        .delete-btn {
            background: #ff4d4d;
            color: white;
        }

        .delete-btn:hover {
            background: #ff3333;
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
                <li><a href="admindashboard.php" class="active"><span class="icon"><i class="fas fa-chart-line"></i></span>
               
                <!--<span class="title">Dashboard</span></a></li>
                <li><a href="usermanagement.php"><span class="icon"><i class="fas fa-users"></i></span>-->
                
                <span class="title">User Management</span></a></li>
                <li><a href="admin_reporteditems.php"><span class="icon"><i class="fas fa-clipboard-list"></i></span>
                
                <span class="title">Item Reports</span></a></li>
                <li><a href="admin_claimeditems.php"><span class="icon"><i class="fas fa-search"></i></span><span class="title">Item Claims</span></a></li>

                <!--<li><a href="settings.php"><span class="icon"><i class="fas fa-cog"></i></span><span class="title">Settings</span></a></li>-->

                <li><a href="home.php"><span class="icon"><i class="fas fa-home"></i></span><span class="title">Back to Home</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <div style="display: flex; align-items: center;">
                    <div class="user-welcome">
                        <div class="user-avatar">A</div>
                        <div>Admin User</div>
                    </div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> 
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> 
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Search -->
            <form method="POST" action="" class="search-container">
                <input type="text" class="search-input" name="search" placeholder="Search users..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
            </form>

            <!-- Stats -->
            <div class="stats-container">
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">Total Users</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-number"><?php echo $todayUsers; ?></div><div class="stat-label">New Today</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
                    <div class="stat-number"><?php echo $weekUsers; ?></div><div class="stat-label">New This Week</div></div>
                <div class="stat-card"><div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="stat-number"><?php echo $totalUsers > 0 ? '100%' : '0%'; ?></div><div class="stat-label">Active Users</div></div>
            </div>

            <!-- User Table -->
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Registered Users</div>
                    <button class="export-btn"><i class="fas fa-download"></i> Export Data</button>
                </div>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Full Name</th><th>Username</th><th>Email</th>
                            <th>NIC</th><th>Contact Number</th><th>Registration Date</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nic']); ?></td>
                                    <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="action-btn view-btn" data-userid="<?php echo $user['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="action-btn delete-btn" 
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center; padding:20px;">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

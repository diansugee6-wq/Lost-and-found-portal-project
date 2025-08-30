<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginuser.php");
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
    $user = [];
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

        /* User Details */
        .details-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .details-header {
            padding: 20px;
            background-color: var(--yellow);
            color: var(--black1);
            font-size: 20px;
            font-weight: 600;
        }

        .details-body {
            padding: 25px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            font-weight: 600;
            width: 200px;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        /* Recent Activity */
        .activity-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .activity-header {
            padding: 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            font-size: 20px;
            font-weight: 600;
        }

        .activity-list {
            padding: 0;
            list-style: none;
        }

        .activity-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--black1);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 12px;
            color: #888;
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
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
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
                <span class="logo-text">Lost & Found User</span>
            </a>
            <ul>
                <li>
                    <a href="userdashboard.php" class="active">
                        <span class="icon"><i class="fas fa-home"></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="reportitem.php">
                        <span class="icon"><i class="fas fa-plus-circle"></i></span>
                        <span class="title">Report Item</span>
                    </a>
                </li>
                <li>
                    <a href="claimmissing.php">
                        <span class="icon"><i class="fas fa-search"></i></span>
                        <span class="title">Claim Item</span>
                    </a>
                </li>
                <li>
                    <a href="myitems.php">
                        <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="title">My Items</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
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
                <h1 class="dashboard-title">User Dashboard</h1>
                <div style="display: flex; align-items: center;">
                    <div class="user-welcome">
                        <div class="user-avatar"><?php echo isset($_SESSION['username']) ? substr($_SESSION['username'], 0, 1) : 'U'; ?></div>
                        <div>Welcome, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?></div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-search"></i></div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Items Reported</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">Items Found</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-number">2</div>
                    <div class="stat-label">Pending Claims</div>
                </div>
                <div class="stat-card fade-in">
                    <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Days Registered</div>
                </div>
            </div>

            <!-- User Details -->
            <div class="details-container fade-in">
                <div class="details-header">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                <div class="details-body">
                    <div class="detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div class="detail-value"><?php echo isset($user['full_name']) ? $user['full_name'] : 'Not available'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username:</div>
                        <div class="detail-value"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Not available'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value"><?php echo isset($user['email']) ? $user['email'] : 'Not available'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">NIC:</div>
                        <div class="detail-value"><?php echo isset($user['nic']) ? $user['nic'] : 'Not available'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Contact Number:</div>
                        <div class="detail-value"><?php echo isset($user['contact_number']) ? $user['contact_number'] : 'Not available'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value">
                            <?php 
                                if (isset($user['address_line1'])) {
                                    echo $user['address_line1'];
                                    if (isset($user['address_line2']) && !empty($user['address_line2'])) {
                                        echo ', ' . $user['address_line2'];
                                    }
                                } else {
                                    echo 'Not available';
                                }
                            ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Member Since:</div>
                        <div class="detail-value"><?php echo isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : 'Not available'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-container fade-in">
                <div class="activity-header">
                    <i class="fas fa-history"></i> Recent Activity
                </div>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Reported a lost wallet</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Claim for iPhone 12 Pro approved</div>
                            <div class="activity-time">1 day ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Searched for "black backpack"</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Created your account</div>
                            <div class="activity-time">5 days ago</div>
                        </div>
                    </li>
                </ul>
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
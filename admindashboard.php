<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost&Found.com</title>
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
            font-family: Arial, sans-serif;
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
            width: 300px;
            background: var(--yellow);
            border-left: 10px solid var(--yellow);
            overflow: hidden;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
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
            padding: 25px 15px;
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
            font-size: 18px;
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
        }

        .logo-link .icon img {
            display: block;
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .logo-text {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 300px;
            padding: 20px;
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
            font-size: 28px;
            color: var(--black1);
        }

        .search-container {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 250px;
        }

        .search-btn {
            background: var(--yellow);
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .search-btn:hover {
            background: #fdd017;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: var(--black1);
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        /* User Table */
        .table-container {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .user-table th {
            background-color: var(--yellow);
            color: var(--black1);
            text-align: left;
            padding: 15px;
            font-weight: bold;
        }

        .user-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        .user-table tr:hover {
            background-color: #f9f9f9;
        }

        .action-btn {
            background: var(--yellow);
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 14px;
        }

        .action-btn:hover {
            background: #fdd017;
        }

        .delete-btn {
            background: #ff4d4d;
            color: white;
        }

        .delete-btn:hover {
            background: #ff3333;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination-btn {
            padding: 8px 15px;
            background: var(--yellow);
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination-btn.active {
            background: var(--black1);
            color: var(--white);
        }

        .pagination-btn:hover:not(.active) {
            background: #fdd017;
        }

        /* Message alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: bold;
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
        @media (max-width: 992px) {
            .navigation {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 768px) {
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
                <span class="logo-text">Lost & Found inc.</span>
            </a>
            <ul>
                <li>
                    <a href="#" class="active">
                        <span class="icon">📊</span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon">👤</span>
                        <span class="title">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon">📝</span>
                        <span class="title">Item Reports</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="icon">🔍</span>
                        <span class="title">Item Claims</span>
                    </a>
                </li>
                <li>
                    <a href="settings.html">
                        <span class="icon">⚙️</span>
                        <span class="title">Settings</span>
                    </a>
                </li>
                <li>
                    <a href="home.html">
                        <span class="icon">🏠</span>
                        <span class="title">Back to Home</span>
                    </a>
                </li>
                <li>
                    <a href="logout.html">
                        <span class="icon">🚪</span>
                        <span class="title">Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Admin Dashboard</h1>
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search users..." id="searchInput">
                    <button class="search-btn" id="searchBtn">Search</button>
                </div>
            </div>

            <?php
            // Database connection and data fetching
            $servername = "localhost";
            $username = "root"; // Your database username
            $password = ""; // Your database password
            $dbname = "lostfounddb"; // Your database name

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                echo '<div class="alert alert-error">Connection failed: ' . $conn->connect_error . '</div>';
                $connectionError = true;
            } else {
                // Get total user count
                $sql = "SELECT COUNT(*) as total FROM user_details";
                $result = $conn->query($sql);
                
                if ($result) {
                    $totalUsers = $result->fetch_assoc()['total'];
                    
                    // Get today's registrations
                    $sql = "SELECT COUNT(*) as today FROM user_details WHERE DATE(created_at) = CURDATE()";
                    $result = $conn->query($sql);
                    $todayUsers = $result ? $result->fetch_assoc()['today'] : 0;
                    
                    // Get this week's registrations
                    $sql = "SELECT COUNT(*) as week FROM user_details WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
                    $result = $conn->query($sql);
                    $weekUsers = $result ? $result->fetch_assoc()['week'] : 0;
                    
                    // Get all user details
                    $sql = "SELECT * FROM user_details ORDER BY created_at DESC";
                    $result = $conn->query($sql);
                } else {
                    echo '<div class="alert alert-error">Error executing query: ' . $conn->error . '</div>';
                    $totalUsers = 0;
                    $todayUsers = 0;
                    $weekUsers = 0;
                }
            ?>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $todayUsers; ?></div>
                    <div class="stat-label">Registered Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $weekUsers; ?></div>
                    <div class="stat-label">Registered This Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Pending Verifications</div>
                </div>
            </div>

            <!-- User Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>NIC</th>
                            <th>Contact Number</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['full_name']}</td>
                                    <td>" . ($row['username'] ? $row['username'] : '-') . "</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['nic']}</td>
                                    <td>{$row['contact_number']}</td>
                                    <td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>
                                    <td>
                                        <button class='action-btn'>View</button>
                                        <button class='action-btn delete-btn'>Delete</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' style='text-align: center;'>No users found</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <?php } ?>

            <!-- Pagination -->
            <div class="pagination">
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn">Next</button>
            </div>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.user-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Allow pressing Enter to search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchBtn').click();
            }
        });

        // Delete button functionality (for demonstration)
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const userName = row.querySelector('td:nth-child(2)').textContent;
                
                if (confirm(`Are you sure you want to delete ${userName}?`)) {
                    // In a real application, you would send an AJAX request to delete the user
                    row.style.display = 'none';
                    alert(`${userName} has been deleted (simulated). In a real application, this would remove the user from the database.`);
                }
            });
        });
    </script>
</body>
</html>

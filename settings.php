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

// Handle settings update
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $settingKey = substr($key, 8); // Remove 'setting_' prefix
            $settingValue = $conn->real_escape_string($value);
            
            $sql = "UPDATE system_settings SET setting_value = '$settingValue', updated_at = NOW() WHERE setting_key = '$settingKey'";
            
            if ($conn->query($sql)) {
                if ($conn->affected_rows > 0) {
                    $successCount++;
                }
            } else {
                $errorCount++;
            }
        }
    }
    
    if ($errorCount > 0) {
        $error = "Error updating some settings. $successCount settings updated successfully, $errorCount failed.";
    } else {
        $message = "Settings updated successfully!";
        
        // Log this activity
        $adminId = $_SESSION['admin_id'];
        $logSql = "INSERT INTO admin_activity_logs (admin_id, activity_type, description, ip_address) 
                   VALUES ($adminId, 'settings_update', 'Updated system settings', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($logSql);
    }
}

// Handle backup request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_backup'])) {
    // In a real implementation, this would create a database backup
    // For this example, we'll just show a message
    $message = "Database backup created successfully!";
    
    // Log this activity
    $adminId = $_SESSION['admin_id'];
    $logSql = "INSERT INTO admin_activity_logs (admin_id, activity_type, description, ip_address) 
               VALUES ($adminId, 'backup', 'Created system backup', '{$_SERVER['REMOTE_ADDR']}')";
    $conn->query($logSql);
}

// Handle cache clear request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clear_cache'])) {
    // In a real implementation, this would clear application cache
    // For this example, we'll just show a message
    $message = "System cache cleared successfully!";
    
    // Log this activity
    $adminId = $_SESSION['admin_id'];
    $logSql = "INSERT INTO admin_activity_logs (admin_id, activity_type, description, ip_address) 
               VALUES ($adminId, 'cache_clear', 'Cleared system cache', '{$_SERVER['REMOTE_ADDR']}')";
    $conn->query($logSql);
}

// Get all settings grouped by category
$settings = [];
$sql = "SELECT * FROM system_settings ORDER BY setting_group, display_order";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $settings[$row['setting_group']][] = $row;
    }
}

// Get recent activity logs
$activityLogs = [];
$logSql = "SELECT al.*, ud.username 
           FROM admin_activity_logs al 
           LEFT JOIN user_details ud ON al.admin_id = ud.id 
           ORDER BY al.created_at DESC 
           LIMIT 10";
$logResult = $conn->query($logSql);

if ($logResult && $logResult->num_rows > 0) {
    while($row = $logResult->fetch_assoc()) {
        $activityLogs[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Lost&Found.com</title>
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

        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            flex-wrap: wrap;
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

        /* Settings Form */
        .settings-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .settings-group {
            padding: 25px;
            border-bottom: 1px solid #eee;
        }

        .settings-group:last-child {
            border-bottom: none;
        }

        .settings-group-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--black1);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--yellow);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-group-icon {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--yellow);
            border-radius: 50%;
        }

        .setting-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .setting-item:last-child {
            margin-bottom: 0;
        }

        .setting-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--black1);
        }

        .setting-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
        }

        .setting-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .setting-input:focus {
            border-color: var(--yellow);
            outline: none;
            box-shadow: 0 0 0 3px rgba(251, 177, 23, 0.2);
        }

        .setting-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background-color: white;
        }

        .setting-textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            resize: vertical;
        }

        .setting-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            font-weight: normal;
        }

        .form-actions {
            padding: 20px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
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

        .btn-info {
            background: var(--blue);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* System Actions */
        .system-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .action-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--black1);
        }

        .action-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--black1);
        }

        .action-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Activity Logs */
        .activity-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .activity-header {
            padding: 20px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--black1);
        }

        .activity-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .activity-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--yellow);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-description {
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .activity-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
        }

        .activity-admin {
            font-weight: 600;
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
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
            .system-actions {
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
                    <a href="itemclaims.php">
                        <span class="icon"><i class="fas fa-search"></i></span>
                        <span class="title">Item Claims</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
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
                <h1 class="dashboard-title">System Settings</h1>
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

            <!-- System Actions -->
            <div class="system-actions">
                <div class="action-card fade-in">
                    <div class="action-icon"><i class="fas fa-database"></i></div>
                    <div class="action-title">Database Backup</div>
                    <div class="action-description">Create a backup of your database for safety and recovery</div>
                    <form method="POST" action="">
                        <button type="submit" name="create_backup" class="btn btn-info">
                            <i class="fas fa-download"></i> Create Backup
                        </button>
                    </form>
                </div>
                
                <div class="action-card fade-in">
                    <div class="action-icon"><i class="fas fa-broom"></i></div>
                    <div class="action-title">Clear Cache</div>
                    <div class="action-description">Clear temporary system cache to improve performance</div>
                    <form method="POST" action="">
                        <button type="submit" name="clear_cache" class="btn btn-info">
                            <i class="fas fa-broom"></i> Clear Cache
                        </button>
                    </form>
                </div>
                
                <div class="action-card fade-in">
                    <div class="action-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="action-title">System Info</div>
                    <div class="action-description">View system information and server environment details</div>
                    <button type="button" class="btn btn-info" onclick="alert('System information would be displayed here in a complete implementation.')">
                        <i class="fas fa-info"></i> View Info
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" data-tab="general">General Settings</div>
                <div class="tab" data-tab="notifications">Notifications</div>
                <div class="tab" data-tab="moderation">Moderation</div>
                <div class="tab" data-tab="appearance">Appearance</div>
                <div class="tab" data-tab="email">Email Settings</div>
                <div class="tab" data-tab="activity">Activity Log</div>
            </div>

            <!-- Settings Form -->
            <form method="POST" action="">
                <input type="hidden" name="update_settings" value="1">
                
                <!-- General Settings Tab -->
                <div class="tab-content active" id="general-tab">
                    <div class="settings-container fade-in">
                        <div class="settings-group">
                            <h2 class="settings-group-title">
                                <span class="settings-group-icon"><i class="fas fa-globe"></i></span>
                                General Settings
                            </h2>
                            
                            <?php if (isset($settings['general'])): ?>
                                <?php foreach ($settings['general'] as $setting): ?>
                                    <div class="setting-item">
                                        <label class="setting-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                            <?php echo $setting['label']; ?>
                                        </label>
                                        
                                        <?php if (!empty($setting['description'])): ?>
                                            <div class="setting-description"><?php echo $setting['description']; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['setting_type'] == 'text'): ?>
                                            <input type="text" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php elseif ($setting['setting_type'] == 'textarea'): ?>
                                            <textarea id="setting_<?php echo $setting['setting_key']; ?>" 
                                                      name="setting_<?php echo $setting['setting_key']; ?>" 
                                                      class="setting-textarea"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                        <?php elseif ($setting['setting_type'] == 'number'): ?>
                                            <input type="number" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php elseif ($setting['setting_type'] == 'boolean'): ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                                       name="setting_<?php echo $setting['setting_key']; ?>" 
                                                       class="setting-checkbox" 
                                                       value="1" 
                                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                Enable
                                            </label>
                                        <?php elseif ($setting['setting_type'] == 'select'): ?>
                                            <select id="setting_<?php echo $setting['setting_key']; ?>" 
                                                    name="setting_<?php echo $setting['setting_key']; ?>" 
                                                    class="setting-select">
                                                <?php 
                                                $options = json_decode($setting['options'], true);
                                                if (is_array($options)) {
                                                    foreach ($options as $option) {
                                                        echo '<option value="' . htmlspecialchars($option) . '" ' . 
                                                             ($setting['setting_value'] == $option ? 'selected' : '') . '>' . 
                                                             htmlspecialchars($option) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Tab -->
                <div class="tab-content" id="notifications-tab">
                    <div class="settings-container fade-in">
                        <div class="settings-group">
                            <h2 class="settings-group-title">
                                <span class="settings-group-icon"><i class="fas fa-bell"></i></span>
                                Notification Settings
                            </h2>
                            
                            <?php if (isset($settings['notifications'])): ?>
                                <?php foreach ($settings['notifications'] as $setting): ?>
                                    <div class="setting-item">
                                        <label class="setting-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                            <?php echo $setting['label']; ?>
                                        </label>
                                        
                                        <?php if (!empty($setting['description'])): ?>
                                            <div class="setting-description"><?php echo $setting['description']; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['setting_type'] == 'boolean'): ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                                       name="setting_<?php echo $setting['setting_key']; ?>" 
                                                       class="setting-checkbox" 
                                                       value="1" 
                                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                Enable
                                            </label>
                                        <?php else: ?>
                                            <input type="text" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="setting-item">
                                    <p>No notification settings found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Moderation Tab -->
                <div class="tab-content" id="moderation-tab">
                    <div class="settings-container fade-in">
                        <div class="settings-group">
                            <h2 class="settings-group-title">
                                <span class="settings-group-icon"><i class="fas fa-shield-alt"></i></span>
                                Moderation Settings
                            </h2>
                            
                            <?php if (isset($settings['moderation'])): ?>
                                <?php foreach ($settings['moderation'] as $setting): ?>
                                    <div class="setting-item">
                                        <label class="setting-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                            <?php echo $setting['label']; ?>
                                        </label>
                                        
                                        <?php if (!empty($setting['description'])): ?>
                                            <div class="setting-description"><?php echo $setting['description']; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['setting_type'] == 'boolean'): ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                                       name="setting_<?php echo $setting['setting_key']; ?>" 
                                                       class="setting-checkbox" 
                                                       value="1" 
                                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                Enable
                                            </label>
                                        <?php else: ?>
                                            <input type="text" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="setting-item">
                                    <p>No moderation settings found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Tab -->
                <div class="tab-content" id="appearance-tab">
                    <div class="settings-container fade-in">
                        <div class="settings-group">
                            <h2 class="settings-group-title">
                                <span class="settings-group-icon"><i class="fas fa-paint-brush"></i></span>
                                Appearance Settings
                            </h2>
                            
                            <?php if (isset($settings['appearance'])): ?>
                                <?php foreach ($settings['appearance'] as $setting): ?>
                                    <div class="setting-item">
                                        <label class="setting-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                            <?php echo $setting['label']; ?>
                                        </label>
                                        
                                        <?php if (!empty($setting['description'])): ?>
                                            <div class="setting-description"><?php echo $setting['description']; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['setting_type'] == 'select'): ?>
                                            <select id="setting_<?php echo $setting['setting_key']; ?>" 
                                                    name="setting_<?php echo $setting['setting_key']; ?>" 
                                                    class="setting-select">
                                                <?php 
                                                $options = json_decode($setting['options'], true);
                                                if (is_array($options)) {
                                                    foreach ($options as $option) {
                                                        echo '<option value="' . htmlspecialchars($option) . '" ' . 
                                                             ($setting['setting_value'] == $option ? 'selected' : '') . '>' . 
                                                             htmlspecialchars($option) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="setting-item">
                                    <p>No appearance settings found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Email Tab -->
                <div class="tab-content" id="email-tab">
                    <div class="settings-container fade-in">
                        <div class="settings-group">
                            <h2 class="settings-group-title">
                                <span class="settings-group-icon"><i class="fas fa-envelope"></i></span>
                                Email Settings
                            </h2>
                            
                            <?php if (isset($settings['email'])): ?>
                                <?php foreach ($settings['email'] as $setting): ?>
                                    <div class="setting-item">
                                        <label class="setting-label" for="setting_<?php echo $setting['setting_key']; ?>">
                                            <?php echo $setting['label']; ?>
                                        </label>
                                        
                                        <?php if (!empty($setting['description'])): ?>
                                            <div class="setting-description"><?php echo $setting['description']; ?></div>
                                        <?php endif; ?>
                                        
                                        <?php if ($setting['setting_type'] == 'boolean'): ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" 
                                                       id="setting_<?php echo $setting['setting_key']; ?>" 
                                                       name="setting_<?php echo $setting['setting_key']; ?>" 
                                                       class="setting-checkbox" 
                                                       value="1" 
                                                       <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                                Enable
                                            </label>
                                        <?php else: ?>
                                            <input type="<?php echo $setting['setting_key'] == 'smtp_password' ? 'password' : 'text'; ?>" 
                                                   id="setting_<?php echo $setting['setting_key']; ?>" 
                                                   name="setting_<?php echo $setting['setting_key']; ?>" 
                                                   class="setting-input" 
                                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                                   <?php echo $setting['setting_key'] == 'smtp_password' ? 'placeholder="Leave blank to keep current password"' : ''; ?>>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="setting-item">
                                    <p>No email settings found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="reset" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reset Changes
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>

            <!-- Activity Log Tab -->
            <div class="tab-content" id="activity-tab">
                <div class="activity-container fade-in">
                    <div class="activity-header">
                        <div class="activity-title">Recent Activity Logs</div>
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Logs
                        </button>
                    </div>
                    
                    <ul class="activity-list">
                        <?php if (!empty($activityLogs)): ?>
                            <?php foreach ($activityLogs as $log): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-<?php 
                                            switch($log['activity_type']) {
                                                case 'login': echo 'sign-in-alt'; break;
                                                case 'settings_update': echo 'cog'; break;
                                                case 'user_management': echo 'user'; break;
                                                case 'item_approval': echo 'check-circle'; break;
                                                case 'backup': echo 'database'; break;
                                                case 'cache_clear': echo 'broom'; break;
                                                default: echo 'history';
                                            }
                                        ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-description">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="activity-admin"><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></span>
                                            <span class="activity-time"><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></span>
                                            <span class="activity-type"><?php echo str_replace('_', ' ', ucfirst($log['activity_type'])); ?></span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="activity-item">
                                <div class="activity-content">
                                    <div class="activity-description">
                                        No activity logs found.
                                    </div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
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

        // Add some interactive effects
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.08)';
            });
        });

        // Settings form validation
        const settingsForm = document.querySelector('form');
        settingsForm.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = this.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                    
                    // Add error message
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = 'red';
                        errorMsg.style.fontSize = '14px';
                        errorMsg.style.marginTop = '5px';
                        errorMsg.textContent = 'This field is required';
                        field.parentNode.appendChild(errorMsg);
                    }
                } else {
                    field.style.borderColor = '';
                    
                    // Remove error message
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                        field.nextElementSibling.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>
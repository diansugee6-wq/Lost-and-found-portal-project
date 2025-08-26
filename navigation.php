<?php
// ==========================================
// navigation.php - Reusable Navigation Menu
// ==========================================

// Make sure user is logged in before showing navigation
if (!isset($_SESSION['user_id'])) {
    header("Location: loginuser.php");
    exit();
}
?>

<nav style="background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <!-- Logo -->
    <a href="afterlogin_home.php">
        <img src="logo2.png" alt="Lost & Found Logo" style="height: 50px;">
    </a>
    
    <!-- User Info and Menu -->
    <div style="display: flex; align-items: center; gap: 2rem;">
        <!-- Welcome Message -->
        <span style="font-weight: 500; color: #333;">
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </span>
        
        <!-- Navigation Links -->
        <ul style="list-style: none; display: flex; gap: 1.5rem; margin: 0; padding: 0;">
            <li><a href="afterlogin_home.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Home</a></li>
            <li><a href="report_lost.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Report Lost Item</a></li>
            <li><a href="report_found.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Report Found Item</a></li>
            <li><a href="search_items.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Search Items</a></li>
            <li><a href="my_reports.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">My Reports</a></li>
            <li><a href="profile.php" style="text-decoration: none; color: #333; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Profile</a></li>
            <li><a href="logout.php" style="text-decoration: none; color: #fff; background: #dc3545; padding: 0.5rem 1rem; border-radius: 4px; transition: background 0.3s;">Logout</a></li>
        </ul>
    </div>
</nav>

<style>
/* Add hover effects */
nav ul li a:hover {
    background: #f8f9fa !important;
    color: #007bff !important;
}

nav ul li a[href="logout.php"]:hover {
    background: #c82333 !important;
    color: #fff !important;
}
</style>

// ==========================================
// HOW TO USE navigation.php in your other files
// ==========================================

// Example 1: afterlogin_home.php
<?php
session_start();
require_once 'auth_check.php';
checkLogin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home - Lost&Found.com</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; }
        .content { padding: 2rem; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>  <!-- Include navigation here -->
    
    <div class="content">
        <h1>Welcome to Lost & Found Portal</h1>
        <p>Hello <?php echo $_SESSION['username']; ?>! What would you like to do today?</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3>Report Lost Item</h3>
                <p>Lost something? Report it here and let others help you find it.</p>
                <a href="report_lost.php" style="background: #ffc107; color: #000; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Report Lost</a>
            </div>
            
            <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3>Report Found Item</h3>
                <p>Found something? Help reunite it with its owner.</p>
                <a href="report_found.php" style="background: #28a745; color: #fff; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Report Found</a>
            </div>
            
            <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3>Search Items</h3>
                <p>Browse through reported lost and found items.</p>
                <a href="search_items.php" style="background: #007bff; color: #fff; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Search Items</a>
            </div>
        </div>
    </div>
</body>
</html>

// ==========================================
// Example 2: report_lost.php (Your updated file)
// ==========================================
<?php
session_start();
require_once 'auth_check.php';
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Your existing form processing code here...
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Lost Item - Lost&Found.com</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f2f5; }
        .form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        /* Your existing CSS styles... */
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>  <!-- Include navigation here too -->
    
    <div class="form-container">
        <h2 class="form-title">Report a Lost Item</h2>
        <!-- Your existing form HTML... -->
    </div>
</body>
</html>

// ==========================================
// Example 3: Simple navigation for public pages
// public_navigation.php (for non-logged in users)
// ==========================================
<nav style="background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="index.php">
        <img src="logo2.png" alt="Lost & Found Logo" style="height: 50px;">
    </a>
    
    <ul style="list-style: none; display: flex; gap: 1.5rem; margin: 0; padding: 0;">
        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="loginuser.php" style="background: #007bff; color: #fff; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Login</a></li>
        <li><a href="register.php" style="background: #28a745; color: #fff; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px;">Register</a></li>
    </ul>
</nav>
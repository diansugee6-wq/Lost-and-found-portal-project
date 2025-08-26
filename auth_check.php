<?php
// ==========================================
// 1. auth_check.php (Create this file first)
// Include this at the top of every protected page
// ==========================================

session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        // User is not logged in, redirect to login page
        header("Location: loginuser.php");
        exit();
    }
    
    // Optional: Check if session is expired (set session timeout)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Session expired after 30 minutes of inactivity
        session_unset();
        session_destroy();
        header("Location: loginuser.php?msg=session_expired");
        exit();
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}
?>
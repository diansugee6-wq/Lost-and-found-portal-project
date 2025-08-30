<?php
require_once 'configure.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $database = new Database();
    $db = $database->getConnection();

    $sql = "SELECT * FROM user_details WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // Use correct DB column name 'role'
        $_SESSION['user_role'] = isset($user['role']) ? (int)$user['role'] : 0; // save role in session

        if (!empty($user['role']) && (int)$user['role'] === 1) {
            // Admin redirect
            echo "<script>alert('Welcome Admin!'); window.location.href='admindashboard.php';</script>";
        } else {
            // Normal user redirect
            echo "<script>alert('Login successful!'); window.location.href='home.php';</script>";
        }
        exit();
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lost&Found.com</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "poppins", sans-serif;
    }
    
    body {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: url(login.png) no-repeat center/cover;
    }

    body::before {
        content: "";
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.19);
        z-index: 0;
    }

    .wrapper {
        position: relative;
        z-index: 1;
        width: 420px;
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(20px);
        color: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    
    .wrapper h1 {
        font-size: 36px;
        text-align: center;
        margin-bottom: 30px;
    }
    
    .input-box {
        position: relative;
        width: 100%;
        height: 50px;
        margin: 30px 0;
    }
    
    .input-box input {
        width: 100%;
        height: 100%;
        background: transparent;
        border: none;
        outline: none;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 40px;
        font-size: 16px;
        color: #fff;
        padding: 20px 45px 20px 20px;
    }
    
    .input-box input::placeholder {
        color: #fff;
    }
    
    .input-box .icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        fill: white;
        opacity: 0.8;
        pointer-events: none;
    }
    
    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #fff;
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .remember-forgot input[type="checkbox"] {
        margin-right: 5px;
        cursor: pointer;
    }
    
    .remember-forgot a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }
    
    .remember-forgot a:hover {
        text-decoration: underline;
    }
    
    .btn {
        width: 100%;
        height: 45px;
        background: #fff;
        border: none;
        outline: none;
        border-radius: 40px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        cursor: pointer;
        font-size: 18px;
        color: #333;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #ddd;
    }
    
    .register-link {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #fff;
    }
    
    .register-link a {
        color: #000;
        text-decoration: none;
        font-weight: 600;
    }
    
    .register-link a:hover {
        text-decoration: underline;
    }
    
    nav {
        display: flex;
        align-items: center;
        justify-content: left;
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
</style>
</head>
<body>
    <nav>
        <a href="home.php">
            <img src="logo2.png" alt="logo">
        </a>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="reportitem.php">Report Items</a></li>
            <li><a href="claimmissing.php">Claim Missing</a></li>
            <li><a href="contactus.php">Contact Us</a></li>
        </ul>
    </nav>
    
    <div class="wrapper">
        <form action="" method="post">
            <h1>User Login</h1>
            
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
                    <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
            </div>
            
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <rect x="6" y="10" width="12" height="10" rx="2" ry="2" />
                  <path d="M9 10V7a3 3 0 016 0v3" />
                </svg>
            </div>
            
            <div class="remember-forgot">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>
            
            <button class="btn" type="submit">Login</button> 
            
            <div class="register-link">
                <p>Don't have an account? <a href="signupuser.php">SignUp</a></p>
            </div>
        </form>
    </div>
</body>
</html>

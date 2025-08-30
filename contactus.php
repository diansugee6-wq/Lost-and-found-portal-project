<!DOCTYPE html>
<html>
<head>
    <title>Lost&Found.com</title>
    
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: white;
            font-family: Arial, sans-serif;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Push left and right apart */
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

        .cover-photo-container {
            width: 100%;
            height: 450px;       
            overflow: hidden;    
        }

        .cover-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;  
            transform: scale(1.4); 
            transform-origin: center;
        }

        footer.advanced-footer {
            background-color: #333333;
            color: white;
            text-align: center;
            padding: 20px 0;
            overflow: visible;
        }

        footer.advanced-footer h3 {
            margin-top: 20px;
            margin-bottom: 10px;
        }

        footer.advanced-footer p {
            margin: 5px 0;
        }

        footer.advanced-footer a {
            color: #ffffff;
            text-decoration: none;
        }

        footer.advanced-footer a:hover {
            text-decoration: underline;
        }

        .bi {
            margin-right: 5px;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
        }

        .nav-right {
            display: flex;
            align-items: center;
            margin-right: 100px; 
        }

        .nav-right li {
            margin-left: 20px;
        }

        .nav-right li a {
            border: 1px solid black;
            padding: 8px 16px;
            border-radius: 4px;
        }

        .nav-right li a:hover {
            background-color: white;
        }
    
        li.dropdown {
            display: inline-block;
            position: relative; 
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ffffff33;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav-left">
      <a href="home.php">
        <img src="logo2.png" alt="Company logo" />
      </a>
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="reportitem.php">Report Items</a></li>
        <li><a href="claimmissing.php">Claim Missing</a></li>
        <li><a class="active" href="contactus.php">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <ul>
        <li class="dropdown">
          <a href="javascript:void(0)" class="dropbtn">Login</a>
          <div class="dropdown-content">
            <a href="loginuser.php">User</a>
            <a href="loginadmin.php">Admin</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="javascript:void(0)" class="dropbtn">Sign Up</a>
          <div class="dropdown-content">
            <a href="signupuser.php">User</a>
            <a href="signupadmin.php">Admin</a>
          </div>
        </li>
      </ul>
    </div>
</nav>

<div class="cover-photo-container">
    <img src="contact%20us.png" alt="Cover Photo" class="cover-photo">
</div>
    
<!-- <p>isbhiceonsvkevrvknr</p> -->

<footer class="advanced-footer">
    <div class="container">
      <h3>Contact Us</h3>
      <p>📧 <a href="mailto:info@lostandfound.com">info@lostandfound.com</a></p>
      <p>📞 <a href="tel:+94762639287">+94 76 263 9287</a></p>
      <h3>Quick Links</h3>
      <p>ℹ️ <a href="about.php">About Us</a></p>
      <p>📋 <a href="#how-it-works">How It Works</a></p>
      <h3>Follow Us On</h3>
      <p><a href="https://facebook.com">🌐 Facebook</a></p>
      <p><a href="https://instagram.com">📷 Instagram</a></p>
      <p><a href="https://linkedin.com">💼 LinkedIn</a></p>
      <p style="margin-top: 20px;">&copy; 2025 Lost&Found Inc. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>

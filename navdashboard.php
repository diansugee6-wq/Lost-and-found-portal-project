<?php

$servername = "localhost";
$username = "root";
$password = "";     // use your MySQL root password or empty string
$dbname = "lostfounddb";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost&Found.com</title>
    <style>
        :root {
            --white: #fff;
            --gray: #f5f5f5;
            --black: #333;
            --yellow: #fbb117;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--gray);
        }

        /* Topbar */
        .topbar {
            width: 100%;
            height: 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--gray);
            border-bottom: 1px solid #ddd;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            padding: 0 20px;
        }

        .toggle {
            font-size: 2rem;
            cursor: pointer;
        }

        .search {
            display: flex;
            align-items: center;
            background: #eee;
            padding: 5px 15px;
            border-radius: 20px;
        }

        .search input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 14px;
        }

        .search span {
            font-size: 1.2rem;
            color: var(--black);
            margin-left: 5px;
        }

        .user {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
        }

        .user img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Sidebar Navigation */
        .navigation {
            position: fixed;
            top: 80px;
            left: -300px;
            width: 300px;
            height: calc(100vh - 80px);
            background: var(--yellow);
            padding: 20px;
            transition: left 0.3s ease;
        }

        .navigation.active {
            left: 0;
        }

        .navigation ul {
            list-style: none;
            padding: 0;
        }

        .navigation ul li {
            margin: 20px 0;
        }

        .navigation ul li a {
            text-decoration: none;
            color: var(--black);
            font-size: 16px;
            font-weight: bold;
        }

        /* Main Section */
        .main {
            margin-top: 80px;
            padding: 20px;
            min-height: calc(100vh - 80px);
            transition: margin-left 0.3s ease;
        }

        .main.active {
            margin-left: 300px;
        }

        /* Cards */
        .cardbox {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
        }

        .cardbox .card {
            background: var(--gray);
            padding: 30px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .cardbox .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            background: var(--yellow);
        }

        .card .numbers {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--black);
        }

        .card .cardname {
            font-size: 0.9rem;
            color: #666;
        }

        .iconbox {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .iconbox img {
            width: 40px;
            height: 40px;
        }

        /* Details Section */
        .details {
            margin-top: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 7px 25px rgba(0,0,0,0.08);
        }

        .cardheader {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .cardheader h2 {
            font-size: 1.2rem;
            color: var(--black);
        }

        .btn {
            padding: 5px 15px;
            background: var(--yellow);
            color: var(--black);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            font-weight: bold;
            padding: 10px;
            border-bottom: 2px solid #ddd;
            text-align: left;
        }

        table tbody td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>

    <!-- Topbar -->
    <div class="topbar">
        <div class="toggle">☰</div>
        
        <div class="search">
            <input type="text" placeholder="Search here">
            <span>🔍</span>
        </div>
        
        <div class="user">
            <img src="navuser.png" alt="User">
        </div>
    </div>

    
    <!-- Main Content -->
    <div class="main" id="main">
        <div class="cardbox">
            <div class="card">
                <div>
                    <div class="numbers">1,504</div>
                    <div class="cardname">Daily Views</div>
                </div>
                <div class="iconbox">
                    <img src="view.jpg" alt="Views">
                </div>
            </div>

            <div class="card">
                <div>
                    <div class="numbers">80</div>
                    <div class="cardname">Lost items per day</div>
                </div>
                <div class="iconbox">
                    <img src="lostitem.png" alt="Lost Items">
                </div>
            </div>

            <div class="card">
                <div>
                    <div class="numbers">2,382</div>
                    <div class="cardname">Comments</div>
                </div>
                <div class="iconbox">
                    <img src="comments.png" alt="Comments">
                </div>
            </div>

            <div class="card">
                <div>
                    <div class="numbers">1,200+</div>
                    <div class="cardname">Resolved Items</div>
                </div>
                <div class="iconbox">
                    <img src="resolved.webp" alt="Resolved">
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="details">
            <div class="recentusers">
                <div class="cardheader">
                    <h2>Recent Transactions</h2>
                    <a href="#" class="btn">View all</a> 
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Reporter</th>
                            <th>Receiver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT reporter, receiver, status FROM transactions ORDER BY id DESC LIMIT 10";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['reporter']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['receiver']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No records found</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <script>
        const toggle = document.querySelector('.toggle');
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');

        toggle.onclick = () => {
            sidebar.classList.toggle('active');
            main.classList.toggle('active');
        }
    </script>
</body>
</html>
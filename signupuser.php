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
    <a href="Home.html">
        <img src="logo2.png" alt="logo">
    </a>
    <ul>
        <li><a href="Home.html">Home</a></li>
        <li><a href="About.html">About Us</a></li>
        <li><a href="reportitem.html">Report Items</a></li>
        <li><a href="claimmissing.html">Claim Missing</a></li>
        <li><a href="contactus.html">Contact Us</a></li>
    </ul>
</nav>
    
    <div class="wrapper">
        <form action="">
            <h1>User Sign up</h1>
            
            <div class="input-box">
                <input type="text" placeholder="Full Name" required>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
                    <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
            </div>

            <div class="input-box">
                    <input type="text" placeholder="Email" required>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
                    <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
            </div>

            <div class="input-box">
                    <input type="text" placeholder="NIC" required>
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
                    <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
            </div>

                <div class="input-box">
            <input type="text" placeholder="Address Line 1" required>
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
             <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
              <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
            </svg>
</div>
<div class="input-box">
    <input type="text" placeholder="Address Line 2">
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
        <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
    </svg>
</div>
<div class="input-box">
    <input type="tel" placeholder="contact number">
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" >
        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
        <path fill-rule="evenodd" d="M8 8a3 3 0 100-6 3 3 0 000 6z"/>
    </svg>
</div>
            
            <div class="input-box">
                <input type="password" placeholder="Password" required>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" >
                  <rect x="6" y="10" width="12" height="10" rx="2" ry="2" />
                  <path d="M9 10V7a3 3 0 016 0v3" />
                </svg>
            </div>
            
            <div class="input-box">
                <input type="password" placeholder="Confirm password" required>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" >
                  <rect x="6" y="10" width="12" height="10" rx="2" ry="2" />
                  <path d="M9 10V7a3 3 0 016 0v3" />
                </svg>
            </div>
            
            <button class="btn" type="submit">Sign Up</button> 
            
        </form>
    </div>
</body>
</html>

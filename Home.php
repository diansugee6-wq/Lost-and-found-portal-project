<!DOCTYPE html>
<html>
<head>
  <title>Lost&Found.com</title>
    
  <style>
    html {
      scroll-behavior: smooth;
  max-width: 100vw;
  overflow-x: hidden;
    }

    body {
      margin: 0;
      background-color: white;
      font-family: Arial, sans-serif;
    }

    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
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

    .cover-photo {
       width: 100vw;
       max-width: 100vw;
  min-width: 0;
  display: block;
      
    }

    .border-wrapper {
      padding: 15px;
      border: 15px solid;
      border-image: repeating-linear-gradient(
        45deg,
        black 0 10px,
        yellow 10px 20px
      ) 30;
      background-color: white;
    }

    
    .main-content {
      padding: 40px 20px;
      background-color: white;
    }

    .content-wrapper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      gap: 40px;
    }

    .text-buttons {
      flex: 1;
      text-align: left;
    }

    .text-buttons h2 {
      margin-bottom: 20px;
      font-size: 2em;
      color: #333;
    }

    .text-buttons p {
      font-size: 1.1em;
      line-height: 1.6;
      color: #555;
      margin-bottom: 15px;
      max-width: 600px;
    }

    .button-group {
      margin-top: 25px;
    }

    .cta-button {
      display: inline-block;
      margin-right: 15px;
      padding: 12px 30px;
      font-size: 1.2em;
      background-color: #fbb117;
      color: black;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(251, 177, 23, 0.5);
      transition: background-color 0.3s ease, transform 0.2s ease;
      cursor: pointer;
    }

    .cta-button:hover {
      background-color: #fdd017;
      transform: scale(1.05);
      box-shadow: 0 6px 12px rgba(253, 208, 23, 0.7);
    }

    .side-image {
      flex: 1;
      text-align: center;
    }

    .side-image img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      object-fit: cover;
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

    li.dropdown {
      display: inline-block;
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

    #how-it-works {
      background: linear-gradient(135deg, #f3e5ab);
      padding: 40px 20px;
    }

    .animated-header {
      font-size: 2rem;
      text-align: center;
      color: #343a40;
      margin-bottom: 30px;
    }

    .how-it-works-wrapper {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-around;
      gap: 20px;
    }

    .how-it-works-card {
      background: linear-gradient(to bottom right, #fdd017, #fbb117);
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
      flex: 1 1 250px;
      max-width: 300px;
      padding: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .how-it-works-card:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .how-it-works-icon {
      font-size: 3rem;
      color: white;
      margin-bottom: 10px;
    }

    .how-it-works-card h3 {
      font-size: 1.5rem;
      color: black;
      margin-bottom: 10px;
    }

    .how-it-works-card p {
      font-size: 1rem;
      color: black;
    }

    
    #poster-section {
      padding: 40px 20px;
    }

    .poster-border-wrapper {
      padding: 15px 40px;
      border: 4px solid black;
      background: linear-gradient(135deg, #f3e5ab);
      width: 100%;
      box-sizing: border-box;
      border-radius: 0;
      margin: 0;
    }

    .poster-container {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .poster-container img {
      width: 400px;
      height: auto;
      object-fit: cover;
      border-radius: 8px;
      display: block;
      transform: rotate(-10deg);
      transition: transform 0.3s ease;
    }

    .poster-text {
      flex: 1;
      font-family: 'Arial Black', Arial, sans-serif;
      font-size: 1.3em;
      line-height: 1.6;
      color: #333;
      margin-left: 20px;
    }
      
      .poster1-text {
          font-size: 1.1em;
      }

    @media (max-width: 768px) {
      .content-wrapper {
        flex-direction: column;
        text-align: center;
      }
        
      .text-buttons {
        max-width: 100%;
      }
      .button-group {
        margin-top: 20px;
      }
      .cta-button {
        margin-right: 10px;
        margin-bottom: 10px;
      }
      .side-image {
        margin-top: 30px;
      }
      .poster-container {
        flex-direction: column;
        text-align: center;
      }
      .poster-container img {
        width: 80%;
        margin: 0 auto;
        transform: rotate(0deg);
      }
    }
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a href="Home.html">
        <img src="logo2.png" alt="Company logo" />
      </a>
      <ul>
        <li><a class="active" href="Home.html">Home</a></li>
        <li><a href="About.html">About Us</a></li>
        <li><a href="reportitem.html">Report Items</a></li>
        <li><a href="claimmissing.html">Claim Missing</a></li>
        <li><a href="contactus.html">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <ul>
        <li class="dropdown">
          <a href="javascript:void(0)" class="dropbtn">Login</a>
          <div class="dropdown-content">
            <a href="loginuser.html">User</a>
            <a href="loginadmin.html">Admin</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="javascript:void(0)" class="dropbtn">Sign Up</a>
          <div class="dropdown-content">
            <a href="signupuser.html">User</a>
            <a href="signupadmin.html">Admin</a>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <img src="wtc-l-f.jpg" alt="Cover Photo" class="cover-photo" />

  <section class="border-wrapper">
    <div class="main-content">
      <div class="content-wrapper">
        <div class="text-buttons">
        <div class="poster1-text">
          <h2>Welcome to Lost & Found</h2>
          <p>
            Have you lost something or found an item that belongs to someone else? Our platform connects people
            to reunite lost items with their owners. Report a lost or found item today or browse our database
            to claim what's yours.
          </p>
          <p>
            Use the navigation above to report items, claim missing belongings, or contact us for assistance. We're
            here to help you every step of the way!
          </p>
          </div>
          <div class="button-group">
            <a href="reportitem.html" class="cta-button">Report an Item</a>
            <a href="claimmissing.html" class="cta-button claim-button">Claim Missing Item</a>
          </div>
        </div>
        <div class="side-image">
          <img src="logo2.png" width="60%" height="100%" alt="Lost & Found Illustration" />
            <p style="margin-top: 20px;">&copy;2025 Lost&Found Inc. All rights reserved.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="poster-section">
    <div class="poster-border-wrapper">
      <div class="poster-container">
        <img src="poster2.png" alt="Lost & Found Poster" />
        <div class="poster-text">
          <h1>Join the Lost & Found Network</h1>
          <p>
            Welcome to our Lost & Found Network — a dedicated platform designed to help you reconnect with your valuable belongings quickly and easily. Whether you’ve misplaced your wallet, keys, or any personal item, or you’ve found something that someone else has lost, our system is here to bridge that gap. By reporting lost or found items through our user-friendly portal, you become part of a community committed to helping one another. Our secure database stores detailed descriptions and images to increase the chances of identifying and returning items to their rightful owners. With real-time search features and administrative verification, we ensure that claims are legitimate and that the process is smooth and reliable. Join us today to be an active participant in making our community safer and more connected — because every item matters, and every reunion counts.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="how-it-works">
    <h2 class="animated-header">HOW IT WORKS !</h2>
    <div class="how-it-works-wrapper">
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="search.png" /></div>
        <h3>Step 1: Report</h3>
        <p>User reports a lost item using the portal's interface.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="db.png" /></div>
        <h3>Step 2: Database Entry</h3>
        <p>The item details are stored in the database securely.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="search2.png" /></div>
        <h3>Step 3: Search</h3>
        <p>User can search for their lost items using various filters.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="verify.png" /></div>
        <h3>Step 4: Verification</h3>
        <p>Admin verifies the claim and matches it with the found items.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="return.png" /></div>
        <h3>Step 5: Return</h3>
        <p>Verified items are returned to their rightful owner.</p>
      </div>
    </div>
  </section>

  <footer class="advanced-footer">
    <div class="container">
      <h3>Contact Us</h3>
      <p>📧 <a href="mailto:info@lostandfound.com">info@lostandfound.com</a></p>
      <p>📞 <a href="tel:+94762639287">+94 76 263 9287</a></p>
      <h3>Quick Links</h3>
      <p>ℹ️ <a href="About.html">About Us</a></p>
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

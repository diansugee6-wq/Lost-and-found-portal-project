<?php
session_start();

//if (!isset($_SESSION['user_id'])) {
    //header("Location: loginuser.php");
   // exit();
//}

require_once 'configure.php'; // Assumes this connects to the database

// Default guest name
$fullName = "Guest";

if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $conn = $database->getConnection();

        // Fetch user full name
        $stmt = $conn->prepare("SELECT full_name FROM user_details WHERE id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $fullName = $user['full_name'];
        }

        // Fetch reported items
        //$stmt = $conn->prepare("SELECT item_id, item_name, category, description, lost_location, lost_date, status, image_path FROM reported_items WHERE status IN ('pending', 'found')");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $fullName = "Guest";
        $items = [];
    }
} else {
    $items = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
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

    .user-greeting a {
      pointer-events: auto !important;
      cursor: pointer !important;
      position: relative;
      z-index: 1000;
      text-decoration: none;
      color: inherit;
      transition: opacity 0.3s ease, transform 0.2s ease;
    }

    .user-greeting a:hover {
      opacity: 0.8;
      transform: scale(1.02);
      text-decoration: underline;
    }

    .user-greeting a:active {
      transform: scale(0.98);
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

    .personalized-welcome {
      font-size: 1.8em;
      color: #2c3e50;
      margin-bottom: 15px;
      font-weight: bold;
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
      margin-bottom: 10px;
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

    .claim-button {
      background-color: #27ae60;
      color: white;
      box-shadow: 0 4px 8px rgba(39, 174, 96, 0.5);
    }

    .claim-button:hover {
      background-color: #2ecc71;
      box-shadow: 0 6px 12px rgba(46, 204, 113, 0.7);
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

    .side-image {
      flex: 1;
      text-align: end;
      text-align: top;
    }

    .side-image {
  position: absolute;   /* take it out of normal flow */
  top: 720px;             /* vertical position */
  right: 200px;          /* distance from right edge */
  transform: translateY(-50%); /* center vertically */
}

.side-image img {
  max-width: 60%;
  height: auto;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  object-fit: cover;
}


    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
      backdrop-filter: blur(5px);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 600px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      color: black;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
    }

    .form-group textarea {
      height: 100px;
      resize: vertical;
    }

    .submit-btn {
      background-color: #fbb117;
      color: black;
      padding: 12px 30px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
      background-color: #fdd017;
    }

    .search-section {
      background: linear-gradient(135deg, #e8f4fd, #ffffff);
      padding: 30px 20px;
      margin: 20px 0;
      border-radius: 10px;
    }

    .search-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .search-form {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .search-input {
      flex: 1;
      min-width: 200px;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    .search-btn {
      padding: 10px 20px;
      background-color: #3498db;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .search-btn:hover {
      background-color: #2980b9;
    }

    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .item-card {
      background: white;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }

    .item-card:hover {
      transform: translateY(-5px);
    }

    .item-status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .status-pending {
      background-color: #ffebee;
      color: #c62828;
    }

    .status-found {
      background-color: #e8f5e8;
      color: #2e7d32;
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
      transition: transform 0.3s ease;
    }

    .how-it-works-card:hover {
      transform: scale(1.05);
    }

    .how-it-works-icon img {
      width: 50px;
      height: 50px;
    }

    footer.advanced-footer {
      background-color: #333333;
      color: white;
      text-align: center;
      padding: 20px 0;
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

    @media (max-width: 768px) {
      .content-wrapper {
        flex-direction: column;
        text-align: center;
      }
      
      .search-form {
        flex-direction: column;
      }
      
      .nav-right {
        margin-right: 20px;
      }
      
      .user-greeting {
        font-size: 14px;
        margin-right: 10px;
      }
    }
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a href="home.php"><img src="logo2.png" alt="Company logo" /></a>
      <ul>
        <li><a class="active" href="home.php">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="report_lost.php">Report Items</a>
    <?php else: ?>
      <a href="loginuser.php">Report Items</a>
    <?php endif; ?>
  </li>
        <li>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="claimmissing.php">Claim Missing</a>
    <?php else: ?>
      <a href="loginuser.php">Claim Missing</a>
    <?php endif; ?>
  </li>
        <li><a href="contactus.php">Contact Us</a></li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1): ?>
    <li><a href="admindashboard.php">Dashboard</a></li>
  <?php endif; ?>
      </ul>
    </div>
    <div class="nav-right">
      <span class="user-greeting" id="userGreeting">
        <a href="profile.php">Welcome, <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>!</a>
      </span>
      <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="profile.php">My Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="dropdown">
            <a href="javascript:void(0)">Login</a>
            <div class="dropdown-content">
              <a href="loginuser.php">User</a>
              <a href="loginadmin.php">Admin</a>
            </div>
          </li>
          <li class="dropdown">
            <a href="javascript:void(0)">Sign Up</a>
            <div class="dropdown-content">
              <a href="signupuser.php">User</a>
              <a href="signupadmin.php">Admin</a>
            </div>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <img src="wtc-l-f.jpg" alt="Cover Photo" class="cover-photo" />

  <section class="border-wrapper">
    <div class="main-content">
      <div class="content-wrapper">
        <div class="text-buttons">
          <div class="personalized-welcome" id="personalizedWelcome">
            <?php if (isset($_SESSION['user_id'])): ?>
              Welcome <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?> to Lost & Found
            <?php else: ?>
              Welcome to Lost & Found
            <?php endif; ?>
          </div>
          <p>
            Have you lost something or found an item that belongs to someone else? Our platform connects people
            to reunite lost items with their owners. Report a lost or found item today or browse our database
            to claim what's yours.
          </p>
          <p>
            Use the tools below to report items, claim missing belongings, or search through our database. We're
            here to help you every step of the way!
          </p>
          <div class="button-group">
            <a href="report_lost.php" class="cta-button">Report a Lost Item</a>
            <a href="claimmissing.php" class="cta-button">Claim Missing Item</a>

            </div>
          
        <div class="side-image">
          <img src="logo2.png" alt="Lost & Found Illustration" />
          <p style="margin-top: 10px;">&copy;2025 Lost&Found Inc. All rights reserved.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Poster Section -->
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

  <!-- How It Works Section -->
  <section id="how-it-works">
    <h2 class="animated-header">HOW IT WORKS!</h2>
    <div class="how-it-works-wrapper">
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="search.png" alt="Search Icon" /></div>
        <h3>Step 1: Report</h3>
        <p>User reports a lost or found item using our portal interface.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="db.png" alt="Database Icon" /></div>
        <h3>Step 2: Database Entry</h3>
        <p>The item details are stored in our secure database system.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="search2.png" alt="Search Icon" /></div>
        <h3>Step 3: Search</h3>
        <p>Users can search for their lost items using various filters.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="verify.png" alt="Verify Icon" /></div>
        <h3>Step 4: Verification</h3>
        <p>Admin verifies claims and matches them with found items.</p>
      </div>
      <div class="how-it-works-card">
        <div class="how-it-works-icon"><img src="return.png" alt="Return Icon" /></div>
        <h3>Step 5: Return</h3>
        <p>Verified items are returned to their rightful owners.</p>
      </div>
    </div>
  </section>

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

  <script>
    const userData = {
      fullName: "<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>",
      isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>
    };
    const itemsData = <?php echo json_encode($items); ?>;

    // Initialize page
    function initializePage() {
      updateUserGreeting();
      displayItems(itemsData);
    }

    // Update user greeting
    function updateUserGreeting() {
      const userGreeting = document.getElementById('userGreeting');
      const personalizedWelcome = document.getElementById('personalizedWelcome');
      
      userGreeting.innerHTML = `<a href="profile.php">Welcome, ${userData.fullName}!</a>`;
      if (userData.isLoggedIn) {
        personalizedWelcome.textContent = `Welcome ${userData.fullName} to Lost & Found`;
      }
    }

    // Trigger action based on login status
    function triggerAction(targetId) {
      if (!userData.isLoggedIn) {
        window.location.href = 'loginuser.php';
      } else if (targetId === 'searchSection') {
        toggleSearch();
      } else {
        openModal(targetId);
      }
    }

  </script>
</body>
</html>
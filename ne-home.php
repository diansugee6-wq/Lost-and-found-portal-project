<?php
session_start();
require_once 'configure.php';

$fullName = "Guest"; // Default
$reportPage = "loginuser.php";
$claimPage = "loginuser.php";

if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT full_name FROM user_details WHERE id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $fullName = $user['full_name'];
            $reportPage = "reportitem.php";
            $claimPage = "claimmissing.php";
        }
    } catch(Exception $e) {
        $fullName = "Guest";
    }
}
?>
<script>
  const userData = {
    fullName: "<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>",
    reportPage: "<?php echo $reportPage; ?>",
    claimPage: "<?php echo $claimPage; ?>"
  };
</script>

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

    .user-greeting {
      color: white;
      font-weight: bold;
      margin-right: 15px;
      padding: 8px 12px;
      background: rgba(0,0,0,0.3);
      border-radius: 20px;
      backdrop-filter: blur(5px);
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

    /* Modal Styles */
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

    .status-lost {
      background-color: #ffebee;
      color: #c62828;
    }

    .status-found {
      background-color: #e8f5e8;
      color: #2e7d32;
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
    /* Keep all your previous CSS here as-is */
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a href="home.php"><img src="logo2.png" alt="Company logo" /></a>
      <ul>
        <li><a class="active" href="home.php">Home</a></li>
        <li><a href="#about">About Us</a></li>
        <li><a href="#" id="navReport">Report Items</a></li>
        <li><a href="#" id="navClaim">Claim Missing</a></li>
        <li><a href="#contact">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <span class="user-greeting" id="userGreeting">Welcome, Guest!</span>
      <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="loginuser.php">Login</a></li>
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
            Welcome to Lost & Found
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
            <!-- Updated buttons with smart redirect -->
            <a href="<?php echo $reportPage; ?>" class="cta-button">Report an Item</a>
            <a href="<?php echo $claimPage; ?>" class="cta-button claim-button">Claim Missing Item</a>
          </div>
        </div>
        <div class="side-image">
          <img src="logo2.png" width="60%" height="100%" alt="Lost & Found Illustration" />
          <p style="margin-top: 20px;">&copy;2025 Lost&Found Inc. All rights reserved.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Keep search section and modals as before -->
  <section class="search-section" id="searchSection" style="display: none;">
    <!-- Your search form code here -->
  </section>

  <div id="reportModal" class="modal">
    <!-- Your Report Lost Item Modal code -->
  </div>

  <div id="foundModal" class="modal">
    <!-- Your Report Found Item Modal code -->
  </div>

  <section id="how-it-works">
    <!-- How it works section -->
  </section>

  <footer class="advanced-footer">
    <!-- Footer content -->
  </footer>

  <script>
    // Initialize page
    function initializePage() {
      const userGreeting = document.getElementById('userGreeting');
      const personalizedWelcome = document.getElementById('personalizedWelcome');

      userGreeting.textContent = `Welcome, ${userData.fullName}!`;
      personalizedWelcome.textContent = `Welcome ${userData.fullName} to Lost & Found`;

      // Nav links redirect
      document.getElementById('navReport').href = userData.reportPage;
      document.getElementById('navClaim').href = userData.claimPage;
    }

    // Modal and search functions as in your current JS code
    function toggleSearch() {
      const searchSection = document.getElementById('searchSection');
      if (searchSection.style.display === 'none' || searchSection.style.display === '') {
        searchSection.style.display = 'block';
        searchSection.scrollIntoView({ behavior: 'smooth' });
      } else {
        searchSection.style.display = 'none';
      }
    }

    // Call initialize
    window.onload = initializePage;
  </script>
</body>
</html>

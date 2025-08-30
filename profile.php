<?php
session_start();
require_once 'configure.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginuser.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];
$full_name = '';
$email = '';
$phone = '';
$items = [];
$message = '';
$show_edit_form = false;

try {
    // Fetch user details
    $stmt = $conn->prepare("SELECT full_name, email, contact_number AS phone FROM user_details WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $full_name = $user['full_name'];
        $email = $user['email'] ?? '';
        $phone = $user['phone'] ?? '';
    } else {
        $message = 'User not found.';
    }

    // Fetch user's reported items
    $stmt = $conn->prepare("SELECT item_id, item_name, category, description, lost_location, lost_date, status, image_path FROM reported_items WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle profile update submission (POST request)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        // Validation
        $errors = [];
        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        if (!empty($phone) && !preg_match('/^0[0-9]{9}$|^(\+94)[0-9]{9}$/', $phone)) {
            $errors[] = 'Invalid phone number. Use 10 digits starting with 0 or +94 followed by 9 digits.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE user_details SET full_name = :full_name, email = :email, contact_number = :phone WHERE id = :user_id");
            $stmt->execute([
                ':full_name' => $full_name,
                ':email' => $email ?: null,
                ':phone' => $phone ?: null,
                ':user_id' => $user_id
            ]);
            $message = 'Profile updated successfully!';
            // Fetch the updated user details to reflect changes immediately
            $stmt = $conn->prepare("SELECT full_name, email, contact_number AS phone FROM user_details WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $full_name = $user['full_name'];
                $email = $user['email'] ?? '';
                $phone = $user['phone'] ?? '';
            }
        } else {
            $message = implode(' ', $errors);
            $show_edit_form = true; // Stay on the edit form if there are errors
        }
    }

    // Check if the user wants to see the edit form (GET request with parameter)
    if (isset($_GET['edit']) && $_GET['edit'] == 'true') {
        $show_edit_form = true;
    }

} catch (Exception $e) {
    $message = 'Error: ' . $e->getMessage();
    $show_edit_form = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile - Lost&Found.com</title>
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

    .main-content {
      padding: 40px 20px;
      background-color: white;
      max-width: 800px;
      margin: 100px auto;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .profile-details h2, .reported-items h2 {
      font-size: 1.8em;
      color: #333;
      margin-bottom: 20px;
    }

    .profile-details p {
      font-size: 1.1em;
      color: #555;
      margin: 10px 0;
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

    .status-claimed {
      background-color: #e3f2fd;
      color: #1565c0;
    }

    .edit-btn {
      background-color: #fbb117;
      color: black;
      padding: 12px 30px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s ease, transform 0.2s ease;
      margin-top: 20px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
    }

    .edit-btn:hover {
      background-color: #fdd017;
      transform: scale(1.05);
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

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
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

    .message {
      color: #2e7d32;
      margin-bottom: 20px;
      text-align: center;
    }

    .error {
      color: #c62828;
    }

    footer.advanced-footer {
      background-color: #333333;
      color: white;
      text-align: center;
      padding: 20px 0;
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
      .main-content {
        margin: 80px 20px;
      }
      .nav-right {
        margin-right: 20px;
      }
      .user-greeting {
        font-size: 14px;
        margin-right: 10px;
      }
      .items-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <nav class="nav">
    <div class="nav-left">
      <a href="home.php"><img src="logo2.png" alt="Company logo" /></a>
      <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="home.php">Report Items</a></li>
        <li><a href="home.php">Claim Missing</a></li>
        <li><a href="contactus.php">Contact Us</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <span class="user-greeting">Welcome, <?php echo htmlspecialchars($full_name); ?>!</span>
      <ul>
        <li><a href="profile.php" class="active">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <?php if ($message): ?>
    <div class="main-content">
      <div class="message<?php echo strpos($message, 'Error') !== false ? ' error' : ''; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="main-content" style="display: <?php echo $show_edit_form ? 'none' : 'block'; ?>;">
    <div class="profile-details">
      <h2>My Profile</h2>
      <p><strong>Full Name:</strong> <?php echo htmlspecialchars($full_name); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($email ?: 'Not provided'); ?></p>
      <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone ?: 'Not provided'); ?></p>
      <a href="profile.php?edit=true" class="edit-btn">Edit Profile</a>
    </div>

    <div class="reported-items">
      <h2>My Reported Items</h2>
      <?php if (empty($items)): ?>
        <p>No items reported yet.</p>
      <?php else: ?>
        <div class="items-grid">
          <?php foreach ($items as $item): ?>
            <div class="item-card">
              <div class="item-status status-<?php echo $item['status']; ?>">
                <?php echo strtoupper($item['status']); ?>
              </div>
              <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
              <?php
                $imagePathRaw = isset($item['image_path']) ? trim((string)$item['image_path']) : '';
                $webPath = '';
                if ($imagePathRaw !== '') {
                  if (preg_match('~^(?:https?:)?//|^data:~i', $imagePathRaw)) {
                    $webPath = $imagePathRaw;
                  } else {
                    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                    if ($imagePathRaw[0] === '/') {
                      $webPath = $basePath . $imagePathRaw;
                    } else {
                      $webPath = $basePath . '/' . $imagePathRaw;
                    }
                  }
                }
                $hasImage = false;
                if ($webPath !== '' && !preg_match('~^(?:https?:)?//|^data:~i', $webPath)) {
                  $absWeb = ($webPath[0] === '/') ? $webPath : ('/' . $webPath);
                  $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . str_replace('/', DIRECTORY_SEPARATOR, $absWeb);
                  $hasImage = file_exists($fsPath);
                } elseif ($webPath !== '') {
                  $hasImage = true;
                }
              ?>
              <?php if ($hasImage): ?>
                <img src="<?php echo htmlspecialchars($webPath); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" style="max-width: 100%; border-radius: 5px; margin-bottom: 10px;" />
              <?php endif; ?>
              <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
              <p><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
              <p><strong>Location:</strong> <?php echo htmlspecialchars($item['lost_location']); ?></p>
              <p><strong>Date:</strong> <?php echo htmlspecialchars($item['lost_date']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="main-content" style="display: <?php echo $show_edit_form ? 'block' : 'none'; ?>;">
    <h2>Edit Profile</h2>
    <form method="POST" action="profile.php">
      <div class="form-group">
        <label for="full_name">Full Name:</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
      </div>
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
      </div>
      <div class="form-group">
        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
      </div>
      <button type="submit" class="submit-btn">Save Changes</button>
      <a href="profile.php" class="submit-btn" style="background-color: #c62828; margin-left: 10px;">Cancel</a>
    </form>
  </div>

  <footer class="advanced-footer">
    <div class="container">
      <p>📧 <a href="mailto:info@lostandfound.com">info@lostandfound.com</a></p>
      <p>📞 <a href="tel:+94762639287">+94 76 263 9287</a></p>
      <p>&copy; 2025 Lost&Found Inc. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
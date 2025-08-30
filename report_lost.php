<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginuser.php");
    exit();
}

$fullName = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once 'configure.php';
    $database = new Database();
    $db = $database->getConnection();

    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lost_date = $_POST['lost_date'] ?? '';
    $lost_location = trim($_POST['lost_location'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validate user exists
    $check_user_sql = "SELECT id FROM user_details WHERE id = :user_id";
    $check_user_stmt = $db->prepare($check_user_sql);
    $check_user_stmt->bindParam(':user_id', $user_id);
    $check_user_stmt->execute();
    if ($check_user_stmt->rowCount() === 0) {
        header("Location: loginuser.php");
        exit();
    }

    // Handle image upload safely
    $uploadError = '';
    $savedPath = '';
    if (!empty($_FILES['item_image']['name'])) {
        $target_dir = 'uploads/';
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }
        $tmp = $_FILES['item_image']['tmp_name'];
        $original = basename($_FILES['item_image']['name']);
        $sanitized = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
        $ext = strtolower(pathinfo($sanitized, PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','webp','gif'];
        $allowedMime = ['image/jpeg','image/png','image/webp','image/gif'];
        $finfo = @mime_content_type($tmp);
        if (!in_array($ext, $allowedExt, true) || ($finfo && !in_array($finfo, $allowedMime, true))) {
            $uploadError = 'Please upload a valid image file (jpg, jpeg, png, webp, gif).';
        } elseif (!is_uploaded_file($tmp)) {
            $uploadError = 'Upload failed. Please try again.';
        } else {
            $unique = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target_file = $target_dir . $unique;
            if (move_uploaded_file($tmp, $target_file)) {
                $savedPath = $target_file; // store project-relative path like 'uploads/xxx.ext'
            } else {
                $uploadError = 'Unable to save the uploaded image.';
            }
        }
    }

    if ($uploadError === '') {
        $sql = "INSERT INTO reported_items (user_id, username, item_name, category, description, lost_date, lost_location, image_path)
                VALUES (:user_id, :username, :item_name, :category, :description, :lost_date, :lost_location, :image_path)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':username', $_SESSION['username']);
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':lost_date', $lost_date);
        $stmt->bindParam(':lost_location', $lost_location);
        $stmt->bindParam(':image_path', $savedPath);
        if ($stmt->execute()) {
            header('Location: claimmissing.php?reported=1');
            exit();
        } else {
            $uploadError = 'Error reporting item. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Report Lost Item - Lost & Found</title>
        <style>
            :root { --yellow:#fbb117; --yellow-light:#fdd017; }
            * { box-sizing: border-box; margin:0; padding:0; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f5f5f5; color:#333; }

                    /* Navbar (shared) */
                    nav {
                        display:flex; align-items:center; justify-content:space-between;
                        position:absolute; top:0; left:0; width:100%; padding:10px 20px; z-index:1000;
                        background:#fff; border-bottom:1px solid #eee; box-shadow:0 2px 8px rgba(0,0,0,0.06);
                    }
                    nav a img { height:50px; }
                    nav ul { list-style:none; display:flex; gap:24px; margin:0; padding:0; }
                    nav li a {
                        display:block; color:#111; text-decoration:none; padding:10px 12px; border-radius:6px;
                    }
                    nav li a:hover { background:rgba(0,0,0,0.05); }
                    nav li a.active { font-weight:700; box-shadow: inset 0 -3px 0 var(--yellow); }
                    .nav-left { display:flex; align-items:center; gap:12px; }
                    .nav-right { display:flex; align-items:center; gap:16px; margin-right:20px; }
                    .nav-right li a { border:none; padding:8px 10px; }
                    .user-greeting a { color:inherit; text-decoration:none; }
                    .user-greeting a:hover { text-decoration:underline; }

            /* Frame wrappers */
            .border-wrapper { padding:15px; border:15px solid; border-image:repeating-linear-gradient(45deg, black 0 10px, yellow 10px 20px) 30; background:white; }
            .content-wrapper { max-width:1200px; margin:0 auto; display:block; }
            .container { max-width:1200px; margin:0 auto; padding:100px 20px; }

            /* Header */
            .header { background:linear-gradient(to bottom right, var(--yellow-light), var(--yellow)); color:#111; text-align:center; padding:32px 20px; border-radius:10px; margin-bottom:24px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
            .header h1 { font-size:2rem; display:flex; align-items:center; justify-content:center; gap:10px; }
            .icon { width:1.2em; height:1.2em; fill:currentColor; }

            /* Card */
            .card { background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.08); border:1px solid #eee; padding:24px; }
            .form-title { font-size:1.25rem; font-weight:700; margin-bottom:16px; }
            .form-grid { display:grid; grid-template-columns:1fr; gap:16px; }
            label { display:block; margin-bottom:6px; font-weight:600; color:#333; }
            input[type="text"], input[type="date"], select, textarea, input[type="file"] { width:100%; padding:10px; border:2px solid #e1e1e1; border-radius:6px; font-size:14px; }
            input:focus, select:focus, textarea:focus { outline:none; border-color:var(--yellow); box-shadow:0 0 0 3px rgba(251,177,23,0.15); }
            textarea { min-height:140px; resize:vertical; }

            .image-preview { width:220px; height:220px; border:2px dashed #ddd; margin-top:8px; display:flex; align-items:center; justify-content:center; }
            #preview-image { max-width:100%; max-height:100%; display:none; }

            .actions { margin-top:8px; display:flex; gap:12px; }
            .btn { background:linear-gradient(to bottom right, var(--yellow-light), var(--yellow)); color:#111; padding:12px 20px; border:none; border-radius:6px; font-weight:700; cursor:pointer; box-shadow:0 6px 16px rgba(251,177,23,0.25); }
            .btn:hover { transform:translateY(-1px); box-shadow:0 10px 22px rgba(251,177,23,0.25); }

            @media (min-width: 720px) {
                .form-grid { grid-template-columns:1fr 1fr; }
                .span-2 { grid-column: span 2; }
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
                <li><a class="active" href="report_lost.php">Report Items</a></li>
                <li><a href="claimmissing.php">Claim Missing</a></li>
                <li><a href="contactus.php">Contact Us</a></li>
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
                    <li><a href="loginuser.php">Login</a></li>
                    <li><a href="signupuser.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <section class="border-wrapper">
        <div class="content-wrapper">
            <div class="container">
                <div class="header">
                    <h1>
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21l-1-1C5 15 2 12 2 8a6 6 0 0 1 12 0c0 4-3 7-9 12l-1 1z" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                        Report a Lost Item
                    </h1>
                </div>

                <div class="card">
                    <?php if (!empty($uploadError ?? '')): ?>
                        <div style="background:#ffefef;border:1px solid #ffcece;color:#a40000;padding:10px 12px;border-radius:6px;margin-bottom:12px;">
                            <?php echo htmlspecialchars($uploadError); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data" class="form-grid">
                        <div>
                            <label for="item_name">Item Name</label>
                            <input type="text" id="item_name" name="item_name" required />
                        </div>
                        <div>
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="electronics">Electronics</option>
                                <option value="documents">Documents</option>
                                <option value="jewelry">Jewelry</option>
                                <option value="clothing">Clothing</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="span-2">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>
                        <div>
                            <label for="lost_date">Date Lost</label>
                            <input type="date" id="lost_date" name="lost_date" required />
                        </div>
                        <div>
                            <label for="lost_location">Location Lost</label>
                            <input type="text" id="lost_location" name="lost_location" required />
                        </div>
                        <div class="span-2">
                            <label for="item_image">Upload Image</label>
                            <input type="file" id="item_image" name="item_image" accept="image/*" required />
                            <div class="image-preview"><img id="preview-image" src="#" alt="Preview" /></div>
                        </div>
                        <div class="span-2 actions">
                            <button type="submit" class="btn">
                                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z" fill="none"/><path d="M4 7l8 5 8-5" fill="none" stroke="currentColor" stroke-width="2"/></svg>
                                Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        const input = document.getElementById('item_image');
        if (input) {
            input.addEventListener('change', function() {
                const file = this.files && this.files[0];
                const preview = document.getElementById('preview-image');
                if (file && preview) {
                    preview.src = URL.createObjectURL(file);
                    preview.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>
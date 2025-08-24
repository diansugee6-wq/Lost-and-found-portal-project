<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginuser.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'configure.php';
    $database = new Database();
    $db = $database->getConnection();

    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $lost_date = $_POST['lost_date'];
    $lost_location = $_POST['lost_location'];

    // Handle image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["item_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO lost_items (user_id, item_name, category, description, lost_date, lost_location, image_path) 
                VALUES (:user_id, :item_name, :category, :description, :lost_date, :lost_location, :image_path)";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':lost_date', $lost_date);
        $stmt->bindParam(':lost_location', $lost_location);
        $stmt->bindParam(':image_path', $target_file);
        
        if ($stmt->execute()) {
            echo "<script>alert('Item reported successfully!'); window.location.href='afterlogin_home.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Lost Item - Lost&Found.com</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "poppins", sans-serif;
        }
        
        body {
            background: #f0f2f5;
            color: #333;
        }

        nav {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #ffc107;
            color: #000;
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background: #ffb300;
        }

        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        #preview-image {
            max-width: 100%;
            max-height: 100%;
            display: none;
        }
    </style>
</head>
<body>
    <nav>
        <a href="afterlogin_home.php">
            <img src="logo2.png" alt="logo" style="height: 50px;">
        </a>
        <ul style="list-style: none; display: flex; gap: 2rem;">
            <li><a href="afterlogin_home.php">Home</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="form-container">
        <h2 class="form-title">Report a Lost Item</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="item_name">Item Name</label>
                <input type="text" id="item_name" name="item_name" required>
            </div>

            <div class="form-group">
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

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="lost_date">Date Lost</label>
                <input type="date" id="lost_date" name="lost_date" required>
            </div>

            <div class="form-group">
                <label for="lost_location">Location Lost</label>
                <input type="text" id="lost_location" name="lost_location" required>
            </div>

            <div class="form-group">
                <label for="item_image">Upload Image</label>
                <input type="file" id="item_image" name="item_image" accept="image/*" required>
                <div class="image-preview">
                    <img id="preview-image" src="#" alt="Preview">
                </div>
            </div>

            <button type="submit" class="submit-btn">Submit Report</button>
        </form>
    </div>

    <script>
        document.getElementById('item_image').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                const preview = document.getElementById('preview-image');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        }
    </script>
</body>
</html>
<?php
include 'db_connection.php';
session_start();

// Ensure campaign_id is properly received and validated
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid campaign ID");
}
$campaign_id = intval($_GET["id"]);

// Create uploads directory if it doesn't exist
$upload_dir = "uploads/campaign_images/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;
    
    // Generate unique filename
    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $file_name = uniqid('campaign_') . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;
    
    // Validate file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($file_extension, $allowed_types)) {
        die("Error: Only JPG, JPEG, PNG & GIF files are allowed.");
    }
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Insert image data into database
        $stmt = $conn->prepare("INSERT INTO campaign_images (campaign_id, file_name, is_primary, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("isi", $campaign_id, $file_name, $is_primary);
        if ($stmt->execute()) {
            // If this is primary image, update other images to non-primary
            if ($is_primary) {
                $update_stmt = $conn->prepare("UPDATE campaign_images SET is_primary = 0 WHERE campaign_id = ? AND file_name != ?");
                $update_stmt->bind_param("is", $campaign_id, $file_name);
                $update_stmt->execute();
            }
            header("Location: campaign.php?id=" . $campaign_id);
            exit;
        } else {
            echo "Database error: " . $stmt->error;
        }
    } else {
        echo "Error uploading file. Details: ";
        print_r($_FILES['image']['error']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Campaign Image - Crowdfunding Platform</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Crowdfunding Platform</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="campaigns.php">Campaigns</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="upload-image-section">
            <h2>Upload Campaign Image</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="image">Select Image:</label>
                    <input type="file" name="image" id="image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_primary" value="1"> 
                        Set as Primary Campaign Image
                    </label>
                </div>

                <input type="hidden" name="campaign_id" value="<?php echo htmlspecialchars($campaign_id); ?>">
                
                <div class="form-group">
                    <button type="submit" class="btn">Upload Image</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform</p>
    </footer>
</body>
</html>
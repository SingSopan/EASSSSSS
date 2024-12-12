<?php
include 'db_connection.php';
session_start();

$campaign_id = $_GET["id"];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $campaign_id = intval($_POST['campaign_id']);
    $is_primary = intval($_POST['is_primary'] ?? 0);
    $target_dir = "uploads/";
    $file_name = basename($_FILES['image']['name']);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Insert image data into `campaign_images`
        $stmt = $conn->prepare("INSERT INTO campaign_images (campaign_id, file_name, is_primary, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isi", $campaign_id, $file_name, $is_primary);
        if ($stmt->execute()) {
            echo "Image uploaded successfully.";
        } else {
            echo "Database error: " . $stmt->error;
        }
    } else {
        echo "Error uploading file.";
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
            <a href="index.php">Home</a>
            <a href="campaigns.php">Campaigns</a>
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
                        <input type="checkbox" name="is_primary"> 
                        Set as Primary Campaign Image
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-submit">Upload Image</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform. All rights reserved.</p>
    </footer>
</body>
</html>
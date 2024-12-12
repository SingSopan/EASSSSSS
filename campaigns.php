<?php
include 'db_connection.php';

// Fetch campaigns with prepared statement
$sql = "SELECT * FROM campaigns";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Campaigns - Crowdfunding Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .campaign-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .campaign-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .campaign-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #555;
        }
    </style>
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
        <h2>All Campaigns</h2>
        <div class="campaign-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="campaign-card">
                    <?php
                    // Get campaign image
                    $image_sql = "SELECT file_name FROM campaign_images WHERE campaign_id = ? ORDER BY is_primary DESC LIMIT 1";
                    $image_stmt = $conn->prepare($image_sql);
                    $image_stmt->bind_param("i", $row['id']);
                    $image_stmt->execute();
                    $image_result = $image_stmt->get_result();
                    $image = $image_result->fetch_assoc();
                    
                    // Image path
                    $image_path = $image ? "uploads/campaign_images/" . htmlspecialchars($image['file_name']) : "uploads/default-image.jpg";
                    ?>
                    
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         onerror="this.src='uploads/default-image.jpg';">
                    
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['short_description']); ?></p>
                    <p><strong>Goal:</strong> $<?php echo number_format($row['goal_amount'], 2); ?></p>
                    <p><strong>Raised:</strong> $<?php echo number_format($row['current_amount'], 2); ?></p>
                    <a href="campaign.php?id=<?php echo $row['id']; ?>" class="btn">View Campaign</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform</p>
    </footer>
</body>
</html>
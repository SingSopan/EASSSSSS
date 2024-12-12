<?php
require 'db_connection.php';

// Fetch featured campaigns with their images, prioritizing primary images
$sql = "SELECT c.*, 
        COALESCE(
            (SELECT ci.file_name 
             FROM campaign_images ci 
             WHERE ci.campaign_id = c.id AND ci.is_primary = 1 
             LIMIT 1),
            (SELECT ci.file_name 
             FROM campaign_images ci 
             WHERE ci.campaign_id = c.id 
             LIMIT 1)
        ) as image_file
        FROM campaigns c 
        ORDER BY c.created_at DESC 
        LIMIT 6";  // Limit to 6 featured campaigns
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Crowdfunding Platform</title>
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
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .campaign-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            background-color: #f5f5f5;
            position: relative;
        }
        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .campaign-image:hover img {
            transform: scale(1.05);
        }
        .campaign-details {
            margin: 15px 0;
        }
        .campaign-details h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.25rem;
        }
        .campaign-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .campaign-goal {
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .campaign-raised {
            color: #4CAF50;
            font-weight: bold;
            margin: 5px 0;
        }
        .progress-bar {
            background: #eee;
            height: 8px;
            border-radius: 4px;
            margin: 15px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: #4CAF50;
            height: 100%;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #555;
        }
        .welcome-section {
            text-align: center;
            padding: 40px 20px;
            background: #f9f9f9;
            margin-bottom: 20px;
        }
        .welcome-section h2 {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Crowdfunding Platform</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="campaigns.php">All Campaigns</a></li>
                <li><a href="create_campaign.php">Create Campaign</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="welcome-section">
            <h2>Featured Campaigns</h2>
            <p>Support amazing projects and help make dreams come true</p>
        </div>

        <div class="campaign-list">
            <?php while ($campaign = $result->fetch_assoc()): ?>
                <div class="campaign-card">
                    <div class="campaign-image">
                        <?php if (!empty($campaign['image_file'])): ?>
                            <img src="uploads/campaign_images/<?php echo htmlspecialchars($campaign['image_file']); ?>" 
                                 alt="<?php echo htmlspecialchars($campaign['name']); ?>"
                                 onerror="this.src='uploads/default-campaign.jpg'">
                        <?php else: ?>
                            <img src="uploads/default-campaign.jpg" 
                                 alt="<?php echo htmlspecialchars($campaign['name']); ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="campaign-details">
                        <h3><?php echo htmlspecialchars($campaign['name']); ?></h3>
                        <div class="campaign-description">
                            <?php echo htmlspecialchars($campaign['short_description']); ?>
                        </div>
                        
                        <?php
                        $progress = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
                        $progress = min(100, $progress); // Cap at 100%
                        ?>
                        
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        
                        <div class="campaign-goal">
                            Goal: $<?php echo number_format($campaign['goal_amount'], 2); ?>
                        </div>
                        <div class="campaign-raised">
                            Raised: $<?php echo number_format($campaign['current_amount'], 2); ?>
                            (<?php echo number_format($progress, 1); ?>%)
                        </div>
                        <p><strong>Backers:</strong> <?php echo number_format($campaign['backer_count']); ?></p>
                        <a href="campaign.php?id=<?php echo $campaign['id']; ?>" class="btn">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform</p>
    </footer>
</body>
</html>
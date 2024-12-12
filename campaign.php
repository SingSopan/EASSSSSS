<?php
include 'db_connection.php';

$campaign_id = $_GET["id"];

$sql = "SELECT * FROM campaigns WHERE id = $campaign_id";
$campaign_result = $conn->query($sql);
$campaign = $campaign_result->fetch_assoc();

$sql = "SELECT * FROM campaign_images WHERE campaign_id = $campaign_id";
$images_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $campaign["name"]; ?> - Crowdfunding Platform</title>
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
        <div class="campaign-details">
            <div class="campaign-image">
                <?php while ($image = $images_result->fetch_assoc()) { ?>
                    <?php if ($image["is_primary"]) { ?>
                        <img src="campaign_images/<?php echo $image["file_name"]; ?>" alt="<?php echo $campaign["name"]; ?>">
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="campaign-info">
                <h2><?php echo $campaign["name"]; ?></h2>
                <p><?php echo $campaign["description"]; ?></p>
                <p>Goal: $<?php echo $campaign["goal_amount"]; ?></p>
                <p>Raised: $<?php echo $campaign["current_amount"]; ?></p>
                <p>Backers: <?php echo $campaign["backer_count"]; ?></p>
                <p>Perks: <?php echo $campaign["perks"]; ?></p>
                <a href="donate.php?id=<?php echo $campaign_id; ?>" class="btn">Donate</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Crowdfunding Platform</p>
    </footer>
</body>
</html>
<?php
include 'db_connection.php';

$sql = "SELECT * FROM campaigns";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Campaigns - Crowdfunding Platform</title>
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
        <h2>All Campaigns</h2>
        <div class="campaign-list">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="campaign-card">
                    <img src="campaign_images/<?php echo $row['id']; ?>.jpg" alt="<?php echo $row['name']; ?>">
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['short_description']; ?></p>
                    <p>Goal: $<?php echo $row['goal_amount']; ?></p>
                    <p>Raised: $<?php echo $row['current_amount']; ?></p>
                    <a href="campaign.php?id=<?php echo $row['id']; ?>" class="btn">View Campaign</a>
                </div>
            <?php } ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Crowdfunding Platform</p>
    </footer>
</body>
</html>
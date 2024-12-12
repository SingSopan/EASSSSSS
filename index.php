<?php
// Refactored index.php file for Crowdfunding platform

require 'db_connection.php';

// Fetch all campaigns
$sql = "SELECT * FROM campaigns";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdfunding Platform</title>
    <link rel="stylesheet" href="style.css">
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
        <section>
            <h2>All Campaigns</h2>
            <?php if ($result->num_rows > 0): ?>
                <ul>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            <a href="campaign.php?id=<?php echo $row['id']; ?>">View Details</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No campaigns available.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crowdfunding Platform</p>
    </footer>
</body>
</html>

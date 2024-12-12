<?php
include 'db_connection.php';
require_once __DIR__ . '/campaign_functions.php';
session_start();

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid campaign ID");
}

$campaign_id = intval($_GET["id"]);

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donate'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit;
    }

    $amount = floatval($_POST['amount']);
    $user_id = $_SESSION['user_id'];
    
    if ($amount <= 0) {
        $error = "Please enter a valid amount greater than 0";
    } else {
        try {
            $conn->begin_transaction();

            // Insert donation record
            $stmt = $conn->prepare("INSERT INTO transactions (campaign_id, user_id, amount, status, created_at) VALUES (?, ?, ?, 'completed', NOW())");
            $stmt->bind_param("iid", $campaign_id, $user_id, $amount);
            $stmt->execute();

            // Update campaign stats using the new function
            if (updateCampaignStats($conn, $campaign_id)) {
                $conn->commit();
                $_SESSION['success_message'] = "Thank you for your donation of $" . number_format($amount, 2) . "!";
                header("Location: campaign.php?id=" . $campaign_id);
                exit;
            } else {
                throw new Exception("Failed to update campaign stats");
            }

        } catch (Exception $e) {
            $conn->rollback();
            $error = "An error occurred while processing your donation. Please try again.";
        }
    }
}

// Fetch campaign details
$sql = "SELECT * FROM campaigns WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$campaign_result = $stmt->get_result();
$campaign = $campaign_result->fetch_assoc();

if (!$campaign) {
    die("Campaign not found");
}

// Fetch campaign images
$sql = "SELECT * FROM campaign_images WHERE campaign_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $campaign_id);
$stmt->execute();
$images_result = $stmt->get_result();

// Calculate progress percentage
$progress = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
$progress = min(100, $progress); // Cap at 100%
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($campaign["name"]); ?> - Crowdfunding Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .campaign-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .campaign-main {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .campaign-sidebar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }
        .campaign-image {
            margin-bottom: 20px;
        }
        .campaign-image img {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }
        .progress-bar {
            background: #eee;
            height: 10px;
            border-radius: 5px;
            margin: 15px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: #4CAF50;
            height: 100%;
            transition: width 0.3s ease;
        }
        .campaign-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
        }
        .donation-form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
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
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 4px; text-align: center;">
                <?php 
                echo htmlspecialchars($_SESSION['success_message']);
                unset($_SESSION['success_message']); 
                ?>
            </div>
        <?php endif; ?>

        <div class="campaign-container">
            <div class="campaign-main">
                <div class="campaign-image">
                    <?php 
                    $primary_image_shown = false;
                    while ($image = $images_result->fetch_assoc()) {
                        if ($image["is_primary"]) {
                            $primary_image_shown = true;
                            ?>
                            <img src="uploads/campaign_images/<?php echo htmlspecialchars($image["file_name"]); ?>" 
                                 alt="<?php echo htmlspecialchars($campaign["name"]); ?>">
                            <?php
                            break;
                        }
                    }
                    
                    if (!$primary_image_shown) {
                        $images_result->data_seek(0);
                        if ($image = $images_result->fetch_assoc()) {
                            ?>
                            <img src="uploads/campaign_images/<?php echo htmlspecialchars($image["file_name"]); ?>" 
                                 alt="<?php echo htmlspecialchars($campaign["name"]); ?>">
                            <?php
                        }
                    }
                    ?>
                </div>

                <h2><?php echo htmlspecialchars($campaign["name"]); ?></h2>
                
                <div class="campaign-stats">
                    <div class="stat-box">
                        <h3>$<?php echo number_format($campaign["current_amount"], 2); ?></h3>
                        <p>raised of $<?php echo number_format($campaign["goal_amount"], 2); ?></p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo number_format($campaign["backer_count"]); ?></h3>
                        <p>backers</p>
                    </div>
                </div>

                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
                </div>

                <div class="campaign-description">
                    <h3>About this campaign</h3>
                    <p><?php echo nl2br(htmlspecialchars($campaign["description"])); ?></p>
                </div>
            </div>

            <div class="campaign-sidebar">
                <h3>Make a Donation</h3>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="donation-form">
                    <div class="form-group">
                        <label for="amount">Donation Amount ($):</label>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               min="1" 
                               step="0.01" 
                               required 
                               placeholder="Enter amount"
                               value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                    </div>

                    <button type="submit" name="donate" class="btn">Complete Donation</button>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <p style="margin-top: 15px; font-size: 0.9em; color: #666;">
                            Please <a href="login.php">login</a> to make a donation.
                        </p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform</p>
    </footer>
</body>
</html>
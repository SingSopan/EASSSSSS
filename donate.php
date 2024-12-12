<?php
require 'db_connection.php';
require_once __DIR__ . '/campaign_functions.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

// Get campaign details
if (isset($_GET['id'])) {
    $campaign_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $campaign = $stmt->get_result()->fetch_assoc();

    if (!$campaign) {
        die("Campaign not found");
    }
}

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donate'])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to Campaign - Crowdfunding Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .donation-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .campaign-summary {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .success {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Donate to Campaign</h1>
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
        <div class="donation-form">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($campaign)): ?>
                <div class="campaign-summary">
                    <h2><?php echo htmlspecialchars($campaign['name']); ?></h2>
                    <p><strong>Current Progress:</strong> 
                        $<?php echo number_format($campaign['current_amount'], 2); ?> 
                        of $<?php echo number_format($campaign['goal_amount'], 2); ?>
                    </p>
                    <p><strong>Backers:</strong> <?php echo number_format($campaign['backer_count']); ?></p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="amount">Donation Amount ($):</label>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               min="1" 
                               step="0.01" 
                               required 
                               value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                    </div>

                    <input type="hidden" name="campaign_id" value="<?php echo $campaign_id; ?>">
                    
                    <button type="submit" name="donate" class="btn">Complete Donation</button>
                </form>
            <?php else: ?>
                <p>Campaign not found.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Crowdfunding Platform</p>
    </footer>
</body>
</html>
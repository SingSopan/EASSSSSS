<?php
// Debugging for Crowdfunding Platform: Donation Fix
require 'db_connection.php';
session_start();

// Donation Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donate'])) {
    $campaign_id = intval($_POST['campaign_id']);
    $user_id = $_SESSION['user_id'] ?? null; // Ensure user is logged in
    $amount = floatval($_POST['amount']);

    // Debug: Check if required fields are set
    if (!$user_id) {
        echo "Error: User not logged in.";
        exit;
    }

    if ($campaign_id <= 0 || $amount <= 0) {
        echo "Error: Invalid campaign ID or donation amount.";
        exit;
    }

    try {
        // Insert donation into `transactions`
        $code = uniqid('txn_');
        $stmt = $conn->prepare("INSERT INTO transactions (campaign_id, user_id, amount, status, code, created_at) VALUES (?, ?, ?, 'pending', ?, NOW())");
        $stmt->bind_param("iids", $campaign_id, $user_id, $amount, $code);

        if ($stmt->execute()) {
            // Debug: Confirm insertion
            echo "Transaction inserted successfully.";

            // Update campaign's current amount
            $update_stmt = $conn->prepare("UPDATE campaigns SET current_amount = current_amount + ? WHERE id = ?");
            $update_stmt->bind_param("di", $amount, $campaign_id);

            if ($update_stmt->execute()) {
                echo "Donation successful. Thank you for your support!";
            } else {
                echo "Error updating campaign amount: " . $update_stmt->error;
            }
        } else {
            echo "Error processing donation: " . $stmt->error;
        }
    } catch (Exception $e) {
        echo "Exception caught: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to Campaign</title>
    <link rel="stylesheet" href="style.css">
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
        <section>
            <h2>Make a Donation</h2>
            <form method="POST">
                <label for="campaign_id_donate">Campaign ID:</label>
                <input type="number" id="campaign_id_donate" name="campaign_id" required>

                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" required>

                <button type="submit" name="donate">Donate</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Crowdfunding Platform</p>
    </footer>
</body>
</html>

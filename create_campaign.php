<?php
include 'db_connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $short_description = $_POST["short_description"];
    $description = $_POST["description"];
    $goal_amount = $_POST["goal_amount"];
    $user_id = $_SESSION["user_id"];

    $sql = "INSERT INTO campaigns (name, short_description, description, goal_amount, user_id, created_at, updated_at) 
            VALUES ('$name', '$short_description', '$description', $goal_amount, $user_id, NOW(), NOW())";
    if ($conn->query($sql) === TRUE) {
        $campaign_id = $conn->insert_id;
        header("Location: upload_image.php?id=$campaign_id");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Campaign - Crowdfunding Platform</title>
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
        <div class="form-container">
            <h2>Create Campaign</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="short_description">Short Description:</label>
                    <input type="text" id="short_description" name="short_description" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="goal_amount">Goal Amount:</label>
                    <input type="number" id="goal_amount" name="goal_amount" required>
                </div>
                <button type="submit" class="btn">Create Campaign</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Crowdfunding Platform</p>
    </footer>
</body>
</html>
<?php
// Enable error reporting to debug any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: Get the policy_name from the URL parameter
if (isset($_GET['id'])) {
    $policy_name = $_GET['id'];
} else {
    die("Policy name not provided.");
}

// Step 2: Get user_id from session (assuming it's already set)
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    die("User not logged in.");
}

// Step 2: Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 3: Fetch the policy details based on policy_name, including the description
$sql = "SELECT * FROM policies WHERE policy_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $policy_name); // Bind the policy name parameter
$stmt->execute();
$result = $stmt->get_result();

// Step 4: Check if the policy was found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Policy not found.");
}

// Step 5: Fetch the user's profile information
$sql_user = "SELECT cnic, gender, age, monthly_income, area, city FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();

// Step 6: Check if the user profile is complete
$is_profile_complete = true;
$incomplete_fields = [];

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $fields = ['cnic', 'gender', 'age', 'monthly_income', 'area', 'city'];

    foreach ($fields as $field) {
        if (empty($user[$field])) {
            $is_profile_complete = false;
            $incomplete_fields[] = $field;
        }
    }
} else {
    die("User not found.");
}

// Step 7: Close the database connection
$stmt->close();
$stmt_user->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
        }

        body::before {
            content: '';
            position: fixed; /* Covers the entire screen */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('bg.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            filter: brightness(50%); /* Dims the background */
            z-index: -1; /* Places it behind all content */
        }

        header {
            background-color: #000;
            color: white;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        nav {
            display: flex;
            gap: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        nav a:hover {
            color: #a1bdc5; /* Change to a new color */
        }

        .search-profile {
            display: flex;
            align-items: center;
            position: relative;
        }

        .icon {
            font-size: 24px;
            margin-left: 15px;
            cursor: pointer;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 35px;
            right: 0;
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 150px;
            z-index: 10;
            text-align: left;
            overflow: hidden;
        }

        .dropdown div {
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dropdown div:hover {
            background: #f1f1f1;
        }

        .profile-icon:hover + .dropdown,
        .dropdown:hover {
            display: block;
        }

        /* Styling the policy-container */
        .policy-container {
            max-width: 500px; /* Ensuring the container is not too wide */
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box; /* Ensures padding doesn't cause overflow */
        }

        .policy-container h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .policy-details {
            margin-top: 20px;
            text-align: left;
        }

        .policy-details p {
            font-size: 18px;
            color: #555;
            margin: 10px 0;
        }

        .policy-details label {
            font-weight: bold;
            color: #333;
        }

        .policy-description {
            margin-top: 20px;
            font-size: 18px;
            color: #555;
            text-align: left;
            line-height: 1.5;
        }

        .purchase-btn-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .purchase-btn {
            padding: 15px 30px;
            background-color: #000;
            color: white;
            font-size: 18px;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .purchase-btn:hover {
            background-color: #a1bdc5;
        }

        footer {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            left: 0;
            right: 0;
        }

        .incomplete-message {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Main Menu Bar -->
<header>
    <h1>InsuraSync</h1>
    <nav>
        <a href="user_dashboard.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
    </nav>
    <div class="search-profile">
        <span class="icon">🔔</span>
        <span class="icon profile-icon">👤</span>
        <div class="dropdown">
            <div><a href="profile.php" style="text-decoration: none; color: inherit;">My Profile</a></div>
            <div><a href="landing_page1.php" style="text-decoration: none; color: inherit;">Logout</a></div>
        </div>
    </div>
</header>

<!-- Policy Details Section -->
<div class="policy-container">
    <h1><?php echo $row["policy_name"]; ?></h1>
    <div class="policy-details">
        <p><strong>Coverage Type:</strong> <?php echo $row["coverage_type"]; ?></p>
        <p><strong>Coverage Amount:</strong> $<?php echo $row["coverage_amount"]; ?></p>
        <p><strong>Deductible:</strong> $<?php echo $row["deductible"]; ?></p>
        <p><strong>Premium:</strong> $<?php echo $row["premium"]; ?></p>
        <p><strong>Policy Term:</strong> <?php echo $row["policy_term"]; ?> years</p>
        <p><strong>Additional Features:</strong> <?php echo $row["additional_features"]; ?></p>
        <p><strong>Target Scenario:</strong> <?php echo $row["target_scenario"]; ?></p>
    </div>
    
    <div class="policy-description">
        <strong>Description:</strong>
        <p><?php echo $row["description"]; ?></p>
    </div>

    <!-- Check if profile is incomplete and display message if necessary -->
    <?php if (!$is_profile_complete): ?>
        <p class="incomplete-message">
            Your profile is incomplete.
        </p>
    <?php endif; ?>

    <!-- Purchase Policy Button -->
    <div class="purchase-btn-container">
        <?php if ($is_profile_complete): ?>
            <a href="purchase_policy.php?user_id=<?php echo $user_id; ?>&policy_name=<?php echo urlencode($row['policy_name']); ?>&coverage_amount=<?php echo urlencode($row['coverage_amount']); ?>&deductible=<?php echo urlencode($row['deductible']); ?>&premium=<?php echo urlencode($row['premium']); ?>&policy_term=<?php echo urlencode($row['policy_term']); ?>&additional_features=<?php echo urlencode($row['additional_features']); ?>&target_scenario=<?php echo urlencode($row['target_scenario']); ?>" class="purchase-btn">Purchase Policy</a>
        <?php else: ?>
            <!-- <button class="purchase-btn" onclick="alert('Please complete your profile before purchasing the policy.');">Purchase Policy</button> -->
            <a href="profile.php?user_id=<?php echo $user_id; ?>&policy_name=<?php echo urlencode($row['policy_name']); ?>&coverage_amount=<?php echo urlencode($row['coverage_amount']); ?>&deductible=<?php echo urlencode($row['deductible']); ?>&premium=<?php echo urlencode($row['premium']); ?>&policy_term=<?php echo urlencode($row['policy_term']); ?>&additional_features=<?php echo urlencode($row['additional_features']); ?>&target_scenario=<?php echo urlencode($row['target_scenario']); ?>" class="purchase-btn">Purchase Policy</a>

        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; 2024 InsuraSync Company. All rights reserved.</p>
</footer>

</body>
</html>

<?php 
// Start session to access user ID
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Database credentials
    $servername = "localhost";
    $username = "root"; // Your MySQL username
    $password = ""; // Your MySQL password
    $dbname = "fyp_db"; // Your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to fetch the user's full name and active policy using the user ID
    $sql = "SELECT full_name, active_policy FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);

    // Initialize variables
    $user_name = "User";
    $active_policy = null;

    // Check if a match is found
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
        $user_name = $user['full_name']; // Use 'full_name' column
        $active_policy = $user['active_policy']; // Use 'active_policy' column
    }

    // Query to check if there is an active claim with pending status
    $claim_status_sql = "SELECT claim_status FROM claims WHERE user_id = '$user_id' AND policy_name = '$active_policy' ORDER BY claim_id DESC LIMIT 1";
    $claim_status_result = $conn->query($claim_status_sql);
    $claim_status = null;

    // If a claim exists for the user, fetch the claim status
    if ($claim_status_result->num_rows > 0) {
        $claim_data = $claim_status_result->fetch_assoc();
        $claim_status = $claim_data['claim_status'];
    }

    // Query to check if the user has any past claims (regardless of status)
    $past_claims_sql = "SELECT claim_id FROM claims WHERE user_id = '$user_id'";
    $past_claims_result = $conn->query($past_claims_sql);
    $has_past_claims = $past_claims_result->num_rows > 0;

    // Close the connection
    $conn->close();
} else {
    // User is not logged in, redirect to login page
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            position: relative; /* Set position to allow overlay layering */
            color: #333;

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


        /* Header */
        header {
            /* background-color: rgba(0, 0, 0, 0.8); */
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
            color: #a1bdc5;
        }

        /* Profile and Search Icons */
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

        /* Container */
        
        .container {
    background-color: rgba(0, 0, 0, 0.8); /* Same color as the navbar */
    color: white; /* Ensures text is readable against a dark background */
    padding: 30px; /* Adds inner spacing for a clean look */
    border-radius: 8px; /* Optional: Adds rounded corners for aesthetic */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Optional: Adds a subtle shadow effect */
    max-width: 1200px;
    margin: 50px auto; /* Centers the container */
    text-align: center; /* Aligns text in the container */
}



        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .dashboard-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .section {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 48%;
            box-sizing: border-box;
            transition: transform 0.3s, box-shadow 0.3s;
            color: black;
        }

        .section:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .section h3 {
            margin-top: 0;
            color: #333;
        }

        .arrow-container {
            display: inline-block;
            padding: 5px;
        }

        .arrow {
            font-size: 30px;
            margin-top: 15px;
            display: inline-block;
            cursor: pointer;
            transition: transform 0.3s ease;
            color: black;
        }

        .arrow:hover {
            transform: translateX(10px);
        }

        .btn-link {
            text-decoration: none;
            color: inherit;
        }

        /* Footer */
        footer {
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;

            background-color: rgba(0, 0, 0, 0.8);
            
        }
    </style>
</head>
<body>

<header>
    <h1>InsuraSync</h1>
    <nav>
        <a href="#">Home</a>
        <a href="#">About Us</a>
        <a href="#">Contact Us</a>
    </nav>
    <div class="search-profile">
    <span class="icon">🔔</span>
    <span class="icon profile-icon">👤</span>
    <div class="dropdown">
        <div>
            <a href="profile.php?user_id=<?php echo urlencode($user_id); ?>" style="text-decoration: none; color: inherit;">My Profile</a>
        </div>
        <div>
        <a href="landing_page1.php" style="text-decoration: none; color: inherit;">Logout</a>    
        </div>
    </div>
</div>

</header>

<div class="container">
    <div class="welcome-message">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>You are successfully logged in. Choose an option below:</p>
    </div>

    <div class="dashboard-content">
        <!-- Explore Policies -->
        <div class="section">
            <h3>Explore Policies</h3>
            <p>Discover more about available insurance policies.</p>
            <a href="policies.php?user_id=<?php echo urlencode($user_id); ?>" class="btn-link">
                <div class="arrow-container">
                    <i class="fas fa-arrow-right arrow"></i>
                </div>
            </a>
        </div>

        <!-- Active Policy -->
        <div class="section">
            <h3>Active Policy</h3>
            <?php if ($active_policy): ?>
                <p><strong>Policy Name:</strong> <?php echo htmlspecialchars($active_policy); ?></p>
                <p>Your active policy details are available.</p>
                <a href="view_personal_policy.php?user_id=<?php echo urlencode($user_id); ?>&policy_name=<?php echo urlencode($active_policy); ?>" class="btn-link">
                    <i class="fas fa-arrow-right arrow"></i>
                </a>
            <?php else: ?>
                <p>You don't have an active policy yet. Explore policies to get started.</p>
            <?php endif; ?>
        </div>

        <!-- File Insurance Claim -->
        <!-- File Insurance Claim -->
        <div class="section">
    <h3>File Insurance Claim</h3>
    <?php if ($active_policy): ?>
        <?php if ($claim_status === 'rejected' || $claim_status === null || $claim_status === 'approved'): ?>
            <p>If you have been in an accident, feel free to file a claim.</p>

            <a href="file_claim.php?user_id=<?php echo urlencode($user_id); ?>&policy_name=<?php echo urlencode($active_policy); ?>" class="btn-link">
                <i class="fas fa-arrow-right arrow"></i>
            </a>
        <?php else: ?>
            <p>We are processing your claims. We will get back to you shortly.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>You don't have an active policy yet. Explore policies to get started.</p>
    <?php endif; ?>
</div>



        <!-- View All Claims -->
        <div class="section">
            <h3>View All Claims</h3>
            <p>Click below to view all filed claims.</p>
            <?php if ($has_past_claims): ?>
                <a href="view_claim_status.php?user_id=<?php echo urlencode($user_id); ?>&policy_name=<?php echo urlencode($active_policy); ?>" class="btn-link">
                    <i class="fas fa-arrow-right arrow"></i>
                </a>
            <?php else: ?>
                <p>No claims available. File your first claim to get started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2024 InsuraSync. All rights reserved.</p>
</footer>

</body>
</html>

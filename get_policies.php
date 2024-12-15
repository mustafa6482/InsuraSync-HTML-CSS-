<?php
// Enable error reporting to debug any issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Step 1: Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch policies from the database
$sql = "SELECT policy_name, coverage_type FROM policies";
$result = $conn->query($sql);

// Step 3: Check if there are any policies
if ($result->num_rows > 0) {
    // Step 4: Loop through the results and create a card for each policy
    while($row = $result->fetch_assoc()) {
        echo '<div class="card">';
        echo '<h3>' . htmlspecialchars($row["policy_name"]) . '</h3>';
        echo '<p>' . htmlspecialchars($row["coverage_type"]) . '</p>';
        // Wrap the button inside the link to make it clickable
        echo '<a href="policy_details.php?id=' . urlencode($row["policy_name"]) . '&user_id=' . urlencode($_GET['user_id']) . '">';
        
        // Add the Learn More button inside the anchor tag
        echo '<button class="learn-more-btn" style = "margin-top: 30px;">Learn More</button>';
        echo '</a>';
        echo '</div>';
    }
    
} else {
    echo "<p>No policies found.</p>";
}

// Close the database connection
$conn->close();
?>

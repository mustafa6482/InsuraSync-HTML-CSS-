<?php
// Start session to retain user ID
session_start();

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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // SQL query to check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    // Check if a match is found
    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();

        // Store the user ID in session
        $_SESSION['user_id'] = $user['id']; // Assuming the user table has a column 'id'

        // Login successful, redirect to user dashboard
        header("Location: user_dashboard.php");
        exit(); // Ensure no further code is executed after the redirect
    } else {
        // Login failed
        echo "Invalid email or password!";
    }

    // Close connection
    $conn->close();
}
?>

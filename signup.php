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
    $fullName = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the email already exists
    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        // Redirect to the signup page with a URL parameter for the error
        header("Location: signup.html?error=Email already registered!");
        exit();
    } else {
        // Insert the new user into the database
        $sql = "INSERT INTO users (full_name, email, password) VALUES ('$fullName', '$email', '$password')";

        if ($conn->query($sql) === TRUE) {
            // Retrieve the user ID of the newly created user
            $user_id = $conn->insert_id;

            // Store the user ID in session
            $_SESSION['user_id'] = $user_id;

            // Redirect to user dashboard
            header("Location: user_dashboard.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    }

    // Close connection
    $conn->close();
}
?>

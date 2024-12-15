<?php
// Start session to access user ID
session_start();

// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "fyp_db";

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$update_success = $update_error = $cnic_error = "";
$user = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Create database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $errors = [];

    // CNIC validation
    $cnic = sanitize_input($_POST['cnic']);
    if (!preg_match('/^\d{13}$/', $cnic)) {
        $errors[] = "CNIC must be a 13-digit number.";
    }

    // Date of Birth validation
    $dob = sanitize_input($_POST['dob']);
    if (!empty($dob)) {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if ($dobDate && $dobDate <= new DateTime()) {
            $currentDate = new DateTime();
            $age = $currentDate->diff($dobDate)->y;
            if ($age < 18) {
                $errors[] = "You must be at least 18 years old.";
            }
        } else {
            $errors[] = "Invalid Date of Birth. Ensure the date is in the past and in YYYY-MM-DD format.";
        }
    } else {
        $errors[] = "Date of Birth is required.";
    }

    // Proceed with update if no errors
    if (empty($errors)) {
        // Prepare update statement
        $update_sql = "UPDATE users SET 
            full_name = ?, 
            cnic = ?, 
            gender = ?, 
            age = ?, 
            monthly_income = ?, 
            area = ?, 
            city = ?, 
            dob = ?, 
            is_senior_citizen = ?, 
            street_number = ?, 
            zip_code = ?, 
            vehicle_company = ?, 
            vehicle_name = ?, 
            vehicle_model_year = ?, 
            is_vehicle_EV = ?, 
            vehicle_reg = ? 
            WHERE id = ?";

        $stmt = $conn->prepare($update_sql);

        // Prepare input values
        $full_name = sanitize_input($_POST['full_name']);
        $gender = $_POST['gender'] == 'male' ? 'm' : 'f';
        $age = sanitize_input($_POST['age']);
        $monthly_income = sanitize_input($_POST['monthly_income']);
        $area = sanitize_input($_POST['area']);
        $city = sanitize_input($_POST['city']);
        $is_senior_citizen = isset($_POST['is_senior_citizen']) ? 1 : 0;
        $street_number = sanitize_input($_POST['street_number']);
        $zip_code = sanitize_input($_POST['zip_code']);
        $vehicle_company = sanitize_input($_POST['vehicle_company']);
        $vehicle_name = sanitize_input($_POST['vehicle_name']);
        $vehicle_model_year = sanitize_input($_POST['vehicle_model_year']);
        $is_vehicle_EV = isset($_POST['is_vehicle_EV']) ? 1 : 0;
        $vehicle_reg = sanitize_input($_POST['vehicle_reg']);

        // Bind parameters
        $stmt->bind_param(
            "ssssssssssssssssi", 
            $full_name, $cnic, $gender, $age, $monthly_income, 
            $area, $city, $dob, $is_senior_citizen, $street_number, 
            $zip_code, $vehicle_company, $vehicle_name, $vehicle_model_year, 
            $is_vehicle_EV, $vehicle_reg, $user_id
        );

        // Execute update
        if ($stmt->execute()) {
            $update_success = "Profile updated successfully!";
            // Refresh user data after update
            $user = array_merge($user, $_POST);
            $user['gender'] = $gender;
        } else {
            $update_error = "Update failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - InsuraSync</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('bg.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #333;
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
            position: sticky;
            z-index: 1000;
            top: 0;
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

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
        }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-title h2 {
            margin: 0;
            color: #333;
        }

        .form-title p {
            margin-top: 5px;
            font-size: 14px;
            color: #555;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #007BFF;
            outline: none;
        }

        .form-group input[type="submit"] {
            background-color: #000;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        .form-group input[type="submit"]:hover {
            background-color: #444;
        }

        .form-group p {
            text-align: center;
        }

        .form-group p a {
            color: #007BFF;
            text-decoration: none;
        }

        .form-group p a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        /* Previous CSS remains the same */
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
            text-align: center;
        }
        .masked-cnic {
    font-family: 'Courier', monospace;  /* Makes the asterisks look uniform */
    letter-spacing: 1px;  /* Adds space between the asterisks */
}

.masked-cnic::before {
    content: '*********************';  /* Length matches the 13-digit CNIC */
    visibility: hidden; /* Makes the 'actual' value hidden */
}

    </style>
</head>
<body>

<header>
    <h1>InsuraSync</h1>
    <nav>
        <a href="user_dashboard.php">Home</a>
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
    <div class="form-title">
        <h2>Edit Profile</h2>
        <p>Update your information below</p>
    </div>

    <?php 
    // Display error messages
    if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php 
    // Display success message
    if (!empty($update_success)): ?>
        <div class="success-message">
            <?php echo $update_success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="cnic">CNIC (13 digits):</label>
            <input type="text" id="cnic" name="cnic" 
                   value="<?php echo htmlspecialchars($user['cnic'] ?? ''); ?>" required>
        </div>
            

        <div class="form-group">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php echo ($user['gender'] == 'm') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($user['gender'] == 'f') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label for="age">Age:</label>
            <input type="number" id="age" name="age" 
                   value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="monthly_income">Monthly Income:</label>
            <input type="number" id="monthly_income" name="monthly_income" 
                   value="<?php echo htmlspecialchars($user['monthly_income'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="area">Area:</label>
            <input type="text" id="area" name="area" 
                   value="<?php echo htmlspecialchars($user['area'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" 
                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" 
                   value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" required>
        </div>
        

        <div class="form-group">
            <label for="is_senior_citizen">Senior Citizen:</label>
            <input type="checkbox" id="is_senior_citizen" name="is_senior_citizen" 
                   <?php echo ($user['is_senior_citizen'] == 1) ? 'checked' : ''; ?>>
        </div>

        <div class="form-group">
            <label for="is_vehicle_EV">Electric Vehicle:</label>
            <input type="checkbox" id="is_vehicle_EV" name="is_vehicle_EV" 
                   <?php echo ($user['is_vehicle_EV'] == 1) ? 'checked' : ''; ?>>
        </div>

        <div class="form-group">
            <label for="street_number">Street Number:</label>
            <input type="text" id="street_number" name="street_number" 
                   value="<?php echo htmlspecialchars($user['street_number'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="zip_code">Zip Code:</label>
            <input type="text" id="zip_code" name="zip_code" 
                   value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="vehicle_company">Vehicle Company:</label>
            <input type="text" id="vehicle_company" name="vehicle_company" 
                   value="<?php echo htmlspecialchars($user['vehicle_company'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="vehicle_name">Vehicle Name:</label>
            <input type="text" id="vehicle_name" name="vehicle_name" 
                   value="<?php echo htmlspecialchars($user['vehicle_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="vehicle_model_year">Vehicle Model Year:</label>
            <input type="text" id="vehicle_model_year" name="vehicle_model_year" 
                   value="<?php echo htmlspecialchars($user['vehicle_model_year'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="vehicle_reg">Vehicle Registration:</label>
            <input type="text" id="vehicle_reg" name="vehicle_reg" 
                   value="<?php echo htmlspecialchars($user['vehicle_reg'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <input type="submit" value="Update Profile">
        </div>
    </form>
</div>

<footer>
    <p>© 2024 InsuraSync</p>
</footer>

</body>
</html>

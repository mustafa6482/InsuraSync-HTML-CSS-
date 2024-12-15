<?php
// Assuming user_id and policy_name are passed as query parameters
$user_id = $_GET['user_id'];
$policy_name = $_GET['policy_name'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp_db";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling the claim form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $accident_date = $_POST['accident_date'];
    $accident_description = $_POST['accident_description'];
    $claim_amount = $_POST['claim_amount'];
    $documents = $_FILES['documents'];
    $cnic = $_POST['cnic']; // Get CNIC from form
    $vehicle_reg = $_POST['vehicle_reg']; // Get Vehicle Registration Number from form

    // Validate accident date
    if (!empty($accident_date)) {
        $accidentDateObj = DateTime::createFromFormat('Y-m-d', $accident_date);
        if ($accidentDateObj) {
            $currentDate = new DateTime();
            $interval = $currentDate->diff($accidentDateObj)->days;

            if ($accidentDateObj > $currentDate) {
                $errors[] = "Accident date cannot be in the future.";
            } elseif ($interval > 10) {
                $errors[] = "Accident date cannot be more than 10 days old.";
            }
        } else {
            $errors[] = "Invalid accident date format. Use YYYY-MM-DD.";
        }
    } else {
        $errors[] = "Accident date is required.";
    }

    // Process the uploaded documents (if any)
    $document_path = "";
    if ($documents['error'] == 0) {
        // Move the uploaded file to the desired location
        $document_path = 'uploads/' . basename($documents['name']);
        move_uploaded_file($documents['tmp_name'], $document_path);
    }

    // Set the current date for file_date
    $file_date = date("Y-m-d");

    // Set the claim status to "pending" by default
    $claim_status = "pending";

    // SQL query to fetch the user details by user_id
    $sql = "SELECT cnic, vehicle_reg FROM users WHERE id = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the user data
        $user_data = $result->fetch_assoc();

        // Check for discrepancies in CNIC and Vehicle Registration Number
        if ($user_data['cnic'] !== $cnic || $user_data['vehicle_reg'] !== $vehicle_reg) {
            $claim_status = "rejected"; // Set status to rejected if there's a discrepancy
        }
    } else {
        $errors[] = "User not found.";
    }

    // If no errors, insert the claim
    if (empty($errors)) {
        // SQL query to insert the claim into the claims table
        $sql = "INSERT INTO claims (user_id, policy_name, accident_date, accident_description, claim_amount, claim_status, file_date, documents) 
                VALUES ('$user_id', '$policy_name', '$accident_date', '$accident_description', '$claim_amount', '$claim_status', '$file_date', '$document_path')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to a success page or back to the dashboard
            header("Location: user_dashboard.php?claim_status=" . $claim_status);
            exit();
        } else {
            $errors[] = "Error: " . $conn->error;
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File a Claim</title>
    <style>
        html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    
}
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-y: auto;
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.0);
            z-index: -1;
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
            color: #a1bdc5;
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

        .main-content {
            padding: 30px;
            color: #fff;
            display: flex;
            gap: 20px;
            flex-grow: 1;
            overflow-y: auto; /* Enable scrolling */
            max-height: calc(100vh - 120px); /* Adjust for header and footer */
            position: relative;
        }



        .instructions {
            flex: 1;
            min-width: 300px;
            font-family: 'Arial', sans-serif;
            font-size: 18px;
            line-height: 1.8;
            color: #f1f1f1;
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: left;
        }

        .instructions h2 {
            font-family: 'Arial', sans-serif;
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .instructions ul {
            list-style-type: square;
            margin-left: 20px;
            font-size: 18px;
        }

        .instructions li {
            margin-bottom: 12px;
        }

        .form-container {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: 0;
            box-sizing: border-box;
        }

        .form-container h3 {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-container label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #555;
        }

        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-container input:focus,
        .form-container textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #000;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #444;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            padding: 12px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #d32f2f;
        }

        footer {
    background-color: #000;
    color: white;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    bottom: 0;
    width: 100%;
    z-index: 10;
}

        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
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

<div class="main-content">
    <div class="instructions">
        <h2>Policy: <?php echo htmlspecialchars($policy_name); ?></h2>
        <p>Here are the instructions or guidelines to file your claim. Please fill in all the required fields below carefully.</p>
        <ul>
            <li>Ensure the claim is for the same vehicle that the policy is registered under.</li>
            <li>Provide accurate and truthful details to avoid fraud. False claims will be rejected.</li>
            <li>Upload valid supporting documents to substantiate your claim.</li>
            <li>Double-check the CNIC and vehicle registration number for accuracy.</li>
            <li>Claims with discrepancies or missing details may face delays or rejection.</li>
            <li>Contact customer support if you encounter any issues while filing your claim.</li>
            <li>Make sure the accident description is clear and concise.</li>
            <li>All claims are subject to verification and approval by the insurer.</li>
        </ul>
    </div>

    <div class="form-container">
        <h3>Submit Your Claim</h3>
        <?php if (!empty($errors)): ?>
            <div class="error-messages" style="color: red;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form id="claim-form" action="file_claim.php?user_id=<?php echo urlencode($user_id); ?>&policy_name=<?php echo urlencode($policy_name); ?>" method="POST" enctype="multipart/form-data">
            <label for="accident_date">Accident Date</label>
            <input type="date" id="accident_date" name="accident_date" required>

            <label for="accident_description">Accident Description</label>
            <textarea id="accident_description" name="accident_description" rows="5" required></textarea>

            <label for="claim_amount">Claim Amount</label>
            <input type="number" id="claim_amount" name="claim_amount" step="0.01" required>

            <label for="documents">Supporting Documents (if any)</label>
            <input type="file" id="documents" name="documents" accept=".jpg,.jpeg,.png,.pdf">

            <label for="cnic">CNIC</label>
            <input type="text" id="cnic" name="cnic" required>

            <label for="vehicle_reg">Vehicle Registration Number</label>
            <input type="text" id="vehicle_reg" name="vehicle_reg" required>

            <button type="submit">Submit Claim</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2024 InsuraSync. All rights reserved.</p>
</footer>
</body>
</html>

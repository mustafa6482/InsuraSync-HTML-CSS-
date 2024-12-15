<?php
// Start session for storing user information (if needed)
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to calculate the score for each policy based on user input (from personalized_policy.php)
function calculate_score($policy, $user_input) {
    $score = 0;

    // Scoring for coverage type (most important)
    if ($policy['coverage_type'] == $user_input['Coverage Type']) {
        $score += 30;  // High score for exact match
    } else {
        $score -= 15;  // Penalty for mismatch
    }

    // Scoring for additional features (second most important)
    if (strpos($policy['additional_features'], $user_input['Additional Features']) !== false) {
        $score += 25;  // Significant score for matching features
    } else {
        $score -= 10;  // Penalty if the feature doesn't match
    }

    // Scoring for coverage amount, deductible, and premium (equally important)
    if (abs($policy['coverage_amount'] - $user_input['Coverage Amount ($)']) <= 5000) {
        $score += 15;
    }

    if (abs($policy['deductible'] - $user_input['Deductible ($)']) <= 200) {
        $score += 15;
    }

    if ($policy['premium'] <= $user_input['Premium ($)']) {
        $score += 15;
    }

    return $score;
}

// Function to calculate cosine similarity between two vectors (from similar_policies.php)
function cosine_similarity($vector1, $vector2) {
    $dot_product = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;

    foreach ($vector1 as $key => $value) {
        if (isset($vector2[$key])) {
            $dot_product += $value * $vector2[$key];
        }
    }

    foreach ($vector1 as $value) {
        $magnitude1 += pow($value, 2);
    }

    foreach ($vector2 as $value) {
        $magnitude2 += pow($value, 2);
    }

    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);

    return $magnitude1 && $magnitude2 ? $dot_product / ($magnitude1 * $magnitude2) : 0;
}

// Function to calculate similarity score between users based on shared attributes (from similar_policies.php)
function calculate_similarity($user1, $user2) {
    $similarity = 0;
    $threshold = 5000; // Margin for income comparison

    if (abs($user1['monthly_income'] - $user2['monthly_income']) <= $threshold) {
        $similarity += 1;
    }

    $attributes1 = [
        'is_senior_citizen' => (int) $user1['is_senior_citizen'],
        'city' => $user1['city'],
        'vehicle_name' => $user1['vehicle_name'],
        'vehicle_company' => $user1['vehicle_company'],
    ];

    $attributes2 = [
        'is_senior_citizen' => (int) $user2['is_senior_citizen'],
        'city' => $user2['city'],
        'vehicle_name' => $user2['vehicle_name'],
        'vehicle_company' => $user2['vehicle_company'],
    ];

    $vector1 = [];
    $vector2 = [];
    foreach ($attributes1 as $key => $value) {
        if (is_numeric($value)) {
            $vector1[$key] = $value;
            $vector2[$key] = (int) $attributes2[$key];
        } else {
            $vector1[$key] = $value === $attributes2[$key] ? 1 : 0;
            $vector2[$key] = $value === $attributes2[$key] ? 1 : 0;
        }
    }

    $similarity += cosine_similarity($vector1, $vector2);

    return $similarity;
}

// Check if form is submitted for personalized recommendations
$recommendations = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input from the form
    $user_input = [
        'Coverage Type' => $_POST['coverage_type'],
        'Coverage Amount ($)' => $_POST['coverage_amount'],
        'Premium ($)' => $_POST['premium'],
        'Deductible ($)' => $_POST['deductible'],
        'Policy Term (Years)' => $_POST['policy_term'],
        'Additional Features' => $_POST['additional_features'],
    ];

    // Query to fetch policies from the database
    $sql = "SELECT * FROM policies";
    $result = $conn->query($sql);

    // Array to hold policies and their scores
    $policies_with_scores = [];

    if ($result->num_rows > 0) {
        // Fetch policies from the database and calculate scores
        while ($row = $result->fetch_assoc()) {
            $score = calculate_score($row, $user_input);
            $policies_with_scores[] = [
                'policy_name' => $row['policy_name'],
                'score' => $score,
                'PolicyID' => $row['PolicyID'],
                'coverage_type' => $row['coverage_type'],
                'premium' => $row['premium'],
                'coverage_amount' => $row['coverage_amount'],
                'deductible' => $row['deductible'],
            ];
        }

        usort($policies_with_scores, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Get top recommendations
        $recommendations = array_slice($policies_with_scores, 0, 2);
    }
}

// Retrieve similar policies based on similar users (if any)
$user_id = $_GET['user_id'];
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$current_user = $user_result->fetch_assoc();

// Fetch all users except the current one
$sql_users = "SELECT * FROM users WHERE id != ?";
$stmt = $conn->prepare($sql_users);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

$similar_users = [];
while ($other_user = $users_result->fetch_assoc()) {
    $similarity_score = calculate_similarity($current_user, $other_user);
    if ($similarity_score >= 1.5) {
        $similar_users[] = [
            'user_id' => $other_user['id'],
            'similarity_score' => $similarity_score,
            'active_policy' => $other_user['active_policy'],
        ];
    }
}

if (empty($similar_users)) {
    echo "<h2>No similar users found to recommend policies.</h2>";
} else {
    $recommended_policies = [];
    foreach ($similar_users as $similar_user) {
        $policy_name = $similar_user['active_policy'];
        if (!in_array($policy_name, $recommended_policies)) {
            $recommended_policies[] = $policy_name;
        }
    }

    if (count($recommended_policies) > 0) {
        echo "<h2>Recommended Policies Based on Similar Users</h2>";
        foreach ($recommended_policies as $policy_name) {
            $policy_sql = "SELECT * FROM policies WHERE policy_name = ?";
            $stmt = $conn->prepare($policy_sql);
            $stmt->bind_param("s", $policy_name);
            $stmt->execute();
            $policy_result = $stmt->get_result();
            if ($policy_result->num_rows > 0) {
                $policy = $policy_result->fetch_assoc();
                echo "<div class='card'>";
                echo "<h3>" . htmlspecialchars($policy['policy_name']) . "</h3>";
                echo "<p><strong>Coverage Type:</strong> " . htmlspecialchars($policy['coverage_type']) . "</p>";
                echo "<p><strong>Premium:</strong> $" . number_format($policy['premium']) . "</p>";
                echo "<p><strong>Coverage Amount:</strong> $" . number_format($policy['coverage_amount']) . "</p>";
                echo "<p><strong>Deductible:</strong> $" . number_format($policy['deductible']) . "</p>";
                echo "<a href='policy_details.php?id=" . urlencode($policy['policy_name']) . "&user_id=" . urlencode($user_id) . "' class='view-details'>View Details and Purchase</a>";
                echo "</div>";
            }
        }
    } else {
        echo "<h2>No policies found for similar users.</h2>";
    }
}

// Close the database connection
$conn->close();
?>

<!-- HTML and JavaScript for displaying policies, form, and buttons (same as before) -->

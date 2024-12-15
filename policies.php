<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure full height */
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

        .content {
            flex: 1; /* Take up available space */
            padding-bottom: 80px; /* Give space for the footer */
        }

        /* New Styles */
        .header-container {
    display: flex;
    justify-content: space-between; /* Keeps button to the right */
    align-items: center;
    padding: 20px;
    background-color: #000; /* Optional: Set a background to match your design */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for a subtle effect */
    margin: 20px 50px; /* Adjust spacing around the container */
    border-radius: 8px; /* Optional: Add rounded corners */
}

.header-container h2 {
    margin: 0;
    text-align: left; /* Aligns the text to the left */
    font-size: 22px;
    color: #fff;
    flex: 1; /* Ensures the text takes up remaining space, pushing the button to the far right */
}


        .personalized-btn {
            padding: 15px 30px;
            background-color: #FFF;
            color: black;
            font-size: 14px;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-left: 20px; /* Add spacing between button and heading */
        }

        .personalized-btn:hover {
            background-color: #000;
            color: white;
        }

        /* Updated Card Styles */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            padding-top: 30px;
        }

        .card {
            background-color: black; /* Darker card background */
            color: #fff; /* White text */
            border: 1px solid #444;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3); /* Soft shadow */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 250px; /* Fix card height */
            width: 350px; /* Fix card width */
            min-width: 300px; /* Ensure cards don’t get too small */
            max-width: 350px; /* Limit max width */
        }

        .card:hover {
            transform: translateY(-10px); /* Slight lift on hover */
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.4); /* Stronger shadow on hover */
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #fff;
        }

        .card p {
            font-size: 16px;
            margin-bottom: 15px;
            color: #ccc;
        }

        .card .target-scenario {
            font-size: 14px;
            margin-bottom: 20px;
            color: #aaa;
        }
        .card a {
    text-decoration: none;   /* Remove the underline */
    color: #fff;             /* Set the text color to white */
}

    .card a:hover {
        color: #ccc;             /* Optional: change the color on hover */
    }


        .learn-more-btn {
            padding: 12px 25px;
            background-color: #fff; /* White background for the button */
            color: #000; /* Black text */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .learn-more-btn:hover {
            background-color: #000;
            color: #fff;
        }

        /* Make the cards responsive */
        @media (max-width: 768px) {
            .card-container {
                grid-template-columns: 1fr 1fr; /* 2 cards per row on medium screens */
            }

            .card {
                height: 320px; /* Adjust height for medium screens */
            }
        }

        @media (max-width: 480px) {
            .card-container {
                grid-template-columns: 1fr; /* 1 card per row on small screens */
            }

            .card {
                height: 320px; /* Adjust height for small screens */
            }
        }

        /* Sticky Footer Styles */
        footer {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: sticky;
            bottom: 0;
            width: 100%;
            left: 0;
            right: 0;
        }
    </style>
</head>
<body>

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

<div class="header-container">
    <h2>Dive into InsuraSync's Policies</h2>
    <a href="personalized_policy.php?user_id=<?php echo urlencode($_GET['user_id']); ?>" class="personalized-btn">Get Personalized Policy</a>
</div>

<!-- Content Section -->
<div class="content">
    <div class="card-container">
        <!-- PHP Include to Fetch and Display Policies -->
        <?php include('get_policies.php'); ?>
    </div>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2024 InsuraSync. All rights reserved.</p>
</footer>

</body>
</html>

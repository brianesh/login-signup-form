<?php
// Start the session
session_start();

// Assuming the username is stored in a session variable
if (!isset($_SESSION['username'])) {
    // If the user is not logged in, redirect to the login page (adjust the URL as needed)
    header("Location: login.php");
    exit;
}

// Get the username from the session
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <script>
        // Function to determine the greeting based on the user's local time
        function getGreeting() {
            var currentHour = new Date().getHours();
            var greeting;

            if (currentHour < 12) {
                greeting = "Good morning";
            } else if (currentHour < 18) {
                greeting = "Good afternoon";
            } else {
                greeting = "Good evening";
            }

            return greeting;
        }

        // Function to display the greeting
        function displayGreeting(username) {
            var greeting = getGreeting();
            document.getElementById("greeting").textContent = greeting + ", " + username + "!";
        }
    </script>
</head>
<body onload="displayGreeting('<?php echo htmlspecialchars($username); ?>')">
    <h1 id="greeting"></h1>
</body>
</html>

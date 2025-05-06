<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}
require_once("includes/db_connect.php");
require_once("includes/functions.php");

if (isset($_GET['id'])) {
    $jobId = $_GET['id'];
    $jobDetails = getJobListingDetails($conn, $jobId); //get the job details
     if (!$jobDetails) {
        // Handle the case where the job listing doesn't exist
        header("Location: error.php?message=Job listing not found"); // Create an error.php page
        exit;
    }
} else {
     header("Location: error.php?message=Invalid job listing ID"); //error page
     exit;
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <?php displayJobDetails($jobDetails); ?>
        <p><a href="#" onclick="window.history.back();">Back to Listings</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
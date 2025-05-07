<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employer') {
    header("Location: login.php"); // Redirect if not logged in or not an employer
    exit;
}
require_once("includes/db_connect.php");
require_once("includes/functions.php");

$jobListings = getAllJobListings($conn); //get all job listing

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, Employer!</h2>
        <p>Here are the job listings:</p>
         <ul class="job-listings">
            <?php if (empty($jobListings)): ?>
                <li>No job listings available.</li>
            <?php else: ?>
                <?php foreach ($jobListings as $job): ?>
                    <li>
                         <a href="job_details.php?id=<?php echo $job['id']; ?>">
                            <strong><?php echo htmlspecialchars($job['title']); ?></strong> -
                            <?php echo htmlspecialchars($job['company_name']); ?> (
                            <?php echo date('F j, Y', strtotime($job['date_posted'])); ?>)
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <p><a href="employer_add_job.php">Post a New Job Listing</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
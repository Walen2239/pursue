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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Worker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <style>
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-4 bg-dark">
                <div class="text-center mb-4">
                    <img src="images/Pursue Logo1.png" alt="Logo" class="img-fluid" style="max-width: 100%;" />
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link text-white" href="/profile">Profile</a>
                    <a class="nav-link active fw-bold text-white" href="#">All Category</a>
                    <a class="nav-link text-white" href="#">Listing 1</a>
                    <a class="nav-link text-white" href="#">Listing 2</a>
                    <a class="nav-link text-white" href="#">Listing 3</a>
                    <a class="nav-link text-white" href="#">About Us</a>
                </nav>
                <div class="mt-5 text-center">
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                </div>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center g-4 mt-2">
                    <div class="container">
                    <?php displayJobDetails($jobDetails); ?>
                    <p><a href="#" onclick="window.history.back();">Back to Listings</a></p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>

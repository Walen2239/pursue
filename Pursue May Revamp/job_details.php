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
    <link rel="stylesheet" href="css/pursue_style.css" />
    <style>
        .sidebar {
            position: fixed; /* Make it fixed */
            top: 0; /* Align to the top */
            left: 0; /* Align to the left */
            height: 100vh; /* Full viewport height */
            overflow-y: auto; /* Enable vertical scroll if needed */
            width: 250px; /* Set the width of the sidebar */
            z-index: 1000; /* Ensure it's above other elements */
            background-color: #331a5e; /* Change sidebar background color */
        }
        .main-content {
            margin-left: 250px; /* Add margin to the main content to prevent overlap */
            padding: 20px;
        }
       .job-details-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add shadow for a similar effect */
            color: #000;
        }
        .job-details-box img{
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .back{
            text-align:right;
            margin-bottom: 2rem;
        }

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-4">
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
                    <div class="col-md-7 mx-auto">
                        <div class="job-details-box" >
                            <div class="back">
                            <button type="button" href="#" onclick="window.history.back();"class="btn btn-outline-dark btn-lg">Back to Listings</button>
                            </div>
                            <?php 
                                if(isset($jobDetails)){
                                    $companyName = htmlspecialchars($jobDetails['company_name']);
                                    $pngPath = "images/" . $companyName . ".png";
                                    $jpgPath = "images/" . $companyName . ".jpg";
                                        if (file_exists($pngPath)) {
                                             echo '<div style="text-align:center;"><img src="' . $pngPath . '" alt="Company Logo" /></div>';
                                        } elseif (file_exists($jpgPath)) {
                                             echo '<div style="text-align:center;"><img src="' . $jpgPath . '" alt="Company Logo" /></div>';
                                        } else {
                                             echo '<div style="text-align:center;">No Company Logo Available</div>'; // Or show a default image
                                        }
                                    echo '<h2 style="margin-top: 1.2rem;">'.htmlspecialchars($jobDetails['title']).'</h2>';
                                    echo '<p><strong>Company Name:</strong> '.htmlspecialchars($jobDetails['company_name']).'</p>';
                                    echo '<p><strong>Location:</strong> '.htmlspecialchars($jobDetails['location']).'</p>';
                                    echo '<p><strong>Job Type:</strong> '.htmlspecialchars($jobDetails['job_type']).'</p>';
                                    echo '<p><strong>Salary:</strong> '.htmlspecialchars($jobDetails['salary']).'</p>';
                                    echo '<p><strong>Description:</strong> '.htmlspecialchars($jobDetails['description']).'</p>';
                                    echo '<p><strong>Date Posted:</strong> '.date('F j, Y', strtotime($jobDetails['date_posted'])).'</p>';
                                }
                            ?>
                        </div>
                    </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



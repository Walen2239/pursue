<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employer') {
    header("Location: login.php");
    exit;
}
require_once("includes/db_connect.php");
require_once("includes/functions.php");

$jobListings = getAllJobListings($conn);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/pursue_style.css" />
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
                    <a class="nav-link text-white" href="#">About Us</a>
                    <a href="employer_add_job.php" style="margin-top:2rem" class="nav-link text-white">New Listing</a>
                    <a href="employer_add_job.php" class="nav-link text-white">Your Listing</a>
                    <a href="logout.php" style="margin-top:2rem" class="nav-link text-red">Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center g-4 mt-2">
                    <h2 class="mb-4 text-center">FEATURED JOB LISTING IN PURSUE WEBSITE</h2>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">Education & Training</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">Healthcare</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">Sales & Marketing</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">IT & Software</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">Hospitality</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="category.html" class="category-box text-decoration-none">
                            <div class="content">
                                <h5 class="mt-3">Accounting & Finance</h5>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row justify-content-center g-4 mt-2">
                    <?php foreach ($jobListings as $job):
                         $companyName = htmlspecialchars($job['company_name']);
                         $imagePath = "images/" . $companyName; //without extension
                         $pngPath =  $imagePath . ".png";
                         $jpgPath =  $imagePath . ".jpg";
                         $jpegPath = $imagePath . ".jpeg";
                    ?>
                        <div class="col-md-4">
                            <a href="job_details.php?id=<?php echo $job['id']; ?>" class="job-box">
                                <div class="d-flex align-items-center">
                                    <?php if (file_exists($pngPath)): ?>
                                        <img src="<?php echo $pngPath; ?>" alt="Company Logo" class="company-logo" />
                                    <?php elseif (file_exists($jpgPath)): ?>
                                        <img src="<?php echo $jpgPath; ?>" alt="Company Logo" class="company-logo" />
                                     <?php elseif (file_exists($jpegPath)): ?>
                                        <img src="<?php echo $jpegPath; ?>" alt="Company Logo" class="company-logo" />
                                    <?php else: ?>
                                        <img src="images/default_logo.png" alt="Company Logo" class="company-logo" />
                                    <?php endif; ?>
                                    <div>
                                        <h5 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                                        <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                    </div>
                                </div>
                                <div class="location"><?php echo htmlspecialchars($job['location']); ?></div>
                                <p class="job-type"><?php echo htmlspecialchars($job['job_type']); ?></p>
                                <small class="post-date">Posted: <?php echo date('F j, Y', strtotime($job['date_posted'])); ?></small>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
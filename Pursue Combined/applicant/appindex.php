<?php
session_start();
require_once '../includes/dbh.php'; 

$jobListings = []; 

$sql = "SELECT listingsID, job_title, company_name, description, location, salary, job_type, date_posted
        FROM listings
        WHERE approval = 1"; // This page (appindex.php) shows ALL approved listings

$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobListings[] = $row; 
        }
    }
} else {
    error_log("Error fetching job listings: " . $conn->error);
}

$conn->close(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Job Portal - Discover Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pursue_style.css" />
    <style>
        .nav-link.text-white {
            color: #fff !important;
        }
        .nav-link.text-red { 
            color: #dc3545 !important; 
        }
        /* You might want a subtle active style for 'All Category' on this page if it's the default */
        .category-box.active {
            border: 2px solid #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        .category-box.active .content h5 {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-4 bg-dark">
                <div class="text-center mb-4">
                    <img src="../images/Pursue Logo1.png" alt="Logo" class="img-fluid" style="max-width: 100%;" />
                </div>
                <nav class="nav flex-column">
                    <?php if (isset($_SESSION['appemail'])): ?>
                        <a class="nav-link text-white" href="appprofile.php">Profile</a>
                        <a class="nav-link text-white" href="../about_us.php">About Us</a>
                        <a class="nav-link active fw-bold text-white" href="appindex.php">All Category</a> 
                        <a class="nav-link text-white" href="applied_listing.php">Applied Listings</a>
                        <a href="../includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                    <?php elseif (isset($_SESSION['recemail'])): ?>
                        <a class="nav-link text-white" href="../about_us.php">About Us</a>
                        <a class="nav-link active fw-bold text-white" href="appindex.php">All Category</a> 
                        <a href="../recruitor/recindex.php" style="margin-top:2rem" class="nav-link text-white">New Listing</a>
                        <a href="../recruitor/reclistings.php" class="nav-link text-white">Your Listing</a>
                        <a href="../includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                    <?php else: ?>
                        <a class="nav-link text-white" href="appsignup.php">Sign Up</a>
                        <a class="nav-link text-white" href="applogin.php">Log In</a>
                        <a class="nav-link text-white" style="margin-top:2rem" href="../about_us.php">About Us</a> 
                        <a class="nav-link active fw-bold text-white" href="appindex.php">All Category</a> 
                    <?php endif; ?>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content">
                <div class="row justify-content-center g-4">
                    <?php
                    $categories = [
                        "Education & Training",
                        "Healthcare",
                        "Sales & Marketing",
                        "IT & Software",
                        "Hospitality",
                        "Finance"
                    ];

                    foreach ($categories as $cat) {
                        echo '<div class="col-md-2">';
                        echo '<a href="category_details.php?category=' . urlencode($cat) . '" class="category-box text-decoration-none">';
                        echo '<div class="content"><h5 class="mt-3">' . htmlspecialchars($cat) . '</h5></div>';
                        echo '</a></div>';
                    }
                    ?>
                </div>
                <div class="row justify-content-center g-4 mt-2">
                    <?php if (!empty($jobListings)): ?>
                        <?php foreach ($jobListings as $job):
                            $companyName = htmlspecialchars($job['company_name']);
                            $imagePath = "../images/" . $companyName;
                            $pngPath = $imagePath . ".png";
                            $jpgPath = $imagePath . ".jpg";
                            $jpegPath = $imagePath . ".jpeg";
                        ?>
                        <div class="col-md-4">
                            <a href="../job_details.php?listingsid=<?php echo htmlspecialchars($job['listingsID']); ?>" class="job-box">
                            <div class="d-flex align-items-center">
                                <?php if (file_exists($pngPath)): ?>
                                    <img src="<?php echo $pngPath; ?>" alt="Company Logo" class="company-logo" />
                                <?php elseif (file_exists($jpgPath)): ?>
                                    <img src="<?php echo $jpgPath; ?>" alt="Company Logo" class="company-logo" />
                                <?php elseif (file_exists($jpegPath)): ?>
                                    <img src="<?php echo $jpegPath; ?>" alt="Company Logo" class="company-logo" />
                                <?php else: ?>
                                    <img src="../images/default_logo.png" alt="Company Logo" class="company-logo" />
                                <?php endif; ?>
                                <div>
                                    <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                    <p class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                </div>
                            </div>
                                <div class="location"><?php echo htmlspecialchars($job['location']); ?></div>
                                <p class="job-type"><?php echo htmlspecialchars($job['job_type']); ?></p>
                                <small class="post-date">Posted: <?php echo date('F j, Y', strtotime($job['date_posted'])); ?></small>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center w-100">No approved job listings available at the moment.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
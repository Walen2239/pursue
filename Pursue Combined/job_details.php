<?php
session_start();

require_once "includes/dbh.php";

$jobDetails = null;
$listingsID = null; // Initialize listingsID for use in the form
$hasApplied = false; // Initialize hasApplied flag

// Check if listingsID is provided in the URL
if (isset($_GET['listingsid'])) {
    $listingsID = intval($_GET['listingsid']); // Assign to listingsID directly as it's used later

    $sql = "SELECT listingsID, job_title, company_name, description, location, salary, date_posted, job_type, category
            FROM listings
            WHERE listingsID = ? AND approval = 1"; // Added category and approval = 1

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $listingsID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $jobDetails = $result->fetch_assoc();
        }
        $stmt->close();
    }

    // --- NEW LOGIC: Check if applicant is logged in AND has already applied ---
    if (isset($_SESSION['appemail'])) {
        $appID = null;
        if (isset($_SESSION['appID'])) {
            $appID = $_SESSION['appID'];
        } else {
            // Fallback: If appID not in session, fetch it from DB using email
            $appEmail = $_SESSION["appemail"];
            $query = $conn->prepare("SELECT appID FROM applicants WHERE appEmail = ?");
            if ($query) {
                $query->bind_param("s", $appEmail);
                $query->execute();
                $result = $query->get_result();
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $appID = $row['appID'];
                    $_SESSION['appID'] = $appID; // Store for future use
                }
                $query->close();
            } else {
                error_log("Failed to prepare appID query: " . $conn->error);
            }
        }

        if ($appID !== null) {
            $checkAppliedSql = "SELECT COUNT(*) FROM job_applications WHERE appID = ? AND listingsID = ?";
            $checkAppliedStmt = $conn->prepare($checkAppliedSql);
            if ($checkAppliedStmt) {
                $checkAppliedStmt->bind_param("ii", $appID, $listingsID);
                $checkAppliedStmt->execute();
                $checkAppliedResult = $checkAppliedStmt->get_result();
                if ($checkAppliedResult && $checkAppliedResult->fetch_row()[0] > 0) {
                    $hasApplied = true;
                }
                $checkAppliedStmt->close();
            } else {
                error_log("Failed to prepare check applied statement: " . $conn->error);
            }
        }
    }
    // --- END NEW LOGIC ---

} else {
    // If no listingsid is provided, redirect to an error page or appindex
    header("Location: appindex.php?error=invalidlisting"); // Redirect to appindex instead of error.php
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Job Details - <?php echo $jobDetails ? htmlspecialchars($jobDetails['job_title']) : 'Pursue'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/pursue_style.css" />
    <style>
        /* Existing CSS styles */
        .job-details-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #000;
            font-size: 1.3rem;
        }
        .job-details-box img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .back {
            text-align: right;
            margin-bottom: 2rem;
        }
        .nav-link.text-red {
            color: #dc3545 !important;
        }
        /* New styles for apply button container */
        .apply-button-container {
            margin-top: 2rem;
            text-align: center;
        }
        .apply-button-container .btn-applied {
            background-color: #6c757d; /* Grey for applied */
            border-color: #6c757d;
            cursor: not-allowed;
        }
        .apply-button-container .btn-applied:hover {
            background-color: #5a6268;
            border-color: #545b62;
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
                    <?php if (isset($_SESSION['appemail'])): ?>
                        <a class="nav-link text-white" href="applicant/appprofile.php">Profile</a>
                        <a class="nav-link text-white" href="#">About Us</a>
                        <a class="nav-link active fw-bold text-white" href="applicant/appindex.php">All Category</a> 
                        <a class="nav-link text-white" href="applicant/applied_listing.php">Applied Listings</a>
                        <a href="includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                    <?php elseif (isset($_SESSION['recemail'])): ?>
                        <a class="nav-link text-white" href="#">About Us</a>
                        <a class="nav-link active fw-bold text-white" href="applicant/appindex.php">All Category</a> 
                        <a href="recruitor/recindex.php" style="margin-top:2rem" class="nav-link text-white">New Listing</a>
                        <a href="recruitor/reclistings.php" class="nav-link text-white">Your Listing</a>
                        <a href="includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                    <?php else: ?>
                        <a class="nav-link text-white" href="applicant/appsignup.php">Sign Up</a>
                        <a class="nav-link text-white" href="applicant/applogin.php">Log In</a>
                        <a class="nav-link text-white" style="margin-top:2rem" href="#">About Us</a>
                        <a class="nav-link active fw-bold text-white" href="applicant/appindex.php">All Category</a> 
                    <?php endif; ?>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center g-4 mt-2">
                    <div class="container">
                        <div class="col-md-7 mx-auto">
                            <div class="job-details-box"> 
                                <div class="back">
                                    <button type="button" onclick="window.location.href='applicant/appindex.php'" class="btn btn-outline-dark btn-lg">Back to Listings</button>                                </div>
                                <?php if ($jobDetails): ?>
                                    <div style="text-align:center;">
                                        <?php
                                        $companyName = htmlspecialchars($jobDetails['company_name']);
                                        $pngPath = "images/" . $companyName . ".png";
                                        $jpgPath = "images/" . $companyName . ".jpg";
                                        $jpegPath = "images/" . $companyName . ".jpeg";

                                        if (file_exists($pngPath)) {
                                            echo '<img src="' . $pngPath . '" alt="Company Logo" />';
                                        } elseif (file_exists($jpgPath)) {
                                            echo '<img src="' . $jpgPath . '" alt="Company Logo" />';
                                        } elseif (file_exists($jpegPath)) {
                                            echo '<img src="' . $jpegPath . '" alt="Company Logo" />';
                                        } else {
                                            echo '<div style="text-align:center;">No Company Logo Available</div>';
                                        }
                                        ?>
                                    </div>

                                    <h2 style="margin-top: 1.2rem; font-size:2.5rem;"><?php echo htmlspecialchars($jobDetails['job_title']); ?></h2>
                                    <p><strong>Company Name:</strong> <?php echo htmlspecialchars($jobDetails['company_name']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($jobDetails['location']); ?></p>
                                    <p><strong>Job Type:</strong> <?php echo htmlspecialchars($jobDetails['job_type']); ?></p>
                                    <p><strong>Salary:</strong> <?php echo htmlspecialchars($jobDetails['salary']); ?></p>
                                    <p><strong>Description:</strong><br> <?php echo nl2br(htmlspecialchars($jobDetails['description'])); ?></p>
                                    <p><strong>Date Posted:</strong> <?php echo date('F j, Y', strtotime($jobDetails['date_posted'])); ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($jobDetails['category']); ?></p> <?php if (isset($_SESSION['appemail'])): // Only show button if applicant is logged in ?>
                                        <div class="apply-button-container">
                                            <?php if ($hasApplied): ?>
                                                <button class="btn btn-secondary btn-lg btn-applied" disabled>Applied</button>
                                            <?php else: ?>
                                                <form action="includes/apply_job.php" method="POST">
                                                    <input type="hidden" name="listings_id" value="<?php echo htmlspecialchars($listingsID); ?>">
                                                    <button type="submit" class="btn btn-secondary btn-lg">Apply Now</button> </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
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
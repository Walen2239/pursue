<?php
session_start();
require_once '../includes/dbh.php';

$jobListings = [];
$recID = null; // Initialize recID to null

// Check if a recruiter is logged in, otherwise redirect
if (!isset($_SESSION['recemail'])) {
    header("Location: ../applicant/applogin.php"); // Redirect to login page if not logged in
    exit();
}

$recEmail = $_SESSION["recemail"];

// Fetch recruiter's details (ID, first name, last name)
// This is crucial to get the recID for filtering listings
$query = $conn->prepare(
    "SELECT recID, recFName, recLName FROM recruiters WHERE recEmail = ?"
);
$query->bind_param("s", $recEmail);
$query->execute();
$result = $query->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $recfname = htmlspecialchars($row["recFName"]);
    $reclname = htmlspecialchars($row["recLName"]);
    $recID = $row["recID"];
} else {
    // Handle case where recruiter email from session doesn't exist in DB (shouldn't happen with proper login)
    error_log("Recruiter email from session not found in database: " . $conn->error);
    header("Location: ../applicant/appindex.php?error=recnotfound");
    exit();
}

// Fetch job listings specific to this recruiter
if ($recID !== null) { // Only proceed if recID was successfully retrieved
    $sql = "SELECT listingsID, job_title, company_name, description, location, salary, job_type, category, approval, date_posted
            FROM listings
            WHERE recID = ? ORDER BY date_posted DESC"; // Order by date posted, newest first
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $jobListings[] = $row;
            }
        }
    } else {
        error_log("Error fetching recruiter's job listings: " . $conn->error);
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Your Listings - Pursue Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pursue_style.css" />
    <style>
        .nav-link.text-white {
            color: #fff !important;
        }
        .nav-link.text-red { 
            color: #dc3545 !important; 
        }
        /* Custom styles for listing cards */
        .listing-card {
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            position: relative; /* Crucial for positioning the delete button */
        }
        .listing-card:hover {
            transform: translateY(-5px);
        }
        .listing-card .job-title {
            font-size: 1.3rem;
            font-weight: bold;
            color:rgb(0, 0, 0);
            margin-bottom: 0rem; /* Increased margin below job title */
        }
        .company-logo {
            max-height: 80px;
            width: auto;
            object-fit: contain;
            margin-right: 0.5rem;
            border-radius: 5px;
        }
        .listing-card .company-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            margin-top: 0.5rem; /* Added margin-top to separate from job title */
        }
        .listing-card .company-info .name {
             font-size: 1rem;
             color: #6c757d;
             font-weight: bold;
        }
        .listing-card .detail-item {
            margin-bottom: 0.3rem;
        }
        .listing-card .detail-label {
            font-weight: bold;
            color:rgb(0, 0, 0);
            margin-right: 0.25rem;
        }
        .listing-card .detail-value {
            color: #6c757d;
        }
        .status-badge {
            display: inline-block;
            padding: 0.5em 1em;
            font-size: 1em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            border-radius: 0.375rem;
            text-decoration: none;
        }
        .status-approved {
            color: #fff;
            background-color: #28a745;
        }
        .status-pending {
            color: #212529;
            background-color: #ffc107;
        }

        /* Delete button as a pill with "Delete" text */
        .delete-icon-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 60px; /* Wider to accommodate "Delete" text */
            height: 30px;
            background-color: #dc3545;
            border-radius: 15px; /* Half of height to make it pill-shaped */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem; /* Smaller font size for "Delete" */
            cursor: pointer;
            text-decoration: none;
            z-index: 10;
            transition: background-color 0.2s ease;
            padding: 0 5px; /* Add some horizontal padding */
        }
        .delete-icon-btn:hover {
            background-color: #c82333;
            color: white;
        }
        a {
            text-decoration: none;
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
                    <a class="nav-link text-white" href="#">About Us</a>
                    <a class="nav-link text-white" href="../applicant/appindex.php">All Categories</a> 
                    <a href="recindex.php" class="nav-link text-white" style="margin-top:2rem">New Listing</a>
                    <a href="reclistings.php" class="nav-link active fw-bold text-white">Your Listings</a> 
                    <a href="../includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content">
                <h1 class="mb-2 text-center">Welcome, <?php echo $recfname . " " . $reclname; ?>!</h1>
                <h2 class="mb-5 text-center">Your Job Listings</h2>

                <div class="row justify-content-center">
                    <?php if (!empty($jobListings)): ?>
                        <?php foreach ($jobListings as $job):
                            // PHP logic for company logo
                            $companyName = htmlspecialchars($job['company_name']);
                            $imagePath = "../images/" . $companyName;
                            $pngPath = $imagePath . ".png";
                            $jpgPath = $imagePath . ".jpg";
                            $jpegPath = $imagePath . ".jpeg";
                        ?>
                            <div class="col-md-6 col-lg-3 d-flex ">
                                <div class="listing-card flex-fill">
                                    <a href="../includes/delete_listing.php?id=<?php echo $job['listingsID']; ?>" class="delete-icon-btn" onclick="return confirm('Are you sure you want to delete this listing?');">
                                        Delete
                                    </a>

                                    <a href="listing_details.php?listingsid=<?php echo $job['listingsID']; ?> " >
                                    <div class="company-info text-start mb-3 mt-4">
                                        <?php if (file_exists($pngPath)): ?>
                                            <img src="<?php echo $pngPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php elseif (file_exists($jpgPath)): ?>
                                            <img src="<?php echo $jpgPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php elseif (file_exists($jpegPath)): ?>
                                            <img src="<?php echo $jpegPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php else: ?>
                                            <img src=".
                                            ./images/default_logo.png" alt="Company Logo" class="company-logo" />
                                        <?php endif; ?>
                                        <div>
                                        <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                        <span class="name"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                        </div>
                                    </div>
                    
                                    <div class="text-start flex-grow-1">
                                        <p class="detail-item"><span class="detail-label">Location:</span> <span class="detail-value"><?php echo htmlspecialchars($job['location']); ?></span></p>
                                        <p class="detail-item"><span class="detail-label">Salary:</span> <span class="detail-value"><?php echo htmlspecialchars($job['salary']); ?></span></p>
                                        <p class="detail-item"><span class="detail-label">Job Type:</span> <span class="detail-value"><?php echo htmlspecialchars($job['job_type']); ?></span></p>
                                        <p class="detail-item"><span class="detail-label">Category:</span> <span class="detail-value"><?php echo htmlspecialchars($job['category']); ?></span></p>
                                        <p class="detail-item"><span class="detail-label">Posted:</span> <span class="detail-value"><?php echo date('F j, Y', strtotime($job['date_posted'])); ?></span></p>
                                    </div>
                                    
                                    <div class="mt-auto pt-3 text-center">
                                        <span class="status-badge <?php echo ($job['approval'] == 1 ? 'status-approved' : 'status-pending'); ?>">
                                            <?php echo ($job['approval'] == 1 ? "Approved" : "Pending Approval"); ?>
                                        </span>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-info" role="alert">
                                No job listings found for your account. Start by creating a <a href="recindex.php">New Listing</a>!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





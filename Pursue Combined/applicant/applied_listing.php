<?php
session_start();
require_once '../includes/dbh.php'; // Adjust path based on your folder structure

$appliedJobs = [];
$appID = null;
$appFName = ''; // Initialize for display
$appLName = ''; // Initialize for display

// Check if an applicant is logged in, otherwise redirect
if (!isset($_SESSION['appemail'])) {
    header("Location: applogin.php"); // Redirect to applicant login page
    exit();
}

$appEmail = $_SESSION["appemail"];

// Fetch applicant's details (ID, first name, last name) if not already in session
if (isset($_SESSION['appID']) && isset($_SESSION['appFName']) && isset($_SESSION['appLName'])) {
    $appID = $_SESSION['appID'];
    $appFName = htmlspecialchars($_SESSION['appFName']);
    $appLName = htmlspecialchars($_SESSION['appLName']);
} else {
    // If details are missing from session, fetch them from DB using email
    $query = $conn->prepare("SELECT appID, appFName, appLName FROM applicants WHERE appEmail = ?");
    if ($query) {
        $query->bind_param("s", $appEmail);
        $query->execute();
        $result = $query->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $appID = $row['appID'];
            $appFName = htmlspecialchars($row['appFName']); // Fetch and store
            $appLName = htmlspecialchars($row['appLName']); // Fetch and store

            // Optionally, store these in session for future requests on this page
            $_SESSION['appID'] = $appID;
            $_SESSION['appFName'] = $appFName;
            $_SESSION['appLName'] = $appLName;
        } else {
            // Applicant not found, redirect (shouldn't happen with proper login)
            error_log("Applicant email from session not found in database: " . $appEmail);
            header("Location: applogin.php?error=appnotfound");
            exit();
        }
        $query->close();
    } else {
        error_log("Failed to prepare applicant details query: " . $conn->error);
        header("Location: applogin.php?error=db_error"); // Generic DB error for user
        exit();
    }
}


// Fetch job listings the applicant has applied to
if ($appID !== null) {
    $sql = "SELECT l.listingsID, l.job_title, l.company_name, l.location, l.salary, l.job_type, l.category, l.date_posted, ja.application_date, ja.status
            FROM job_applications ja
            JOIN listings l ON ja.listingsID = l.listingsID
            WHERE ja.appID = ?
            ORDER BY ja.application_date DESC"; // Order by application date, newest first
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $appID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $appliedJobs[] = $row;
                }
            }
        } else {
            error_log("Error fetching applicant's applied job listings: " . $conn->error);
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare applied jobs query: " . $conn->error);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Your Applied Listings - Pursue Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pursue_style.css" />
    <style>
        .nav-link.text-white {
            color: #fff !important;
        }
        .nav-link.text-red {
            color: #dc3545 !important;
        }
        /* Custom styles for listing cards (retained from reclistings) */
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
            position: relative; /* For status badge positioning */
        }
        .listing-card:hover {
            transform: translateY(-5px);
        }
        .listing-card .job-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: rgb(0, 0, 0);
            margin-bottom: 0rem;
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
            margin-top: 0.5rem;
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
            color: rgb(0, 0, 0);
            margin-right: 0.25rem;
        }
        .listing-card .detail-value {
            color: #6c757d;
        }
        /* Adjusted status badge styles for applicant statuses */
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
        .status-pending {
            color: #212529;
            background-color: #ffc107; /* Yellow for Pending */
        }
        .status-accepted {
            color: #fff;
            background-color: #28a745; /* Green for Accepted */
        }
        .status-rejected {
            color: #fff;
            background-color: #dc3545; /* Red for Rejected */
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
                    <a class="nav-link text-white" href="appprofile.php">Profile</a>
                    <a class="nav-link text-white" href="#">About Us</a>
                    <a class="nav-link text-white" href="appindex.php">All Categories</a>
                    <a class="nav-link active fw-bold text-white" href="applied_listing.php">Applied Listings</a>
                    <a href="../includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content">
                <h1 class="mb-2 text-center">Welcome, <?php echo $appFName . " " . $appLName; ?>!</h1>
                <h2 class="mb-5 text-center">Your Applied Job Listings</h2>

                <div class="row justify-content-center">
                    <?php if (!empty($appliedJobs)): ?>
                        <?php foreach ($appliedJobs as $job):
                            // PHP logic for company logo
                            $companyName = htmlspecialchars($job['company_name']);
                            $imagePath = "../images/" . $companyName; // Adjust path to images from applicant/
                            $pngPath = $imagePath . ".png";
                            $jpgPath = $imagePath . ".jpg";
                            $jpegPath = $imagePath . ".jpeg";

                            // Determine status badge class
                            $statusClass = '';
                            switch ($job['status']) {
                                case 'Accepted':
                                    $statusClass = 'status-accepted';
                                    break;
                                case 'Rejected':
                                    $statusClass = 'status-rejected';
                                    break;
                                case 'Pending':
                                default:
                                    $statusClass = 'status-pending';
                                    break;
                            }
                        ?>
                            <div class="col-md-6 col-lg-3 d-flex ">
                                <div class="listing-card flex-fill">
                                    <div class="company-info text-start mb-3 mt-4">
                                        <?php if (file_exists($pngPath)): ?>
                                            <img src="<?php echo $pngPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php elseif (file_exists($jpgPath)): ?>
                                            <img src="<?php echo $jpgPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php elseif (file_exists($jpegPath)): ?>
                                            <img src="<?php echo $jpegPath; ?>" alt="Company Logo" class="company-logo" />
                                        <?php else: ?>
                                            <img src="../images/default_logo.png" alt="Company Logo" class="company-logo" /> <?php endif; ?>
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
                                        <p class="detail-item"><span class="detail-label">Applied On:</span> <span class="detail-value"><?php echo date('F j, Y', strtotime($job['application_date'])); ?></span></p>
                                    </div>
                                    
                                    <div class="mt-auto pt-3 text-center">
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($job['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <div class="alert alert-info" role="alert">
                                You haven't applied for any jobs yet. Check out <a href="appindex.php">All Categories</a> to find listings!
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
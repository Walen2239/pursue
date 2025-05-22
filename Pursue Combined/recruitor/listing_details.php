<?php
session_start();
require_once '../includes/dbh.php'; 

$jobDetails = null;
$listingsID = null;
$applicantsForListing = []; 

if (!isset($_SESSION['recemail'])) {
    header("Location: ../applicant/applogin.php"); 
    exit();
}

$recID = null;
if (isset($_SESSION['recID'])) {
    $recID = $_SESSION['recID'];
} else {
    $recEmail = $_SESSION["recemail"];
    $query = $conn->prepare("SELECT recID FROM recruiters WHERE recEmail = ?");
    if ($query) {
        $query->bind_param("s", $recEmail);
        $query->execute();
        $result = $query->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $recID = $row['recID'];
            $_SESSION['recID'] = $recID; 
        } else {
            error_log("Recruiter email from session not found in database for authorization: " . $recEmail);
            header("Location: ../applicant/applogin.php?error=recnotfound");
            exit();
        }
        $query->close();
    } else {
        error_log("Failed to prepare recID query for authorization: " . $conn->error);
        header("Location: ../applicant/applogin.php?error=db_error");
        exit();
    }
}


if (isset($_GET['listingsid'])) {
    $listingsID = intval($_GET['listingsid']);

    $sql = "SELECT listingsID, job_title, company_name, description, location, salary, date_posted, job_type, category, approval
            FROM listings
            WHERE listingsID = ? AND recID = ?"; 

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $listingsID, $recID); 
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $jobDetails = $result->fetch_assoc();

            $applicantSql = "SELECT a.appID, a.appFName, a.appLName, a.appEmail, ja.application_date, ja.status
                             FROM job_applications ja
                             JOIN applicants a ON ja.appID = a.appID
                             WHERE ja.listingsID = ?
                             ORDER BY ja.application_date DESC"; 

            if ($applicantStmt = $conn->prepare($applicantSql)) {
                $applicantStmt->bind_param("i", $listingsID);
                $applicantStmt->execute();
                $applicantResult = $applicantStmt->get_result();

                if ($applicantResult->num_rows > 0) {
                    while ($row = $applicantResult->fetch_assoc()) {
                        $applicantsForListing[] = $row;
                    }
                }
                $applicantStmt->close();
            } else {
                error_log("Failed to prepare applicant details query: " . $conn->error);
            }

        } else {
            header("Location: reclistings.php?error=listingnotfound");
            exit;
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare listing details query: " . $conn->error);
        header("Location: reclistings.php?error=db_error");
        exit;
    }

} else {
    header("Location: reclistings.php?error=invalidlisting");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Listing Details - <?php echo $jobDetails ? htmlspecialchars($jobDetails['job_title']) : 'Pursue'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pursue_style.css" />
    <style>
        .job-details-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #000;
            font-size: 1.3rem;
            height: fit-content; 
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
        
        .applicants-column {
            background-color: #f0f2f5; 
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            position: sticky;
            top: 20px; 
        }
        .applicant-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .applicant-card h6 {
            margin-bottom: 5px;
            font-weight: bold;
            color: #343a40;
        }
        .applicant-card p {
            margin-bottom: 3px;
            font-size: 0.9em;
            color: #6c757d;
        }
        .applicant-card .app-status-badge {
            display: inline-block;
            padding: 0.3em 0.6em;
            font-size: 0.8em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            margin-top: 5px;
        }
        .app-status-pending { background-color: #ffc107; color: #212529; } 
        .app-status-accepted { background-color: #28a745; color: #fff; } 
        .app-status-rejected { background-color: #dc3545; color: #fff; } 

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
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center g-4 mt-2">
                    <div class="col-lg-8">
                        <div class="job-details-box">
                            <div class="back">
                                <button type="button" onclick="window.history.back();" class="btn btn-outline-dark btn-lg">Back to Listings</button>
                            </div>
                            <?php if ($jobDetails): ?>
                                <div style="text-align:center;">
                                    <?php
                                    $companyName = htmlspecialchars($jobDetails['company_name']);
                                    $pngPath = "../images/" . $companyName . ".png";
                                    $jpgPath = "../images/" . $companyName . ".jpg";
                                    $jpegPath = "../images/" . $companyName . ".jpeg";

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
                                <p><strong>Description:</strong> <br><?php echo nl2br(htmlspecialchars($jobDetails['description'])); ?></p>
                                <p><strong>Date Posted:</strong> <?php echo date('F j, Y', strtotime($jobDetails['date_posted'])); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($jobDetails['category']); ?></p>
                                <p><strong>Approval Status:</strong>
                                    <span class="status-badge <?php echo ($jobDetails['approval'] == 1 ? 'status-approved' : 'status-pending'); ?>">
                                        <?php echo ($jobDetails['approval'] == 1 ? "Approved" : "Pending Approval"); ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <div class="alert alert-warning text-center" role="alert">
                                    Job listing not found or you do not have permission to view it.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="applicants-column">
                            <h4 class="text-center mb-4" style="color: #000;">Applicants</h4>
                            <?php if (!empty($applicantsForListing)): ?>
                                <?php foreach ($applicantsForListing as $applicant): ?>
                                    <div class="applicant-card" id="applicant-card-<?php echo $applicant['appID']; ?>">
                                        <h6><?php echo htmlspecialchars($applicant['appFName'] . " " . $applicant['appLName']); ?></h6>
                                        <p>Email: <?php echo htmlspecialchars($applicant['appEmail']); ?></p>
                                        <p>Applied: <?php echo date('M j, Y', strtotime($applicant['application_date'])); ?></p>
                                        <div class="application-status-controls" id="status-controls-<?php echo $applicant['appID']; ?>">
                                            <?php if ($applicant['status'] == 'Pending'): ?>
                                                <button class="btn btn-success btn-sm update-status-btn"
                                                        data-appid="<?php echo $applicant['appID']; ?>"
                                                        data-listingsid="<?php echo $listingsID; ?>"
                                                        data-status="Accepted">Approve</button>
                                                <button class="btn btn-danger btn-sm update-status-btn"
                                                        data-appid="<?php echo $applicant['appID']; ?>"
                                                        data-listingsid="<?php echo $listingsID; ?>"
                                                        data-status="Rejected">Reject</button>
                                            <?php else: ?>
                                                <span class="app-status-badge <?php
                                                    $appStatusClass = '';
                                                    switch ($applicant['status']) {
                                                        case 'Accepted': $appStatusClass = 'app-status-accepted'; break;
                                                        case 'Rejected': $appStatusClass = 'app-status-rejected'; break;
                                                        default: $appStatusClass = 'app-status-pending'; break; // Fallback
                                                    }
                                                    echo $appStatusClass;
                                                ?>">
                                                    <?php echo htmlspecialchars($applicant['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info text-center" role="alert">
                                    No applicants for this listing yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.update-status-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const appID = this.dataset.appid;
                    const listingsID = this.dataset.listingsid;
                    const status = this.dataset.status;
                    const confirmation = confirm(`Are you sure you want to ${status} this applicant?`);

                    if (confirmation) {
                        fetch('update_application_status.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `appID=${appID}&listingsID=${listingsID}&status=${status}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const statusControlsDiv = document.getElementById(`status-controls-${appID}`);
                                if (statusControlsDiv) {
                                    let newBadgeClass = '';
                                    if (status === 'Accepted') {
                                        newBadgeClass = 'app-status-accepted';
                                    } else if (status === 'Rejected') {
                                        newBadgeClass = 'app-status-rejected';
                                    }

                                    statusControlsDiv.innerHTML = `
                                        <span class="app-status-badge ${newBadgeClass}">
                                            ${status}
                                        </span>
                                    `;
                                }
                                alert('Applicant status updated successfully!');
                            } else {
                                alert('Error updating status: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while updating the status.');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
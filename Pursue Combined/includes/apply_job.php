<?php
session_start();
require_once 'dbh.php'; // Correct: dbh.php is in the same 'includes' folder

// Check if applicant is logged in
if (!isset($_SESSION['appemail'])) {
    // Corrected path: From 'includes' folder, go up one level to project root, then find applogin.php
    header("Location: ../applogin.php?error=notloggedin");
    exit();
}

$appID = null;
$listingsID = null;

// Get the applicant's ID from the session (or fetch it if not stored directly)
if (isset($_SESSION['appID'])) {
    $appID = $_SESSION['appID'];
} else {
    // If appID is not in session, fetch it from the database using email
    $appEmail = $_SESSION["appemail"];
    $query = $conn->prepare("SELECT appID FROM applicants WHERE appEmail = ?");
    $query->bind_param("s", $appEmail);
    $query->execute();
    $result = $query->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $appID = $row['appID'];
        $_SESSION['appID'] = $appID; // Store it for future use
    } else {
        // Applicant not found, serious error or session desync
        // Corrected path: From 'includes' folder, go up one level to project root, then find applogin.php
        header("Location: ../applogin.php?error=appnotfound");
        exit();
    }
}

// Get listingsID from POST request
if (isset($_POST['listings_id']) && is_numeric($_POST['listings_id'])) {
    $listingsID = (int)$_POST['listings_id'];
} else {
    // Corrected path: From 'includes' folder, go up one level to project root, then find appindex.php
    header("Location: ../appindex.php?error=invalidlisting");
    exit();
}

// Check if the applicant has already applied to this listing
$checkSql = "SELECT COUNT(*) FROM job_applications WHERE appID = ? AND listingsID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $appID, $listingsID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$alreadyApplied = false;
if ($checkResult && $checkResult->fetch_row()[0] > 0) {
    $alreadyApplied = true;
}
$checkStmt->close();

if ($alreadyApplied) {
    // Corrected path: From 'includes' folder, go up one level to project root, then find job_details.php
    header("Location: ../job_details.php?listingsid=" . $listingsID . "&status=alreadyapplied");
    exit();
}

// Insert application into job_applications table
$insertSql = "INSERT INTO job_applications (appID, listingsID) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertSql);

if ($insertStmt) {
    $insertStmt->bind_param("ii", $appID, $listingsID);
    if ($insertStmt->execute()) {
        // Corrected path: From 'includes' folder, go up one level to project root, then find job_details.php
        header("Location: ../job_details.php?listingsid=" . $listingsID . "&status=apply_success");
    } else {
        error_log("Error inserting application: " . $insertStmt->error);
        // Corrected path
        header("Location: ../job_details.php?listingsid=" . $listingsID . "&status=apply_fail&reason=db_error");
    }
    $insertStmt->close();
} else {
    error_log("Failed to prepare insert statement: " . $conn->error);
    // Corrected path
    header("Location: ../job_details.php?listingsid=" . $listingsID . "&status=apply_fail&reason=db_prepare_error");
}

$conn->close();
exit();
?>
<?php
session_start();
require_once '../includes/dbh.php'; 

header('Content-Type: application/json'); 

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['recID'])) {
    $response['message'] = 'Recruiter not logged in.';
    echo json_encode($response);
    exit();
}

$recID = $_SESSION['recID'];

if (isset($_POST['appID'], $_POST['listingsID'], $_POST['status'])) {
    $appID = intval($_POST['appID']);
    $listingsID = intval($_POST['listingsID']);
    $status = $_POST['status'];

    if (!in_array($status, ['Accepted', 'Rejected'])) {
        $response['message'] = 'Invalid status provided.';
        echo json_encode($response);
        exit();
    }

    $sql = "UPDATE job_applications ja
            JOIN listings l ON ja.listingsID = l.listingsID
            SET ja.status = ?
            WHERE ja.appID = ?
            AND ja.listingsID = ?
            AND l.recID = ?"; 

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("siii", $status, $appID, $listingsID, $recID);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Application status updated successfully.';
            } else {
                $response['message'] = 'No changes made or application not found/authorized for update.';
            }
        } else {
            $response['message'] = 'Database execution error: ' . $stmt->error;
            error_log("Database execution error in update_application_status.php: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        error_log("Failed to prepare statement in update_application_status.php: " . $conn->error);
    }
} else {
    $response['message'] = 'Required parameters (appID, listingsID, status) are missing.';
}

$conn->close();
echo json_encode($response);
?>
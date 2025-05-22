<?php
session_start();
require_once 'dbh.php'; 

if (!isset($_SESSION['recemail'])) {
    header("Location: ../applicant/appindex.php"); 
    exit();
}

$recID = null;

if (isset($_SESSION['recID'])) {
    $recID = $_SESSION['recID'];
} else {

    $recEmail = $_SESSION["recemail"];
    $query = $conn->prepare("SELECT recID FROM recruiters WHERE recEmail = ?");
    $query->bind_param("s", $recEmail);
    $query->execute();
    $result = $query->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $recID = $row['recID'];
        $_SESSION['recID'] = $recID; 
    } else {

        header("Location: ../applicant/appindex.php?error=recnotfound");
        exit();
    }
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $listingsID = (int)$_GET['id'];

    $sql = "DELETE FROM listings WHERE listingsID = ? AND recID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $listingsID, $recID); 
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: ../recruitor/reclistings.php?status=deleted_success");
        } else {
            header("Location: ../recruitor/reclistings.php?status=deleted_fail&reason=not_found_or_not_owned");
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare delete statement: " . $conn->error);
        header("Location: ../recruitor/reclistings.php?status=deleted_fail&reason=db_error");
    }
} else {
    header("Location: ../recruitor/reclistings.php?status=deleted_fail&reason=invalid_id");
}

$conn->close();
exit();
?>
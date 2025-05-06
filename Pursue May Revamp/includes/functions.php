<?php
// Function to fetch user role from the database
function getUserRole($conn, $email) {
    $email = mysqli_real_escape_string($conn, $email); // Escape for safety
    $query = "SELECT role FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['role']; // Returns "worker" or "employer"
    } else {
        return null; // Or some default value, or handle the error as you prefer
    }
}

// Function to get all job listings
function getAllJobListings($conn) {
    $query = "SELECT * FROM job_listings";
    $result = mysqli_query($conn, $query);

    $listings = array();
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $listings[] = $row;
        }
    }
    return $listings;
}
// Function to get all job listings by a user id
function getJobListingsByEmployerId(mysqli $conn, int $employer_id): array {
    $query = "SELECT * FROM job_listings WHERE employer_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $employer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $jobListings = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $jobListings;
    } else {
        // Handle the error appropriately (e.g., log it, display a user-friendly message)
        error_log("Error preparing statement: " . mysqli_error($conn));
        return []; // Return an empty array to avoid further errors
    }
}

// Function to get job listing details
function getJobListingDetails($conn, $id) {
    $id = mysqli_real_escape_string($conn, $id);
    $query = "SELECT * FROM job_listings WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row;
    } else {
        return null;
    }
}
// Function to display job details
function displayJobDetails($job) {
    if ($job) {
        echo "<h2>Job Title: " . htmlspecialchars($job['title']) . "</h2>";
        echo "<p>Company: " . htmlspecialchars($job['company_name']) . "</p>";
        echo "<p>Description: " . htmlspecialchars($job['description']) . "</p>";
        echo "<p>Salary: $" . htmlspecialchars($job['salary']) . "</p>";
        echo "<p>Location: " . htmlspecialchars($job['location']) . "</p>";
        echo "<p>Date Posted: " . date('F j, Y', strtotime($job['date_posted'])) . "</p>";
        echo "<p>Job Type: " . htmlspecialchars($job['job_type']) . "</p>"; //ADDED

        // Add more fields as necessary
    } else {
        echo "<p>Job details not found.</p>";
    }
}
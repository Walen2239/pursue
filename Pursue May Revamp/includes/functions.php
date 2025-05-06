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
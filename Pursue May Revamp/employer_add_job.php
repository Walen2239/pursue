<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employer') {
    header("Location: login.php");
    exit;
}
require_once("includes/db_connect.php");
require_once("includes/functions.php");

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use filter_input for validation and sanitization
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT); // Use FILTER_VALIDATE_FLOAT
    $job_type = filter_input(INPUT_POST, 'job_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate input fields
    if (empty($title)) {
        $error = "Please enter the job title.";
    } elseif (empty($company_name)) {
        $error = "Please enter the company name.";
    } elseif (empty($description)) {
        $error = "Please enter the job description.";
    } elseif (empty($location)) {
        $error = "Please enter the job location.";
    } elseif ($salary === false || $salary <= 0) { //check if salary is valid
        $error = "Salary must be a positive number.";
    }  elseif (empty($job_type)) {
        $error = "Please enter the job type.";
    } else {
        // Insert the job listing into the database
        $employer_id = $_SESSION['user_id'];
        $query = "INSERT INTO job_listings (employer_id, title, company_name, description, location, salary, job_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssds", $employer_id, $title, $company_name, $description, $location, $salary, $job_type); // "d" for float/decimal
            if (mysqli_stmt_execute($stmt)) {
                header("Location: employer_dashboard.php?job_added=true");
                exit;
            } else {
                $error = "Error posting job: " . mysqli_error($conn); // More specific error
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Error preparing statement: " . mysqli_error($conn);
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Post a New Job Listing</h2>
        <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
        <form method="post" action="employer_add_job.php">
            <div class="form-group">
                <label for="title">Job Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>
            <div class="form-group">
                <label for="description">Job Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="form-group">
                <label for="salary">Salary:</label>
                <input type="number" id="salary" name="salary" required>
            </div>
            <div class="form-group">
                <label for="job_type">Job Type:</label>
                <select id="job_type" name="job_type" required>
                    <option value="">Select Job Type</option>
                    <option value="Contract">Contract</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Full Time">Full Time</option>
                </select>
            </div>
            <button type="submit">Post Job</button>
        </form>
        <p><a href="employer_dashboard.php">Back to Dashboard</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>
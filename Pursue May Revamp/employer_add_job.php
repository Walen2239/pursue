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
    $salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT);
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
    } elseif ($salary === false || $salary <= 0) {
        $error = "Salary must be a positive number.";
    } elseif (empty($job_type)) {
        $error = "Please enter the job type.";
    }

    $logo_path = ''; // Initialize logo path

    // Handle file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['logo']['type'];

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'images/';
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $company_name_safe = preg_replace("/[^a-zA-Z0-9]+/", "-", $company_name);
            // Use the company name for the new file name
            $new_file_name = $company_name_safe . "." . $file_extension;
            $target_file = $upload_dir . $new_file_name;

             // Check if the file already exists
            if (file_exists($target_file)) {
                $error = "File with this name already exists. Please choose a different company name or logo.";
            } else {
                // Attempt to move the uploaded file
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                    // File upload was successful
                    $logo_path = $new_file_name; // Store the new file name
                } else {
                    $error = "Error uploading file.";
                }
            }
        } else {
            $error = "Invalid file type. Allowed types are JPEG, PNG, and GIF.";
        }
    } else if (isset($_FILES['logo']) && $_FILES['logo']['error'] != 4) {
        $error = "Error uploading logo.";
    }

    if (empty($error)) {
        // Insert the job listing into the database, exclude the logo path
        $employer_id = $_SESSION['user_id'];
        $query = "INSERT INTO job_listings (employer_id, title, company_name, description, location, salary, job_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssds", $employer_id, $title, $company_name, $description, $location, $salary, $job_type);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: employer_dashboard.php?job_added=true");
                exit;
            } else {
                $error = "Error posting job: " . mysqli_error($conn);
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/pursue_tyle.css" />
    <style>s
        .container {
            margin-top: 50px;
        }
        .job-details-box {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .job-details-box h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .form-group label {
            font-weight: bold;
            font-size: 1.1rem;
            color: #555;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 0 auto;
            font-size: 1.1rem;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        .back-to-dashboard {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-dashboard a {
            color: #0078d7;
            text-decoration: none;
            font-size: 1.1rem;
        }
        .back-to-dashboard a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
        .form-group input[type="file"] {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            width: 100%;
            box-sizing: border-box;
        }
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 sidebar p-4 bg-dark">
                <div class="text-center mb-4">
                    <img src="images/Pursue Logo1.png" alt="Logo" class="img-fluid" style="max-width: 100%;" />
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link text-white" href="/profile">Profile</a>
                    <a class="nav-link text-white" onclick="window.history.back();">All Category</a>
                    <a class="nav-link text-white" href="#">About Us</a>
                    <a href="employer_add_job.php" style="margin-top:2rem" class="nav-link active fw-bold text-white">New Listing</a>
                    <a href="employer_listing.php" class="nav-link text-white">Your Listing</a>
                    <a href="logout.php" style="margin-top:2rem" class="nav-link text-red">Logout</a>
                </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="job-details-box">
                            <h2>Post a New Job Listing</h2>
                            <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
                            <form method="post" action="employer_add_job.php" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="title">Job Title:</label>
                                    <input type="text" id="title" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="company_name">Company Name:</label>
                                    <input type="text" id="company_name" name="company_name" required>
                                </div>
                                 <div class="form-group">
                                    <label for="logo">Company Logo:</label>
                                    <input type="file" id="logo" name="logo" accept="image/*">
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
                                <button type="submit" class="btn btn-primary mt-3">Post Job</button>
                            </form>
                            <div class="back-to-dashboard">
                                <a href="employer_dashboard.php">Back to Dashboard</a>
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

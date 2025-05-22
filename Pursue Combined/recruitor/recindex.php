<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recruitor Index</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/pursue_style.css" >
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .nav-link.text-white {
            color: #fff !important;
        }
        .nav-link.text-red { 
            color: #dc3545 !important; 
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
                        <a class="nav-link text-white" href="../applicant/appindex.php">All Category</a> 
                        <a href="#" style="margin-top:2rem" class="nav-link active fw-bold text-white">New Listing</a>
                        <a href="../recruitor/reclistings.php" class="nav-link text-white">Your Listing</a>
                        <a href="../includes/logout.php" class="nav-link text-red" style="margin-top:2rem">Logout</a>
                    </nav>
            </aside>
            <main class="col-md-9 col-lg-10 p-4 main-content mt-2">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="job-details-box">
                            <h2>Post a New Job Listing</h2>
                                <form action="../includes/listingsupload.php" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="job_title">Job Title:</label>
                                    <input type="text" id="job_title" name="job_title" required>
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
                                <div class="form-group">
                                    <label for="category">Job Type:</label>
                                    <select id="category" name="category" required>
                                        <option value="">Select Your Category</option>
                                        <option value="Education & Training">Education & Training</option>
                                        <option value="Healthcare">Healthcare</option>
                                        <option value="Sales & Marketing">Sales & Marketing</option>
                                        <option value="IT & Software">IT & Software</option>
                                        <option value="Hospitality">Hospitality</option>
                                        <option value="Finance">Finance</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Post Job</button>
                            </form>

                            <div class="back-to-dashboard">
                                <a href="../applicant/appindex.php">Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <?php 
        if (isset($_SESSION["listingerror"])) {
            echo "<p style='color:red'>" . $_SESSION["listingerror"] . "</p>";
            unset($_SESSION["listingerror"]);
        }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

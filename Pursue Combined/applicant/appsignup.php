<?php
session_start(); // session_start() must be the very first thing in your PHP script
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Sign Up as Applicant</h2>

                                <?php
                // Display error or success messages from session
                if (isset($_SESSION["appsignuperror"])) {
                    echo "<div class='error'>" . $_SESSION["appsignuperror"] . "</div>";
                    unset($_SESSION["appsignuperror"]); // Clear the message after displaying
                }
                if (isset($_SESSION["appsignupsuccess"])) {
                    echo "<div class='success-message'>" . $_SESSION["appsignupsuccess"] . "</div>";
                    unset($_SESSION["appsignupsuccess"]); // Clear the message after displaying
                }
                ?>

                <form method="POST" action="../includes/appsigner.php">
                     <div class="form-group">
                        <label for="appfname">First Name:</label>
                        <input type="text" class="form-control" name="appfname" required>
                    </div>

                    <div class="form-group">
                        <label for="applname">Last Name:</label>
                        <input type="text" class="form-control" name="applname" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" name="appemail" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="apppwd" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary btn-block mt-4">Sign Up</button>

                    <p class="mt-3 text-center">Already have an account? <a href="../applicant/applogin.php">Login</a></p>
                    <p class="mt-3 text-center">Sign up as Recruiter! <a href='../recruitor/recsignup.php'>Recruiter Signup</a></p>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




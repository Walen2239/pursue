<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
                <h2 class="text-center mb-4">Login as Applicant</h2>

                <form method="post" action="../includes/applogger.php">
                    <div class="form-group">
                        <label for="email">Enter Your Email:</label>
                        <input type="email" class="form-control" name="appemail" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Enter Your Password:</label>
                        <input type="password" class="form-control" name="apppwd" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary btn-block mt-4">Login</button>

                    <p class="mt-3 text-center">login as Recruiter! <a href='../recruitor/reclogin.php'>Recruiter login</a></p>
                </form>
            </div>
        </div>
    </div>
    <?php
        session_start();
        if (isset($_SESSION["apploginerror"])) {
            echo $_SESSION["apploginerror"];
        }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

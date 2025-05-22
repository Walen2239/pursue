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
                <h2 class="text-center mb-4">Sign Up as Recruitor</h2>

                <form method="post" action="../includes/recsigner.php">
                     <div class="form-group">
                        <label for="recfname">First Name:</label>
                        <input type="text" class="form-control" name="recfname" required>
                    </div>

                    <div class="form-group">
                        <label for="reclname">Last Name:</label>
                        <input type="text" class="form-control" name="reclname" required>
                    </div>

                    <div class="form-group">
                        <label for="recemail">Email:</label>
                        <input type="email" class="form-control" name="recemail" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" name="recpwd" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary btn-block mt-4">Sign Up</button>

                    <p class="mt-3 text-center">Login as Recruitor! <a href="../recruitor/reclogin.php">Login</a></p>

                </form>
            </div>
        </div>
    </div>
    <?php
        if (isset($_SESSION["recsignuperror"])) {
            echo $_SESSION["recsignuperror "];
        }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
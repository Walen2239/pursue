<?php
// Include the database connection file
require_once("includes/db_connect.php"); // Make sure this path is correct

// Initialize variables to hold form data and error messages
$email = $password = $role = "";
$email_error = $password_error = $role_error = $general_error = "";
$registration_success = false; // Added for success message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"])) {
        $email_error = "Email is required";
    } else {
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Invalid email format";
        }
    }

    // Validate password
    if (empty($_POST["password"])) {
        $password_error = "Password is required";
    } else {
        $password = $_POST["password"];
        //  Add more robust password validation if needed (length, complexity, etc.)
        if (strlen($password) < 6) {
            $password_error = "Password must be at least 6 characters long";
        }
    }

    // Validate role
    if (empty($_POST["role"])) {
        $role_error = "Role is required";
    } else {
        $role = $_POST["role"];
        if ($role != "worker" && $role != "employer") {
            $role_error = "Invalid role";
        }
    }

    // If there are no errors, proceed with registration
    if (empty($email_error) && empty($password_error) && empty($role_error)) {
        // Prepare and execute the database query
        $query = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $email, $password, $role); //removed hasing
            if (mysqli_stmt_execute($stmt)) {
                // Registration successful
                $registration_success = true;
                // Clear the form data
                $email = $password = $role = "";
                 header("Location: login.php?registration=success"); //redirect to login
                 exit;

            } else {
                $general_error = "Registration failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_error = "Error preparing statement: " . mysqli_error($conn);
        }
    }
    mysqli_close($conn); // Close the database connection
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
                <h2 class="text-center mb-4">Register</h2>

                <?php if ($registration_success): ?>
                    <div class="alert alert-success" role="alert">
                        Registration successful! Please <a href="login.php">login</a>.
                    </div>
                <?php endif; ?>

                <?php if ($general_error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $general_error; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="register.php">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
                        <span class="error"><?php echo $email_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?php echo $password; ?>">
                        <span class="error"><?php echo $password_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select class="form-control" id="role" name="role">
                            <option value="">Select Role</option>
                            <option value="worker" <?php if ($role == "worker") echo "selected"; ?>>Worker</option>
                            <option value="employer" <?php if ($role == "employer") echo "selected"; ?>>Employer</option>
                        </select>
                        <span class="error"><?php echo $role_error; ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mt-4">Register</button>
                    <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
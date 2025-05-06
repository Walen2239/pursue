<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to the appropriate dashboard based on their role
    require_once("includes/db_connect.php"); // Ensure this path is correct
    require_once("includes/functions.php");   // Ensure this path is correct
    $email = $_SESSION['email'];
    $role = getUserRole($conn, $email);
    if ($role == 'worker') {
        header("Location: worker_dashboard.php");
    } elseif ($role == 'employer') {
        header("Location: employer_dashboard.php");
    }
    exit;
}

$error = ""; // Initialize error message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once("includes/db_connect.php"); // Ensure this path is correct
    require_once("includes/functions.php");   // Ensure this path is correct

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL); // Sanitize email
    $password = $_POST['password'];

    // Input validation (more robust)
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (empty($password)) {
        $error = "Please enter your password.";
    } else {
        try {
            // Use prepared statements to prevent SQL injection
            $query = "SELECT id, email, password, role FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    // Verify the password 
                    if ($password == $user['password']) {
                        // Password is correct, set session variables and redirect
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];

                        if ($user['role'] == 'worker') {
                            header("Location: worker_dashboard.php");
                            exit;
                        } elseif ($user['role'] == 'employer') {
                            header("Location: employer_dashboard.php");
                            exit;
                        }
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "Invalid email.";
                }
                mysqli_stmt_close($stmt);
            } else {
                throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
            }
        } catch (Exception $e) {
            // Log the error (optional, but highly recommended for debugging)
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred. Please try again later."; // User-friendly error
        } finally {
            mysqli_close($conn); // Close the connection in a finally block
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<style>
    body {
        background-color: #4545a3;
    }
</style>
<body>
    <div class="container" style="font-size:2rem;">
        <h2>Login</h2>
        <?php if ($error) { echo "<p class='error'>$error</p>"; } ?>
        <form method="post" action="login.php" style="font-size:1.6rem;">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" style="font-size:1.2rem;" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" style="font-size:1.2rem;" name="password" required>
            </div>
            <button type="submit" style="font-size:1.3rem;">Login</button>
        </form>
        <div style="font-size:1.5rem;">
        <p>Don't have an account? <a href="register.php">Register</a></p> 
        </div>
        </div>
</body>
</html>

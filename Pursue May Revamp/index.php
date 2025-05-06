<?php
session_start();
if (isset($_SESSION['user_id'])) {
    //already logged in
    header("Location: login.php"); //redirect to login
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Job Portal</title>
    <link rel="stylesheet" href="css/styles.css"> </head>
<body>
    <div class="container">
        <h2>Welcome to Our Job Portal</h2>
        <p>Please <a href="login.php">login</a> to your account to find job listings or post a job.</p>
    </div>
</body>
</html>
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
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<style>
    body {
        background-color: #4545a3;
    }
</style>
<body>
    <div class="container" style="font-size:2rem; text-align:center;">
        <h2>Welcome to Pursue Job Portal</h2>
        <p>To access, please <a href="login.php">login</a> to your account!</p>
        <p>If you are new to Pursue, please <a href="register.php">Join</a> our platform!</p>
    </div>
</body>
</html>
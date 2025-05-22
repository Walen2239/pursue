<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "dbh.php";

    $job_title = $_POST["job_title"];
    $company_name = $_POST["company_name"];
    $description = $_POST["description"];
    $location = $_POST["location"];
    $salary = $_POST["salary"];
    $job_type = $_POST["job_type"];
    $recEmail = $_SESSION["recemail"];
    $date_posted = date("Y-m-d"); 
    $category = $_POST["category"];
    $approval = 0;

    $logo_path = '';
    $error = '';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['logo']['type'];

        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../images/';
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $company_name_safe = preg_replace("/[^a-zA-Z0-9]+/", "-", $company_name);
            $new_file_name = $company_name_safe . "." . $file_extension;
            $target_file = $upload_dir . $new_file_name;

            if (file_exists($target_file)) {
                $error = "File with this name already exists. Please choose a different company name or logo.";
            } else {
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                    $logo_path = $new_file_name; 
                } else {
                    $error = "Error uploading file.";
                }
            }
        }
    } 


    $stmt = $conn->prepare("SELECT recID FROM recruiters WHERE recEmail = ?");
    if ($stmt) {
        $stmt->bind_param("s", $recEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $recID = $row["recID"];
        } else {
            $_SESSION["listingerror"] = "Recruiter not found.";
            $stmt->close();
            $conn->close();
            header("Location: ../recruitor/recindex.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION["listingerror"] = "Database error.";
        $conn->close();
        header("Location: ../recruitor/recindex.php");
        exit();
    }


    $stmt = $conn->prepare(
        "INSERT INTO listings (recID, job_title, company_name, description, location, salary, job_type, category, approval) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param("issssdssi", $recID, $job_title, $company_name, $description, $location, $salary, $job_type, $category, $approval);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: ../applicant/appindex.php");
            exit();
        } else {
            $_SESSION["listingerror"] =
                "Error inserting listing: " . $stmt->error;
            $stmt->close();
            $conn->close();
            header("Location: ../recruitor/recindex.php");
            exit();
        }
    } else {
        $_SESSION["listingerror"] = "Failed to prepare insert statement.";
        $conn->close();
        header("Location: ../recruitor/recindex.php");
        exit();
    }

    $logo_path = ''; 
    $error = '';

} else {
    header("Location: ../recruitor/recindex.php");
    exit();
}













    // $error = "";

    // $job_title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // $company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // $salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT);
    // $job_type = filter_input(INPUT_POST, 'job_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // if (empty($job_title)) {
    //     $error = "Please enter the job title.";
    // } elseif (empty($company_name)) {
    //     $error = "Please enter the company name.";
    // } elseif (empty($description)) {
    //     $error = "Please enter the job description.";
    // } elseif (empty($location)) {
    //     $error = "Please enter the job location.";
    // } elseif ($salary === false || $salary <= 0) {
    //     $error = "Salary must be a positive number.";
    // } elseif (empty($job_type)) {
    //     $error = "Please enter the job type.";
    // }

<?php

session_start();
session_unset();
session_destroy();

header("Location: ../applicant/appindex.php");
exit();
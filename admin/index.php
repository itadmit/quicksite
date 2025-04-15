<?php
require_once '../config/init.php';

// וידוא שמשתמש מחובר
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// הפניה לדשבורד
header('Location: dashboard.php');
exit;
?>

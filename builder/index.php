<?php
require_once '../config/init.php';

// וידוא שמשתמש מחובר
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// קבלת מזהה דף נחיתה/תבנית אימייל אם קיים
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'landing';

include '../includes/header.php';
?>

<div class="builder-container">
    <!-- ממשק בילדר -->
</div>

<?php include '../includes/footer.php'; ?>


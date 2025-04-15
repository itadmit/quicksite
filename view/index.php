<?php
require_once '../config/init.php';

// קבלת ה-slug של דף הנחיתה
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    http_response_code(404);
    echo '404 - דף לא נמצא';
    exit;
}

// קבלת פרטי דף הנחיתה מהמסד נתונים
// קוד להצגת דף הנחיתה
?>

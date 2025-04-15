<?php
// נקודת כניסה לממשק ה-API
header('Content-Type: application/json');

// בדיקת ה-endpoint המבוקש והפניה לקובץ המתאים
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

switch ($endpoint) {
    case 'auth':
        require_once 'auth.php';
        break;
    case 'landing-pages':
        require_once 'landing-pages.php';
        break;
    case 'contacts':
        require_once 'contacts.php';
        break;
    case 'messaging':
        require_once 'messaging.php';
        break;
    default:
        // תשובת שגיאה כאשר ה-endpoint אינו קיים
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
?>

<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// בדיקה אם זו בקשת AJAX
if (!is_ajax_request()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'גישה לא מורשית']);
    exit;
}

// בדיקה שהמשתמש מחובר
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'יש להתחבר כדי לבצע פעולה זו']);
    exit;
}

// קבלת מזהה התבנית
$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

if ($template_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'מזהה תבנית לא תקין']);
    exit;
}

// בדיקת CSRF
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
if ($csrf_token !== $_SESSION[CSRF_TOKEN_NAME]) {
    echo json_encode(['success' => false, 'message' => 'תוקן האבטחה אינו תקף']);
    exit;
}

// טעינת התבנית
try {
    $stmt = $pdo->prepare("
        SELECT html_content 
        FROM templates 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    
    if (!$template) {
        echo json_encode(['success' => false, 'message' => 'התבנית המבוקשת לא נמצאה']);
        exit;
    }
    
    // החזרת תוכן התבנית
    echo json_encode([
        'success' => true, 
        'html' => $template['html_content']
    ]);
    
} catch (PDOException $e) {
    error_log("שגיאה בטעינת תבנית: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'אירעה שגיאה בטעינת התבנית']);
}
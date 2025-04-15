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

// קבלת מזהה ההתראה
$alert_id = isset($_POST['alert_id']) ? $_POST['alert_id'] : '';

if (empty($alert_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'מזהה התראה חסר']);
    exit;
}

// טיפול בהתראות מערכת
if (is_numeric($alert_id)) {
    // בדיקה אם הטבלה קיימת
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'system_alerts'
        ");
        $stmt->execute([DB_NAME]);
        
        if ($stmt->fetchColumn()) {
            // עדכון התראה כנקראה
            $stmt = $pdo->prepare("
                UPDATE system_alerts 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$alert_id, $current_user['id']]);
            
            // החזרת תשובה חיובית
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("שגיאה בעדכון התראה: " . $e->getMessage());
    }
}

// טיפול בהתראות דינמיות
if ($alert_id === 'subscription_expiring') {
    // שמירת העדפה בסשן שלא להציג את ההתראה שוב בתקופה הקרובה
    $_SESSION['dismissed_alerts'][$alert_id] = time();
    
    echo json_encode(['success' => true]);
    exit;
}

// אם הגענו לכאן, לא הצלחנו לטפל בהתראה
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'התראה לא נמצאה או לא ניתנת לסימון כנקראה']);
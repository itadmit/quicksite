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

// קבלת נתוני הבקשה
$subscription_id = isset($_POST['subscription_id']) ? intval($_POST['subscription_id']) : 0;
$auto_renew = isset($_POST['auto_renew']) ? (bool)$_POST['auto_renew'] : false;
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// בדיקת CSRF
if ($csrf_token !== $_SESSION[CSRF_TOKEN_NAME]) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'תוקן האבטחה אינו תקף']);
    exit;
}

// בדיקה שהמנוי קיים ושייך למשתמש
try {
    $stmt = $pdo->prepare("
        SELECT * FROM subscriptions 
        WHERE id = ? AND user_id = ? AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$subscription_id, $current_user['id']]);
    $subscription = $stmt->fetch();
    
    if (!$subscription) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'מנוי לא נמצא או אינו פעיל']);
        exit;
    }
    
    // עדכון הגדרת חידוש אוטומטי
    $stmt = $pdo->prepare("
        UPDATE subscriptions 
        SET auto_renew = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$auto_renew ? 1 : 0, $subscription_id]);
    
    // הוספת רישום לפעילות משתמש (אם הטבלה קיימת)
    try {
        $activity_desc = $auto_renew ? 'הפעלת חידוש אוטומטי למנוי' : 'ביטול חידוש אוטומטי למנוי';
        $stmt = $pdo->prepare("
            INSERT INTO user_activities (user_id, type, description, created_at)
            VALUES (?, 'subscription', ?, NOW())
        ");
        $stmt->execute([$current_user['id'], $activity_desc]);
    } catch (PDOException $e) {
        // התעלם משגיאות - ייתכן שהטבלה לא קיימת
    }
    
    // החזרת תגובה חיובית
    echo json_encode([
        'success' => true, 
        'message' => $auto_renew ? 'חידוש אוטומטי הופעל בהצלחה' : 'חידוש אוטומטי בוטל בהצלחה',
        'auto_renew' => $auto_renew
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("שגיאה בעדכון חידוש אוטומטי: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'אירעה שגיאה בעדכון ההגדרות']);
    exit;
}
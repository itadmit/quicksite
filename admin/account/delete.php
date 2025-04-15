<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// ברירת מחדל להודעות
$error = '';
$success = '';

// בדיקה שהבקשה היא מסוג POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        // שגיאת CSRF - הפניה בחזרה לפרופיל עם הודעת שגיאה
        $_SESSION['account_error'] = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
        redirect(SITE_URL . '/admin/profile.php');
        exit;
    }
    
    // קבלת המזהה של המשתמש הנוכחי
    $user_id = $current_user['id'] ?? 0;
    
    // בדיקה שיש משתמש מחובר ומזהה תקין
    if (!$user_id) {
        $_SESSION['account_error'] = 'משתמש לא מזוהה. נא להתחבר שוב ולנסות מחדש.';
        redirect(SITE_URL . '/auth/login.php');
        exit;
    }
    
    try {
        // התחלת טרנזקציה בבסיס הנתונים
        $pdo->beginTransaction();
        
        // 1. מחיקת נתונים מטבלאות משויכות
        // רשימת טבלאות וקשרים לטיפול - יש לעדכן בהתאם למבנה המערכת
        $related_tables = [
            ['table' => 'subscriptions', 'field' => 'user_id'],
            ['table' => 'landing_pages', 'field' => 'user_id'],
            ['table' => 'contacts', 'field' => 'user_id'],
            ['table' => 'campaigns', 'field' => 'user_id'],
            ['table' => 'sent_messages', 'field' => 'user_id'],
            ['table' => 'user_settings', 'field' => 'user_id'],
            ['table' => 'user_notifications', 'field' => 'user_id'],
            ['table' => 'user_logs', 'field' => 'user_id']
        ];
        
        // מחיקת נתונים מכל טבלה משויכת
        foreach ($related_tables as $table_info) {
            try {
                // בדיקה אם הטבלה קיימת
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table_info['table']]);
                
                if ($stmt->rowCount() > 0) {
                    // מחיקת נתונים משויכים
                    $delete_sql = "DELETE FROM " . $table_info['table'] . " WHERE " . $table_info['field'] . " = ?";
                    $stmt = $pdo->prepare($delete_sql);
                    $stmt->execute([$user_id]);
                    
                    // רישום לוג
                    error_log("מחיקת נתונים מטבלה " . $table_info['table'] . " עבור משתמש #" . $user_id);
                }
            } catch (PDOException $e) {
                // רישום שגיאה אך המשך התהליך
                error_log("שגיאה במחיקת נתונים מטבלה " . $table_info['table'] . ": " . $e->getMessage());
            }
        }
        
        // 2. מחיקת המשתמש עצמו
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // רישום לוג
        error_log("חשבון משתמש #" . $user_id . " נמחק בהצלחה");
        
        // אישור הטרנזקציה
        $pdo->commit();
        
        // ניקוי הסשן
        session_unset();
        session_destroy();
        
        // הגדרת הודעת הצלחה
        $_SESSION['account_success'] = 'החשבון נמחק בהצלחה. תודה שהשתמשת בשירותינו.';
        
        // הפניה לעמוד הבית או דף רישום
        redirect(SITE_URL . '/auth/register.php?account_deleted=true');
        exit;
        
    } catch (PDOException $e) {
        // ביטול הטרנזקציה במקרה של שגיאה
        $pdo->rollBack();
        
        // רישום השגיאה
        error_log("שגיאה במחיקת חשבון משתמש #" . $user_id . ": " . $e->getMessage());
        
        // הצגת הודעת שגיאה למשתמש
        $_SESSION['account_error'] = 'אירעה שגיאה במחיקת החשבון. אנא נסה שוב או צור קשר עם התמיכה.';
        redirect(SITE_URL . '/admin/profile.php');
        exit;
    }
} else {
    // גישה ישירה לעמוד (לא דרך POST) - הפניה לעמוד הפרופיל
    redirect(SITE_URL . '/admin/profile.php');
    exit;
} 
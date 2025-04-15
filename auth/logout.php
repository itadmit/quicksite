<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../config/init.php';

// הוספת לוג התנתקות אם המשתמש מחובר
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'] ?? null;
    
    // עדכון זמן התחברות אחרון בבסיס הנתונים
    if ($user_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("שגיאה בעדכון זמן התחברות אחרון: " . $e->getMessage());
        }
    }
    
    // רישום לוג התנתקות
    error_log("משתמש ID: $user_id התנתק בהצלחה");
}

// ניקוי משתני הסשן
$_SESSION = array();

// מחיקת קוקי הסשן אם קיים
if (ini_get("session.use_cookies")) {
    $params = session.cookie_params;
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// סיום הסשן
session_destroy();

// מחיקת משתני cookie נוספים אם קיימים
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}

// הפניה לדף הכניסה
header('Location: ' . SITE_URL . '/auth/login.php?logout=success');
exit;
?>
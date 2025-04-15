<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות והתחברות
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// אתחול המערכת - לאחר טעינת קבצי התצורה שמגדירים את SESSION_LIFETIME
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'cookie_httponly' => true,
    'cookie_secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
    'cookie_samesite' => 'Lax',
    'gc_maxlifetime' => SESSION_LIFETIME
]);

// הגדרת אזור זמן
date_default_timezone_set('Asia/Jerusalem');

// הגדרת charset להודעות
header('Content-Type: text/html; charset=utf-8');

// טעינת ספריות ופונקציות
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/templates.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/contacts.php';
require_once __DIR__ . '/../includes/landing_pages.php';

// הגדרת ניהול שגיאות בהתאם לסביבה
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // הגדרת פונקציית טיפול בשגיאות
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // רישום השגיאה ללוג
        error_log("Error [$errno]: $errstr in $errfile on line $errline");
        
        // אם זו שגיאה קריטית, הפנה לדף שגיאה
        if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            header('Location: ' . SITE_URL . '/error.php');
            exit;
        }
        
        return true; // אפשר לPHP להמשיך בטיפול בשגיאה
    });
}

// יצירת CSRF token אם לא קיים
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// פונקציית עזר להפקת CSRF token
function csrf_token() {
    return $_SESSION[CSRF_TOKEN_NAME];
}

// פונקציית עזר לבדיקת CSRF token
function csrf_verify($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || $token !== $_SESSION[CSRF_TOKEN_NAME]) {
        die("CSRF token mismatch");
    }
    return true;
}

// בדיקה אם המשתמש מחובר ושמירת פרטי המשתמש בהתאם
$current_user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch();
        
        if ($current_user) {
            // עדכון זמן הכניסה האחרון אם עברה יותר מדקה מאז העדכון האחרון
            if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity']) > 60) {
                $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->execute([$current_user['id']]);
                $_SESSION['last_activity'] = time();
            }
        } else {
            // אם המשתמש לא נמצא או לא פעיל, מחק את הסשן
            session_unset();
            session_destroy();
        }
    } catch (PDOException $e) {
        // שגיאה בגישה למסד הנתונים, טיפול בהתאם לסביבה
        if (ENVIRONMENT === 'development') {
            die("שגיאה בטעינת פרטי המשתמש: " . $e->getMessage());
        } else {
            error_log("שגיאה בטעינת פרטי המשתמש: " . $e->getMessage());
            session_unset();
            session_destroy();
        }
    }
}

// בדיקה אם התקבלה בקשת AJAX
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// פונקציה להחזרת תשובת JSON לבקשות AJAX
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// חיבור למסד הנתונים
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    die("שגיאת התחברות למסד הנתונים: " . $e->getMessage());
}
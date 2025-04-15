<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../config/init.php';

// אם המשתמש כבר מחובר, הפנה לדשבורד
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

// בדיקה אם יש עוגיית זכירה
check_remember_login();

// כותרת הדף
$page_title = 'התחברות למערכת';

// משתנים לטופס
$email = '';
$error = '';

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
        
        // ביצוע ההתחברות
        $login_result = login($email, $password, $remember);
        
        if ($login_result['success']) {
            // התחברות הצליחה
            set_flash_message('ברוכים הבאים למערכת!', 'success');
            
            // הפניה לדף המתאים (דשבורד או ישירות לדף שביקשו)
            $redirect_url = isset($_SESSION['redirect_after_login']) ? 
                $_SESSION['redirect_after_login'] : 
                SITE_URL . '/admin/dashboard.php';
            
            // מחיקת כתובת ההפניה מהסשן
            if (isset($_SESSION['redirect_after_login'])) {
                unset($_SESSION['redirect_after_login']);
            }
            
            redirect($redirect_url);
        } else {
            // התחברות נכשלה
            $error = $login_result['message'];
        }
    }
}

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form class="space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="login-form">
            <!-- CSRF token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-r-4 border-red-500 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-error-warning-line text-red-500"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-red-700"><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">כתובת דוא"ל</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           value="<?php echo htmlspecialchars($email); ?>"
                           class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">סיסמה</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" value="1" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember" class="mr-2 block text-sm text-gray-900 flex items-center">
      
                        זכור אותי
                    </label>
                </div>

                <div class="text-sm">
                    <a href="<?php echo SITE_URL; ?>/auth/reset-password.php" class="font-medium text-indigo-600 hover:text-indigo-500 flex items-center">
                        <i class="ri-question-line ml-1"></i>
                        שכחת סיסמה?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="ri-login-box-line ml-2"></i>
                    התחברות
                </button>
            </div>
        </form>
        
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">
                        או
                    </span>
                </div>
            </div>

            <div class="mt-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        אין לך חשבון?
                        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="font-medium text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                            הירשם עכשיו
                            <i class="ri-user-add-line mr-1"></i>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';
?>
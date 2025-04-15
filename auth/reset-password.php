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

// כותרת הדף
$page_title = 'איפוס סיסמה';

// משתנים
$email = '';
$code = '';
$error = '';
$success = '';
$show_request_form = true;
$show_reset_form = false;

// בדיקה אם יש קוד איפוס בכתובת
if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = trim($_GET['email']);
    $code = trim($_GET['code']);
    
    // בדיקה שהפרמטרים לא ריקים
    if (!empty($email) && !empty($code)) {
        $show_request_form = false;
        $show_reset_form = true;
    }
}

// טיפול בטופס בקשת איפוס
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_reset'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $email = trim($_POST['email'] ?? '');
        
        // בדיקה שהאימייל לא ריק
        if (empty($email)) {
            $error = 'יש להזין כתובת דוא"ל';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'כתובת הדוא"ל אינה תקינה';
        } else {
            // שליחת קוד איפוס
            $result = send_password_reset($email);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// טיפול בטופס איפוס סיסמה
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $email = trim($_POST['email'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // בדיקת שדות חובה
        if (empty($email) || empty($code) || empty($new_password) || empty($confirm_password)) {
            $error = 'יש למלא את כל השדות';
        }
        // בדיקת תקינות אימייל
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'כתובת הדוא"ל אינה תקינה';
        }
        // בדיקת התאמת סיסמאות
        elseif ($new_password !== $confirm_password) {
            $error = 'הסיסמאות אינן תואמות';
        }
        // בדיקת אורך סיסמה
        elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = 'הסיסמה חייבת להכיל לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        }
        else {
            // איפוס הסיסמה
            $result = reset_password($email, $code, $new_password);
            
            if ($result['success']) {
                $success = $result['message'];
                $show_reset_form = false; // הסתר את הטופס לאחר איפוס מוצלח
            } else {
                $error = $result['message'];
            }
        }
    }
}

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-r-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-checkbox-circle-line text-green-500"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm text-green-700"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-6">
                <?php if (!$show_reset_form): ?>
                    <p class="mb-4">סיסמתך אופסה בהצלחה. כעת תוכל להתחבר עם הסיסמה החדשה.</p>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        עבור לדף ההתחברות
                    </a>
                <?php else: ?>
                    <p class="mb-4">אם כתובת הדוא"ל קיימת במערכת, נשלח אליך דוא"ל עם הוראות לאיפוס הסיסמה.</p>
                    <p>לא קיבלת את הדוא"ל? בדוק בתיקיית הספאם או <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="text-indigo-600 hover:text-indigo-500">נסה שוב</a>.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($show_request_form): ?>
            <h3 class="mb-4 text-lg font-medium text-gray-900">בקשת איפוס סיסמה</h3>
            
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
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
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
                    <button type="submit" name="request_reset" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-key-line ml-2"></i>
                        שלח קישור לאיפוס סיסמה
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                    חזרה לדף ההתחברות
                </a>
            </div>
        <?php elseif ($show_reset_form): ?>
            <h3 class="mb-4 text-lg font-medium text-gray-900">הגדרת סיסמה חדשה</h3>
            
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
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">סיסמה חדשה</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-lock-password-line text-gray-400"></i>
                        </div>
                        <input id="new_password" name="new_password" type="password" required 
                               class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">הסיסמה חייבת להכיל לפחות <?php echo PASSWORD_MIN_LENGTH; ?> תווים</p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">אימות סיסמה</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-checkbox-circle-line text-gray-400"></i>
                        </div>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <button type="submit" name="reset_password" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-refresh-line ml-2"></i>
                        אפס סיסמה
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';
?>
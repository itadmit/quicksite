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
$page_title = 'הרשמה למערכת';

// משתנים לטופס
$user_data = [
    'email' => '',
    'first_name' => '',
    'last_name' => '',
    'company_name' => '',
    'phone' => ''
];

$error = '';
$success = '';

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $user_data = [
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // בדיקת תקינות
        $validation_errors = [];
        
        // בדיקת אימייל
        if (empty($user_data['email'])) {
            $validation_errors[] = 'כתובת דוא"ל היא שדה חובה';
        } elseif (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
            $validation_errors[] = 'כתובת הדוא"ל אינה תקינה';
        }
        
        // בדיקת סיסמה
        if (empty($user_data['password'])) {
            $validation_errors[] = 'סיסמה היא שדה חובה';
        } elseif (strlen($user_data['password']) < PASSWORD_MIN_LENGTH) {
            $validation_errors[] = 'הסיסמה חייבת להכיל לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        } elseif ($user_data['password'] != $user_data['password_confirm']) {
            $validation_errors[] = 'הסיסמאות אינן תואמות';
        }
        
        // בדיקת שם פרטי
        if (empty($user_data['first_name'])) {
            $validation_errors[] = 'שם פרטי הוא שדה חובה';
        }
        
        // בדיקת שם משפחה
        if (empty($user_data['last_name'])) {
            $validation_errors[] = 'שם משפחה הוא שדה חובה';
        }
        
        // אם אין שגיאות, בצע הרשמה
        if (empty($validation_errors)) {
            $register_result = register($user_data);
            
            if ($register_result['success']) {
                // הרשמה הצליחה
                $success = $register_result['message'];
                // נקה את נתוני הטופס
                $user_data = [
                    'email' => '',
                    'first_name' => '',
                    'last_name' => '',
                    'company_name' => '',
                    'phone' => ''
                ];
            } else {
                // הרשמה נכשלה
                $error = $register_result['message'];
            }
        } else {
            // הוסף את שגיאות האימות להודעת השגיאה
            $error = implode('<br>', $validation_errors);
        }
    }
}

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <?php if ($success): ?>
            <div class="bg-green-50 border-r-4 border-green-500 p-4 mb-4">
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
                <p class="mb-4 flex items-center justify-center">
                    <i class="ri-mail-send-line ml-2 text-indigo-500"></i>
                    תודה שנרשמת! שלחנו לך אימייל עם קישור לאימות החשבון.
                </p>
                <p>לא קיבלת את האימייל? בדוק בתיקיית הספאם או <a href="<?php echo SITE_URL; ?>/auth/resend-verification.php" class="text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                    שלח שוב
                    <i class="ri-refresh-line mr-1"></i>
                </a>.</p>
                <p class="mt-6">
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="font-medium text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                        <i class="ri-arrow-right-line ml-1"></i>
                        חזרה לדף ההתחברות
                    </a>
                </p>
            </div>
        <?php else: ?>
            <form class="space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="register-form">
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
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">שם פרטי *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-user-line text-gray-400"></i>
                            </div>
                            <input id="first_name" name="first_name" type="text" autocomplete="given-name" required 
                                  value="<?php echo htmlspecialchars($user_data['first_name']); ?>"
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">שם משפחה *</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-user-line text-gray-400"></i>
                            </div>
                            <input id="last_name" name="last_name" type="text" autocomplete="family-name" required 
                                  value="<?php echo htmlspecialchars($user_data['last_name']); ?>"
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">כתובת דוא"ל *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-mail-line text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                              value="<?php echo htmlspecialchars($user_data['email']); ?>"
                              class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">שם החברה</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-building-line text-gray-400"></i>
                        </div>
                        <input id="company_name" name="company_name" type="text" autocomplete="organization" 
                              value="<?php echo htmlspecialchars($user_data['company_name']); ?>"
                              class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">טלפון</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-phone-line text-gray-400"></i>
                        </div>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" 
                              value="<?php echo htmlspecialchars($user_data['phone']); ?>"
                              class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">סיסמה *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-lock-password-line text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                              class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 flex items-center">
                        <i class="ri-information-line ml-1"></i>
                        הסיסמה חייבת להכיל לפחות <?php echo PASSWORD_MIN_LENGTH; ?> תווים
                    </p>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">אימות סיסמה *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="ri-checkbox-circle-line text-gray-400"></i>
                        </div>
                        <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required 
                              class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required
                          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="terms" class="mr-2 block text-sm text-gray-900">
                        אני מסכים/ה <a href="<?php echo SITE_URL; ?>/terms.php" class="text-indigo-600 hover:text-indigo-500" target="_blank"> לתנאי השימוש </a> ו <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-indigo-600 hover:text-indigo-500" target="_blank"> מדיניות הפרטיות </a>
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-user-add-line ml-2"></i>
                        הרשמה
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
                            כבר יש לך חשבון?
                            <a href="<?php echo SITE_URL; ?>/auth/login.php" class="font-medium text-indigo-600 hover:text-indigo-500 inline-flex items-center">
                                התחבר/י כאן
                                <i class="ri-login-box-line mr-1"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';
?>
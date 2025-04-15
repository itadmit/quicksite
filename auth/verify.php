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
$page_title = 'אימות חשבון';

// משתנים
$email = '';
$code = '';
$error = '';
$success = '';
$verified = false;

// בדיקה אם יש פרמטרים בכתובת
if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = trim($_GET['email']);
    $code = trim($_GET['code']);
    
    // בדיקה שהפרמטרים לא ריקים
    if (empty($email) || empty($code)) {
        $error = 'פרמטרים חסרים בקישור האימות.';
    } else {
        // בדיקה שהאימייל תקין
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'כתובת הדוא"ל אינה תקינה.';
        } else {
            // ביצוע האימות
            if (verify_account($email, $code)) {
                $success = 'החשבון אומת בהצלחה! כעת תוכל להתחבר למערכת.';
                $verified = true;
            } else {
                $error = 'קישור האימות אינו תקף או שפג תוקפו.';
            }
        }
    }
} else {
    $error = 'פרמטרים חסרים בקישור האימות.';
}

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <h3 class="mb-4 text-lg font-medium text-gray-900">אימות חשבון</h3>
        
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
                <p class="mb-4">חשבונך אומת בהצלחה!</p>
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="ri-login-box-line ml-2"></i>
                    התחבר למערכת
                </a>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="bg-red-50 border-r-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-error-warning-line text-red-500"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm text-red-700"><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-6">
                <p class="mb-4">לא הצלחנו לאמת את חשבונך. ייתכן שפג תוקף הקישור או שכבר אימתת את החשבון בעבר.</p>
                <p class="mb-4">רוצה לקבל קישור אימות חדש?</p>
                <a href="<?php echo SITE_URL; ?>/auth/resend-verification.php" class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="ri-refresh-line ml-2"></i>
                    שלח קישור אימות חדש
                </a>
                <p class="mt-4">
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                        חזרה לדף ההתחברות
                    </a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';
?>
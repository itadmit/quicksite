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
$page_title = 'שליחת קישור אימות חדש';

// משתנים
$email = '';
$error = '';
$success = '';

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            // שליחת קישור אימות חדש
            $result = resend_verification_email($email);
            
            if ($result['success']) {
                $success = $result['message'];
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
        <h3 class="mb-4 text-lg font-medium text-gray-900">שליחת קישור אימות חדש</h3>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-50 border-r-4 border-green-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-mail-check-line text-green-500"></i>
                    </div>
                    <div class="mr-3">
                        <p class="text-sm text-green-700"><?php echo $success; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-6">
                <p class="mb-4">שלחנו קישור אימות חדש לכתובת הדוא"ל שהזנת.</p>
                <p>לא קיבלת את הדוא"ל? בדוק בתיקיית הספאם או <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="text-indigo-600 hover:text-indigo-500">נסה שוב</a>.</p>
                <p class="mt-4">
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                        חזרה לדף ההתחברות
                    </a>
                </p>
            </div>
        <?php else: ?>
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
                    <button type="submit" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-mail-send-line ml-2"></i>
                        שלח קישור אימות חדש
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                    חזרה לדף ההתחברות
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';

/**
 * שליחת דוא"ל אימות חדש
 * 
 * @param string $email כתובת הדוא"ל
 * @return array תוצאות הפעולה [success, message]
 */
function resend_verification_email($email) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // בדיקה אם המשתמש קיים
        $stmt = $pdo->prepare("
            SELECT id, is_verified, status, first_name 
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // שקר לבן מטעמי אבטחה - אל תודיע שהאימייל לא קיים במערכת
            $result['success'] = true;
            $result['message'] = 'אם כתובת האימייל קיימת במערכת, נשלח אליך דוא"ל חדש עם קישור לאימות.';
            return $result;
        }
        
        // בדיקה אם החשבון כבר אומת
        if ($user['is_verified']) {
            $result['success'] = true;
            $result['message'] = 'החשבון כבר אומת. ניתן להתחבר למערכת.';
            return $result;
        }
        
        // בדיקה אם החשבון פעיל
        if ($user['status'] !== 'active') {
            $result['message'] = 'החשבון אינו פעיל. אנא פנה לתמיכה.';
            return $result;
        }
        
        // יצירת קוד אימות חדש
        $verification_code = generate_token('verification', 32, 48); // תוקף של 48 שעות
        
        // עדכון קוד האימות
        $update = $pdo->prepare("
            UPDATE users 
            SET verification_code = ? 
            WHERE id = ?
        ");
        $update->execute([$verification_code, $user['id']]);
        
        // שליחת אימייל אימות
        $verification_url = SITE_URL . "/auth/verify.php?email=" . urlencode($email) . "&code=" . urlencode($verification_code);
        
        $subject = SITE_NAME . " - אימות חשבון";
        
        $message = "
        <html dir='rtl'>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4f46e5; color: white; padding: 10px 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; 
                          text-decoration: none; border-radius: 5px; }
                .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . SITE_NAME . "</h2>
                </div>
                <div class='content'>
                    <h3>שלום " . $user['first_name'] . ",</h3>
                    <p>קיבלנו בקשה לשלוח קישור אימות חדש לחשבונך ב-" . SITE_NAME . ".</p>
                    <p>כדי לאמת את כתובת האימייל שלך, אנא לחץ על הכפתור למטה:</p>
                    <p style='text-align: center;'>
                        <a href='" . $verification_url . "' class='button'>אמת את החשבון שלי</a>
                    </p>
                    <p>או העתק את הקישור הבא לדפדפן שלך:</p>
                    <p>" . $verification_url . "</p>
                    <p>קישור זה יהיה בתוקף למשך 48 שעות.</p>
                    <p>אם לא ביקשת קישור אימות חדש, אנא התעלם מהודעה זו.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". כל הזכויות שמורות.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // כותרות אימייל
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // שליחת האימייל
        $mail_sent = mail($email, $subject, $message, implode("\r\n", $headers));
        
        if (!$mail_sent) {
            $result['message'] = 'שגיאה בשליחת האימייל, אנא נסה שוב מאוחר יותר';
            return $result;
        }
        
        $result['success'] = true;
        $result['message'] = 'אם כתובת האימייל קיימת במערכת, נשלח אליך דוא"ל חדש עם קישור לאימות.';
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה במערכת, אנא נסה שוב מאוחר יותר';
        error_log("שגיאה בשליחת אימייל אימות חדש: " . $e->getMessage());
        return $result;
    }
}
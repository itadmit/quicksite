<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// כותרת הדף
$page_title = 'פרופיל משתמש';

// משתנים
$error = '';
$success = '';
$profile_data = $current_user;

// בדיקה להודעות ממחיקת חשבון
if (isset($_SESSION['account_error'])) {
    $error = $_SESSION['account_error'];
    unset($_SESSION['account_error']);
}

if (isset($_SESSION['account_success'])) {
    $success = $_SESSION['account_success'];
    unset($_SESSION['account_success']);
}

// טיפול בעדכון פרטים
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // בדיקת שדות חובה
        if (empty($first_name) || empty($last_name)) {
            $error = 'יש למלא את כל שדות החובה';
        } else {
            // עדכון הפרטים
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, company_name = ?, phone = ? 
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $company_name,
                    $phone,
                    $current_user['id']
                ]);
                
                $success = 'הפרטים עודכנו בהצלחה';
                
                // עדכון נתוני המשתמש בסשן
                $profile_data['first_name'] = $first_name;
                $profile_data['last_name'] = $last_name;
                $profile_data['company_name'] = $company_name;
                $profile_data['phone'] = $phone;
                
                // עדכון משתנה המשתמש הנוכחי
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$current_user['id']]);
                $current_user = $stmt->fetch();
                
            } catch (PDOException $e) {
                $error = 'אירעה שגיאה בעדכון הפרטים, אנא נסה שוב';
                error_log("שגיאה בעדכון פרופיל: " . $e->getMessage());
            }
        }
    }
}

// טיפול בעדכון סיסמה
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // קבלת נתוני הטופס
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // בדיקת שדות חובה
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'יש למלא את כל השדות';
        }
        // בדיקת תאימות סיסמאות
        elseif ($new_password !== $confirm_password) {
            $error = 'הסיסמאות החדשות אינן תואמות';
        }
        // בדיקת אורך סיסמה
        elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $error = 'הסיסמה החדשה חייבת להכיל לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        }
        else {
            // עדכון הסיסמה
            $result = update_password($current_user['id'], $current_password, $new_password);
            
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

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg leading-6 font-medium text-gray-900">פרופיל משתמש</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">עדכון פרטים אישיים וסיסמה</p>
        </div>
        <div>
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                <i class="ri-arrow-right-line ml-2"></i>
                חזרה לדשבורד
            </a>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="bg-red-50 border-r-4 border-red-500 p-4 mx-6 mb-4">
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
    
    <?php if (!empty($success)): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 mx-6 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-checkbox-circle-line text-green-500"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm text-green-700"><?php echo $success; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- טופס עדכון פרטים -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 mb-4">פרטים אישיים</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">שם פרטי *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="ri-user-line text-gray-400"></i>
                                </div>
                                <input id="first_name" name="first_name" type="text" required 
                                      value="<?php echo htmlspecialchars($profile_data['first_name']); ?>"
                                      class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">שם משפחה *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="ri-user-line text-gray-400"></i>
                                </div>
                                <input id="last_name" name="last_name" type="text" required 
                                      value="<?php echo htmlspecialchars($profile_data['last_name']); ?>"
                                      class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">כתובת דוא"ל</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-mail-line text-gray-400"></i>
                            </div>
                            <input id="email" type="email" readonly 
                                  value="<?php echo htmlspecialchars($profile_data['email']); ?>"
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-200 rounded-md shadow-sm bg-gray-100 text-gray-500 focus:outline-none sm:text-sm">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">לא ניתן לשנות את כתובת הדוא"ל</p>
                    </div>
                    
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700">שם החברה</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-building-line text-gray-400"></i>
                            </div>
                            <input id="company_name" name="company_name" type="text" 
                                  value="<?php echo htmlspecialchars($profile_data['company_name'] ?? ''); ?>"
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">טלפון</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-phone-line text-gray-400"></i>
                            </div>
                            <input id="phone" name="phone" type="tel" 
                                  value="<?php echo htmlspecialchars($profile_data['phone'] ?? ''); ?>"
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" name="update_profile" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="ri-save-line ml-2"></i>
                            עדכן פרטים
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- טופס עדכון סיסמה -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 mb-4">עדכון סיסמה</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-6">
                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">סיסמה נוכחית</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="ri-lock-line text-gray-400"></i>
                            </div>
                            <input id="current_password" name="current_password" type="password" required 
                                  class="appearance-none block w-full pr-10 px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    
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
                        <button type="submit" name="update_password" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="ri-lock-password-line ml-2"></i>
                            עדכן סיסמה
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-100 px-4 py-4 sm:px-6">
        <div class="text-sm flex justify-between items-center">
            <div>
                <p class="text-gray-500">חשבון נוצר ב: <span class="text-gray-900"><?php echo format_date($profile_data['created_at'], 'd/m/Y'); ?></span></p>
                <p class="text-gray-500">כניסה אחרונה: <span class="text-gray-900"><?php echo format_date($profile_data['last_login'], 'd/m/Y H:i'); ?></span></p>
            </div>
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 hover:text-indigo-500">
                    <i class="ri-vip-crown-line ml-1"></i>
                    פרטי המנוי שלי
                </a>
            </div>
        </div>
    </div>
</div>

<!-- מחיקת חשבון -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">מחיקת חשבון</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">פעולה זו תמחק באופן קבוע את החשבון שלך וכל המידע המשויך אליו</p>
    </div>
    <div class="px-4 py-5 sm:p-6 bg-gray-50">
        <div class="border-2 border-red-200 rounded-lg p-4 bg-red-50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-error-warning-fill text-red-600 text-xl"></i>
                </div>
                <div class="mr-3">
                    <h3 class="text-sm font-medium text-red-800">אזהרה: פעולה זו לא ניתנת לביטול</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>מחיקת החשבון תסיר לצמיתות:</p>
                        <ul class="mt-1 list-disc mr-5 space-y-1">
                            <li>את כל הנתונים האישיים שלך</li>
                            <li>את כל דפי הנחיתה שיצרת</li>
                            <li>את כל הקמפיינים והודעות שנשלחו</li>
                            <li>את כל אנשי הקשר ורשימות התפוצה</li>
                            <li>את כל ההגדרות והמנויים</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button type="button" id="delete-account-btn" class="inline-flex items-center justify-center px-4 py-2 border border-transparent font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm">
                    <i class="ri-delete-bin-line ml-2"></i>
                    מחק את החשבון שלי
                </button>
            </div>
        </div>
    </div>
</div>

<!-- חלונית אישור מחיקת חשבון -->
<div id="delete-account-modal" class="fixed inset-0 overflow-y-auto hidden" style="z-index: 50;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="ri-error-warning-fill text-red-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">אישור מחיקת חשבון</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">האם אתה בטוח שברצונך למחוק את החשבון שלך? פעולה זו לא ניתנת לביטול ותמחק את כל הנתונים שלך.</p>
                        </div>
                        <div class="mt-4">
                            <div class="flex flex-col">
                                <label for="confirm_delete_email" class="block text-sm font-medium text-gray-700 text-right mb-1">הזן את כתובת הדוא"ל שלך לאישור</label>
                                <input type="email" id="confirm_delete_email" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="email@example.com">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form action="<?php echo SITE_URL; ?>/admin/account/delete.php" method="POST" id="delete-account-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                    <button type="submit" id="confirm-delete-btn" disabled class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm opacity-50 cursor-not-allowed">
                        מחק את החשבון
                    </button>
                </form>
                <button type="button" id="cancel-delete-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    ביטול
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// מחיקת חשבון - פתיחת חלונית
document.getElementById('delete-account-btn').addEventListener('click', function() {
    document.getElementById('delete-account-modal').classList.remove('hidden');
});

// מחיקת חשבון - סגירת חלונית
document.getElementById('cancel-delete-btn').addEventListener('click', function() {
    document.getElementById('delete-account-modal').classList.add('hidden');
    document.getElementById('confirm_delete_email').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
    document.getElementById('confirm-delete-btn').classList.add('opacity-50', 'cursor-not-allowed');
});

// בדיקת כתובת אימייל לאישור מחיקה
document.getElementById('confirm_delete_email').addEventListener('input', function() {
    const userEmail = '<?php echo htmlspecialchars($profile_data['email']); ?>';
    const confirmBtn = document.getElementById('confirm-delete-btn');
    
    if (this.value === userEmail) {
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        confirmBtn.disabled = true;
        confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});
</script>

<?php
// טעינת תבנית העיצוב - פוטר
include_once '../includes/footer.php';
?>
<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// כותרת הדף
$page_title = 'חידוש מנוי';

// קבלת פרטי המנוי הפעיל
$active_subscription = get_active_subscription($current_user['id']);

// בדיקה שיש מנוי פעיל
if (!$active_subscription) {
    set_flash_message('אין לך מנוי פעיל. אנא רכוש מנוי חדש.', 'warning');
    redirect(SITE_URL . '/admin/subscription.php');
}

// קבלת פרטי התוכנית הנוכחית
try {
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ? AND status = 'active'");
    $stmt->execute([$active_subscription['plan_id']]);
    $current_plan = $stmt->fetch();
    
    if (!$current_plan) {
        set_flash_message('לא נמצאה תוכנית המנוי הנוכחית', 'error');
        redirect(SITE_URL . '/admin/subscription.php');
    }
} catch (PDOException $e) {
    error_log("שגיאה בקבלת פרטי תוכנית: " . $e->getMessage());
    set_flash_message('אירעה שגיאה בקבלת פרטי המנוי', 'error');
    redirect(SITE_URL . '/admin/subscription.php');
}

// חישוב תאריך סיום חדש
$new_end_date = date('Y-m-d', strtotime($active_subscription['end_date'] . " + 1 month"));

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        set_flash_message('שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.', 'error');
        redirect(SITE_URL . '/admin/payment/renew.php');
    }
    
    // בדיקה אם להפעיל חידוש אוטומטי
    $auto_renew = isset($_POST['auto_renew']) && $_POST['auto_renew'] == 1;
    
    // כאן המערכת תבצע את התשלום בפועל באמצעות שער תשלום
    // לצורך הדוגמה נדלג על הטיפול בכרטיס אשראי ונחדש את המנוי באופן מיידי
    
    try {
        // חידוש המנוי עם תאריך חדש
        $stmt = $pdo->prepare("
            UPDATE subscriptions 
            SET end_date = ?, auto_renew = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$new_end_date, $auto_renew ? 1 : 0, $active_subscription['id']]);
        
        // הוספת רשומה חדשה להיסטוריית תשלום
        $stmt = $pdo->prepare("
            INSERT INTO payment_history (
                user_id, subscription_id, amount, payment_method, status, payment_date, 
                description, created_at
            ) VALUES (
                ?, ?, ?, 'credit_card', 'completed', NOW(),
                'חידוש מנוי לתוכנית: " . $current_plan['name'] . "', NOW()
            )
        ");
        $stmt->execute([
            $current_user['id'], 
            $active_subscription['id'],
            $current_plan['price']
        ]);
        
        // הודעת הצלחה
        set_flash_message('המנוי חודש בהצלחה!', 'success');
        redirect(SITE_URL . '/admin/subscription.php?plan_renewed=true');
        
    } catch (PDOException $e) {
        error_log("שגיאה בחידוש מנוי: " . $e->getMessage());
        set_flash_message('אירעה שגיאה בחידוש המנוי. אנא נסה שוב.', 'error');
        redirect(SITE_URL . '/admin/payment/renew.php');
    }
}

// טעינת תבנית העיצוב - הדר
include_once '../../includes/header.php';
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h2 class="text-lg leading-6 font-medium text-gray-900">חידוש מנוי</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">אנא מלא את פרטי התשלום לחידוש המנוי</p>
            </div>
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                    <i class="ri-arrow-right-line ml-2"></i>
                    חזרה למנוי
                </a>
            </div>
        </div>
        
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">סיכום המנוי</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">שם התוכנית:</p>
                        <p class="font-medium"><?php echo htmlspecialchars($current_plan['name']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">תעריף חודשי:</p>
                        <p class="font-medium"><?php echo number_format($current_plan['price'], 2); ?> ₪</p>
                    </div>
                    <div>
                        <p class="text-gray-500">תאריך סיום נוכחי:</p>
                        <p class="font-medium"><?php echo format_date($active_subscription['end_date']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500">תאריך סיום חדש (לאחר חידוש):</p>
                        <p class="font-medium"><?php echo format_date($new_end_date); ?></p>
                    </div>
                </div>
            </div>
            
            <form method="POST" id="payment-form" class="space-y-6">
                <!-- CSRF token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <!-- פרטי כרטיס אשראי -->
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">פרטי תשלום</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="card_number" class="block text-sm font-medium text-gray-700">מספר כרטיס</label>
                            <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700">תוקף</label>
                                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                            <div>
                                <label for="cvc" class="block text-sm font-medium text-gray-700">קוד אבטחה (CVV)</label>
                                <input type="text" id="cvc" name="cvc" placeholder="123" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="card_holder" class="block text-sm font-medium text-gray-700">שם בעל הכרטיס</label>
                            <input type="text" id="card_holder" name="card_holder" placeholder="ישראל ישראלי" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                        
                        <div>
                            <label for="id_number" class="block text-sm font-medium text-gray-700">מספר ת.ז.</label>
                            <input type="text" id="id_number" name="id_number" placeholder="000000000" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                    </div>
                </div>
                
                <!-- הגדרות חידוש אוטומטי -->
                <div class="flex items-center">
                    <input id="auto_renew" name="auto_renew" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" <?php echo $active_subscription['auto_renew'] ? 'checked' : ''; ?>>
                    <label for="auto_renew" class="mr-2 block text-sm text-gray-900">
                        הפעל חידוש אוטומטי חודשי
                    </label>
                </div>
                <p class="text-xs text-gray-500">
                    בהפעלת חידוש אוטומטי, המנוי שלך יחודש אוטומטית בכל חודש באותו סכום. תוכל לבטל את החידוש האוטומטי בכל זמן.
                </p>
                
                <!-- סיכום תשלום -->
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>סה"כ לתשלום:</span>
                        <span class="text-indigo-600"><?php echo number_format($current_plan['price'], 2); ?> ₪</span>
                    </div>
                </div>
                
                <!-- כפתור תשלום -->
                <div>
                    <button type="submit" class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-secure-payment-line ml-2"></i>
                        שלם וחדש מנוי
                    </button>
                </div>
                
                <p class="text-xs text-gray-500 text-center">
                    התשלום מאובטח ומוצפן. אנו לא שומרים את פרטי כרטיס האשראי שלך.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// ולידציה בסיסית של טופס התשלום
document.getElementById('payment-form').addEventListener('submit', function(e) {
    // בדיקת מספר כרטיס
    const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
    if (!/^\d{16}$/.test(cardNumber)) {
        alert('אנא הזן מספר כרטיס תקין (16 ספרות)');
        e.preventDefault();
        return;
    }
    
    // בדיקת תוקף
    const expiry = document.getElementById('expiry_date').value;
    if (!/^\d{2}\/\d{2}$/.test(expiry)) {
        alert('אנא הזן תאריך תוקף תקין (MM/YY)');
        e.preventDefault();
        return;
    }
    
    // בדיקת CVV
    const cvc = document.getElementById('cvc').value;
    if (!/^\d{3,4}$/.test(cvc)) {
        alert('אנא הזן קוד אבטחה תקין (3-4 ספרות)');
        e.preventDefault();
        return;
    }
    
    // בדיקת ת.ז.
    const idNumber = document.getElementById('id_number').value.replace(/\s/g, '');
    if (!/^\d{9}$/.test(idNumber)) {
        alert('אנא הזן מספר ת.ז. תקין (9 ספרות)');
        e.preventDefault();
        return;
    }
});

// עיצוב שדה מספר כרטיס אשראי
document.getElementById('card_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 16) {
        value = value.substr(0, 16);
    }
    
    // הוספת רווח כל 4 ספרות
    const parts = [];
    for (let i = 0; i < value.length; i += 4) {
        parts.push(value.substr(i, 4));
    }
    
    e.target.value = parts.join(' ');
});

// עיצוב שדה תוקף
document.getElementById('expiry_date').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substr(0, 4);
    }
    
    if (value.length > 2) {
        value = value.substr(0, 2) + '/' + value.substr(2);
    }
    
    e.target.value = value;
});

// עיצוב שדה CVC
document.getElementById('cvc').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substr(0, 4);
    }
    
    e.target.value = value;
});
</script>

<?php include_once '../../includes/footer.php'; ?>
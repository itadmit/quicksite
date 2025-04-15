<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// קבלת פרמטרים
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($invoice_id <= 0) {
    set_flash_message('מזהה חשבונית לא תקין', 'error');
    redirect(SITE_URL . '/admin/subscription.php');
}

// קבלת פרטי החשבונית
try {
    $stmt = $pdo->prepare("
        SELECT ph.*, s.plan_id, p.name as plan_name
        FROM payment_history ph
        JOIN subscriptions s ON ph.subscription_id = s.id
        JOIN plans p ON s.plan_id = p.id
        WHERE ph.id = ? AND ph.user_id = ?
        LIMIT 1
    ");
    $stmt->execute([$invoice_id, $current_user['id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        set_flash_message('חשבונית לא נמצאה או אינה שייכת למשתמש זה', 'error');
        redirect(SITE_URL . '/admin/subscription.php');
    }
    
    // קבלת פרטי המשתמש
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$current_user['id']]);
    $user = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("שגיאה בקבלת פרטי חשבונית: " . $e->getMessage());
    set_flash_message('אירעה שגיאה בטעינת החשבונית', 'error');
    redirect(SITE_URL . '/admin/subscription.php');
}

// כותרת הדף
$page_title = 'חשבונית מס/קבלה #' . $invoice_id;

// בדיקה אם לייצא PDF
$export_pdf = isset($_GET['pdf']) && $_GET['pdf'] == 'true';

if ($export_pdf) {
    // כאן אפשר להוסיף קוד לייצוא ל-PDF
    // לצורך הדוגמה נפנה בחזרה לעמוד הרגיל
    redirect(SITE_URL . '/admin/payment/invoice.php?id=' . $invoice_id);
}

// טעינת תבנית העיצוב - הדר
include_once '../../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h2 class="text-lg leading-6 font-medium text-gray-900">חשבונית מס/קבלה</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">מספר #<?php echo $invoice_id; ?></p>
            </div>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                    <i class="ri-arrow-right-line ml-2"></i>
                    חזרה למנוי
                </a>
                
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                    <i class="ri-printer-line ml-2"></i>
                    הדפס
                </button>
                
                <a href="<?php echo SITE_URL; ?>/admin/payment/invoice.php?id=<?php echo $invoice_id; ?>&pdf=true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="ri-file-download-line ml-2"></i>
                    הורד PDF
                </a>
            </div>
        </div>
        
        <div class="border-t border-gray-200 p-6">
            <!-- פרטי החברה -->
            <div class="flex justify-between mb-8">
                <div>
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="h-8 mb-2">
                    <h3 class="text-lg font-bold"><?php echo SITE_NAME; ?></h3>
                    <p class="text-sm">ח.פ: 515123456</p>
                    <p class="text-sm">רח' הברזל 30, תל אביב</p>
                    <p class="text-sm">טלפון: 03-1234567</p>
                    <p class="text-sm">דוא"ל: <?php echo ADMIN_EMAIL; ?></p>
                </div>
                <div class="text-left">
                    <h3 class="text-lg font-bold mb-2">חשבונית מס/קבלה</h3>
                    <p class="text-sm">מספר: <?php echo $invoice_id; ?></p>
                    <p class="text-sm">תאריך: <?php echo format_date($invoice['payment_date'], 'd/m/Y'); ?></p>
                    <p class="text-sm">סטטוס: <?php echo $invoice['status'] == 'completed' ? 'שולם' : 'ממתין לתשלום'; ?></p>
                </div>
            </div>
            
            <!-- פרטי לקוח -->
            <div class="mb-8">
                <h3 class="text-md font-bold mb-2 bg-gray-100 p-2">פרטי הלקוח</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p>שם: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                        <p>חברה: <?php echo !empty($user['company_name']) ? htmlspecialchars($user['company_name']) : 'לא הוזן'; ?></p>
                    </div>
                    <div>
                        <p>אימייל: <?php echo htmlspecialchars($user['email']); ?></p>
                        <p>טלפון: <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'לא הוזן'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- פירוט הזמנה -->
            <div class="mb-8">
                <h3 class="text-md font-bold mb-2 bg-gray-100 p-2">פרטי ההזמנה</h3>
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-right py-2">#</th>
                            <th class="text-right py-2">תיאור</th>
                            <th class="text-right py-2">כמות</th>
                            <th class="text-right py-2">מחיר יחידה</th>
                            <th class="text-right py-2">סה"כ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="py-2">1</td>
                            <td class="py-2">
                                <?php echo htmlspecialchars($invoice['description'] ?: 'מנוי חודשי - תוכנית ' . $invoice['plan_name']); ?>
                            </td>
                            <td class="py-2">1</td>
                            <td class="py-2"><?php echo number_format($invoice['amount'], 2); ?> ₪</td>
                            <td class="py-2"><?php echo number_format($invoice['amount'], 2); ?> ₪</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- סיכום ותשלום -->
            <div class="flex justify-end mb-8">
                <div class="w-64">
                    <div class="flex justify-between border-b py-2">
                        <span>סכום ביניים:</span>
                        <span><?php echo number_format($invoice['amount'], 2); ?> ₪</span>
                    </div>
                    <div class="flex justify-between border-b py-2">
                        <span>מע"מ (17%):</span>
                        <span><?php echo number_format($invoice['amount'] * 0.17, 2); ?> ₪</span>
                    </div>
                    <div class="flex justify-between font-bold py-2">
                        <span>סה"כ לתשלום:</span>
                        <span><?php echo number_format($invoice['amount'], 2); ?> ₪</span>
                    </div>
                </div>
            </div>
            
            <!-- פרטי תשלום ותנאים -->
            <div class="grid grid-cols-2 gap-8 text-sm">
                <div>
                    <h4 class="font-bold mb-2">פרטי תשלום</h4>
                    <p>אמצעי תשלום: <?php echo $invoice['payment_method'] == 'credit_card' ? 'כרטיס אשראי' : $invoice['payment_method']; ?></p>
                    <p>תאריך תשלום: <?php echo format_date($invoice['payment_date'], 'd/m/Y H:i'); ?></p>
                    <p>סטטוס עסקה: <?php echo $invoice['status'] == 'completed' ? 'הושלם' : $invoice['status']; ?></p>
                    <p>מזהה עסקה: <?php echo isset($invoice['transaction_id']) ? $invoice['transaction_id'] : $invoice_id; ?></p>
                </div>
                <div>
                    <h4 class="font-bold mb-2">תנאים והערות</h4>
                    <p>חשבונית זו מהווה אישור תשלום רשמי.</p>
                    <p>לשאלות או בירורים אנא צרו קשר עם שירות הלקוחות.</p>
                    <p>אין צורך בחתימה - מסמך ממוחשב.</p>
                </div>
            </div>
        </div>
        
        <!-- חותמת תחתית -->
        <div class="border-t border-gray-200 p-6 text-center text-sm text-gray-500">
            <p>תודה שבחרת ב-<?php echo SITE_NAME; ?>!</p>
            <p>© <?php echo date('Y'); ?> כל הזכויות שמורות.</p>
        </div>
    </div>
</div>

<style media="print">
    header, footer, .no-print {
        display: none !important;
    }
    
    body {
        background-color: white;
    }
    
    .bg-white {
        box-shadow: none !important;
    }
    
    @page {
        size: A4;
        margin: 1cm;
    }
</style>

<?php include_once '../../includes/footer.php'; ?>
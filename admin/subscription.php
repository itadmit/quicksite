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
$page_title = 'המנוי שלי';

// קבלת פרטי המנוי הפעיל
$active_subscription = get_active_subscription($current_user['id']);

// קבלת היסטוריית מנויים
$subscription_history = get_subscription_history($current_user['id']);

// קבלת פרטי תוכניות זמינות לשדרוג
$available_plans = get_available_plans();

// קבלת סטטיסטיקות שימוש
$usage_stats = get_subscription_usage_stats($current_user['id']);

// בדיקה אם יש הודעה מהפניה
$plan_upgraded = isset($_GET['plan_upgraded']) && $_GET['plan_upgraded'] == 'true';
$plan_renewed = isset($_GET['plan_renewed']) && $_GET['plan_renewed'] == 'true';

// טיפול בפעולות
$error = '';
$success = '';

// טיפול בפעולת שדרוג/חידוש
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_subscription'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
        
        if ($plan_id <= 0) {
            $error = 'נא לבחור תוכנית מנוי תקפה';
        } else {
            // בדיקה שהתוכנית קיימת
            $valid_plan = false;
            foreach ($available_plans as $plan) {
                if ($plan['id'] == $plan_id) {
                    $valid_plan = true;
                    break;
                }
            }
            
            if (!$valid_plan) {
                $error = 'התוכנית שנבחרה אינה תקפה';
            } else {
                // לשם הדוגמה - הפניה לעמוד תשלום/עדכון תוכנית
                redirect(SITE_URL . '/admin/subscription.php?plan_upgraded=true');
            }
        }
    }
}

// טיפול בבקשה ליצירת מנוי ניסיון
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_trial'])) {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        $error = 'שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.';
    } else {
        // ניסיון ליצור מנוי ניסיון
        $trial_success = create_trial_subscription($current_user['id'], 7);
        
        if ($trial_success) {
            $success = 'מנוי ניסיון הופעל בהצלחה! כעת תוכל ליהנות מכל תכונות המערכת למשך 7 ימים.';
            // רענון הדף כדי להציג את המנוי החדש
            redirect(SITE_URL . '/admin/subscription.php?trial_created=true');
        } else {
            $error = 'לא ניתן להפעיל מנוי ניסיון כרגע. אנא נסה שוב מאוחר יותר או צור קשר עם התמיכה.';
        }
    }
}

// בדיקה אם נוצר מנוי ניסיון
$trial_created = isset($_GET['trial_created']) && $_GET['trial_created'] == 'true';

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg leading-6 font-medium text-gray-900">המנוי שלי</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">ניהול תוכנית המנוי וצפייה בפרטי החיוב</p>
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
    
    <?php if ($plan_upgraded): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 mx-6 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-checkbox-circle-line text-green-500"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm text-green-700">המנוי שלך שודרג בהצלחה! תודה על האמון.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($plan_renewed): ?>
        <div class="bg-green-50 border-r-4 border-green-500 p-4 mx-6 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="ri-checkbox-circle-line text-green-500"></i>
                </div>
                <div class="mr-3">
                    <p class="text-sm text-green-700">המנוי שלך חודש בהצלחה! תודה על האמון המתמשך.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- מנוי נוכחי ונתוני שימוש -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- מנוי נוכחי -->
    <div class="bg-white rounded-lg shadow col-span-1">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">המנוי הנוכחי שלי</h3>
        </div>
        <div class="p-4">
            <?php if ($active_subscription): ?>
                <div class="flex justify-center mb-6">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium <?php echo $active_subscription['payment_method'] === 'trial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                        <i class="<?php echo $active_subscription['payment_method'] === 'trial' ? 'ri-gift-line' : 'ri-vip-crown-line'; ?> ml-2 text-xl"></i>
                        <?php echo htmlspecialchars($active_subscription['plan_name']); ?>
                        <?php if ($active_subscription['payment_method'] === 'trial'): ?>
                            <span class="ml-2 text-xs bg-yellow-200 px-2 py-1 rounded-full">ניסיון</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($active_subscription['payment_method'] === 'trial'): ?>
                    <div class="bg-yellow-50 border border-yellow-100 rounded-md p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line text-yellow-500 text-xl"></i>
                            </div>
                            <div class="mr-3">
                                <h4 class="text-sm font-medium text-yellow-800">מנוי ניסיון פעיל</h4>
                                <p class="text-sm text-yellow-700 mt-1">
                                    נותרו לך <span class="font-bold"><?php echo $days_left; ?> ימים</span> במנוי הניסיון שלך.
                                    <?php if ($days_left <= 3): ?>
                                    <br><span class="font-bold">שים לב!</span> המנוי שלך יפוג בקרוב, שקול לשדרג כדי להמשיך ליהנות מכל התכונות.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">מחיר חודשי:</span>
                        <span class="text-gray-900 font-medium"><?php echo number_format($active_subscription['price'], 2); ?> ₪</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">תאריך התחלה:</span>
                        <span class="text-gray-900"><?php echo format_date($active_subscription['start_date']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">תאריך סיום:</span>
                        <span class="text-gray-900"><?php echo format_date($active_subscription['end_date']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">ימים שנותרו:</span>
                        <span class="<?php echo (strtotime($active_subscription['end_date']) - time() < 60*60*24*7) ? 'text-red-600 font-bold' : 'text-gray-900'; ?>">
                            <?php 
                            $days_left = ceil((strtotime($active_subscription['end_date']) - time()) / 86400);
                            echo $days_left . ' ימים';
                            ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">סטטוס:</span>
                        <span class="<?php echo $active_subscription['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                            <?php echo $active_subscription['status'] === 'active' ? 'פעיל' : 'לא פעיל'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">חידוש אוטומטי:</span>
                        <span class="text-gray-900">
                            <?php echo isset($active_subscription['auto_renew']) && $active_subscription['auto_renew'] ? 'מופעל' : 'כבוי'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 mt-6 pt-6">
                    <h4 class="text-base font-medium text-gray-900 mb-3">מגבלות מנוי</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-500">דפי נחיתה</span>
                                <span class="text-sm text-gray-900">
                                    <?php echo $usage_stats['landing_pages']; ?> / 
                                    <?php echo ($active_subscription['landing_pages_limit'] > 0) ? $active_subscription['landing_pages_limit'] : 'ללא הגבלה'; ?>
                                </span>
                            </div>
                            <?php if ($active_subscription['landing_pages_limit'] > 0): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: <?php echo min(100, ($usage_stats['landing_pages'] / $active_subscription['landing_pages_limit']) * 100); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-500">אנשי קשר</span>
                                <span class="text-sm text-gray-900">
                                    <?php echo $usage_stats['contacts']; ?> / 
                                    <?php echo ($active_subscription['contacts_limit'] > 0) ? $active_subscription['contacts_limit'] : 'ללא הגבלה'; ?>
                                </span>
                            </div>
                            <?php if ($active_subscription['contacts_limit'] > 0): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo min(100, ($usage_stats['contacts'] / $active_subscription['contacts_limit']) * 100); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-500">הודעות בחודש</span>
                                <span class="text-sm text-gray-900">
                                    <?php echo $usage_stats['messages']; ?> / 
                                    <?php echo ($active_subscription['messages_limit'] > 0) ? $active_subscription['messages_limit'] : 'ללא הגבלה'; ?>
                                </span>
                            </div>
                            <?php if ($active_subscription['messages_limit'] > 0): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-600 h-2 rounded-full" style="width: <?php echo min(100, ($usage_stats['messages'] / $active_subscription['messages_limit']) * 100); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-gray-500">צפיות בחודש</span>
                                <span class="text-sm text-gray-900">
                                    <?php echo $usage_stats['views']; ?> / 
                                    <?php echo ($active_subscription['views_limit'] > 0) ? $active_subscription['views_limit'] : 'ללא הגבלה'; ?>
                                </span>
                            </div>
                            <?php if ($active_subscription['views_limit'] > 0): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min(100, ($usage_stats['views'] / $active_subscription['views_limit']) * 100); ?>%"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex flex-col space-y-3">
                    <a href="<?php echo SITE_URL; ?>/admin/payment/renew.php" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-refresh-line ml-2"></i>
                        חדש מנוי
                    </a>
                    <button type="button" id="auto-renew-toggle" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="ri-restart-line ml-2"></i>
                        <?php echo isset($active_subscription['auto_renew']) && $active_subscription['auto_renew'] ? 'בטל חידוש אוטומטי' : 'הפעל חידוש אוטומטי'; ?>
                    </button>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center p-4 bg-yellow-100 text-yellow-800 rounded-full mb-4">
                        <i class="ri-alert-line text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">אין לך מנוי פעיל</h4>
                    <p class="text-sm text-gray-500 mb-6">רכוש מנוי כדי ליהנות מכל תכונות המערכת</p>
                    
                    <div class="flex flex-col space-y-3">
                        <a href="#subscription-plans" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="ri-shopping-cart-line ml-2"></i>
                            רכוש מנוי עכשיו
                        </a>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                            <input type="hidden" name="create_trial" value="1">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-indigo-300 text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="ri-gift-line ml-2"></i>
                                הפעל מנוי ניסיון ל-7 ימים
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- סטטיסטיקות שימוש -->
    <div class="bg-white rounded-lg shadow col-span-2">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">נתוני שימוש במערכת</h3>
        </div>
        <div class="p-4 h-96">
            <canvas id="usage-chart"></canvas>
        </div>
    </div>
</div>

<!-- רשימת תוכניות לשדרוג -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6" id="subscription-plans">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">תוכניות מנוי זמינות</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">שדרג את המנוי שלך ליהנות מיותר תכונות ומשאבים</p>
    </div>
    <div class="p-4 bg-white shadow rounded-lg">
        <h3 class="text-xl font-medium text-gray-900 mb-4">תוכניות זמינות</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($available_plans as $plan): ?>
            <div class="border rounded-lg overflow-hidden <?php echo $active_subscription && $active_subscription['plan_id'] == $plan['id'] ? 'border-green-500 bg-green-50' : 'border-gray-200'; ?>">
                <div class="p-4 <?php echo $active_subscription && $active_subscription['plan_id'] == $plan['id'] ? 'bg-green-100' : 'bg-gray-50'; ?>">
                    <div class="flex justify-between items-center">
                        <h4 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($plan['name']); ?></h4>
                        <?php if ($active_subscription && $active_subscription['plan_id'] == $plan['id']): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="ri-check-line ml-1"></i> פעיל
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <span class="text-2xl font-bold text-gray-900"><?php echo number_format($plan['price'], 0); ?></span>
                        <span class="text-gray-500">₪ / חודש</span>
                    </div>
                    
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            <span><?php echo $plan['landing_pages_limit'] ?? $plan['landing_page_limit'] ?? 'ללא הגבלה'; ?> דפי נחיתה</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            <span><?php echo $plan['contacts_limit'] ?? $plan['contact_limit'] ?? 'ללא הגבלה'; ?> אנשי קשר</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            <span><?php echo $plan['messages_limit'] ?? $plan['message_limit'] ?? 'ללא הגבלה'; ?> הודעות</span>
                        </li>
                        <li class="flex items-center">
                            <i class="ri-check-line text-green-500 mr-2"></i>
                            <span><?php echo $plan['views_limit'] ?? $plan['view_limit'] ?? 'ללא הגבלה'; ?> צפיות</span>
                        </li>
                    </ul>
                    
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id'] ?? 0; ?>">
                        <button type="submit" name="update_subscription" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-300 <?php echo isset($active_subscription['plan_id']) && $active_subscription['plan_id'] == $plan['id'] ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo isset($active_subscription['plan_id']) && $active_subscription['plan_id'] == $plan['id'] ? 'disabled' : ''; ?>>
                            <?php if (isset($active_subscription['plan_id']) && $active_subscription['plan_id'] == $plan['id']): ?>
                            התוכנית הנוכחית שלך
                            <?php else: ?>
                            <?php echo isset($active_subscription) && !empty($active_subscription) ? 'שדרג עכשיו' : 'רכוש עכשיו'; ?>
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- היסטוריית מנוי ותשלומים -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">היסטוריית מנוי ותשלומים</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">הצגת כל המנויים הקודמים והנוכחיים והתשלומים ששולמו</p>
    </div>
    <div class="border-t border-gray-200">
        <?php if (!empty($subscription_history)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">תוכנית</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">מחיר</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">תאריך התחלה</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">תאריך סיום</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">סטטוס</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">פעולות</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($subscription_history as $subscription): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($subscription['plan_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo number_format($subscription['price'], 2); ?> ₪</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo format_date($subscription['start_date']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo format_date($subscription['end_date']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                          <?php echo subscription_status_color($subscription['status']); ?>">
                                        <?php echo subscription_status_text($subscription['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if (isset($subscription['invoice_id']) && !empty($subscription['invoice_id'])): ?>
                                        <a href="<?php echo SITE_URL; ?>/admin/payment/invoice.php?id=<?php echo $subscription['invoice_id']; ?>" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center" target="_blank">
                                            <i class="ri-file-list-3-line ml-1"></i>
                                            חשבונית
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="py-8 text-center">
                <p class="text-gray-500">אין עדיין היסטוריית מנויים</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- בחלק זה ניתן להוסיף סקריפטים ייחודיים לעמוד -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
// נתוני שימוש לגרף
const usageData = {
    labels: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני'],
    datasets: [
        {
            label: 'צפיות',
            data: <?php echo json_encode($usage_stats['monthly_views'] ?? [0, 0, 0, 0, 0, 0]); ?>,
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            tension: 0.3
        },
        {
            label: 'הודעות שנשלחו',
            data: <?php echo json_encode($usage_stats['monthly_messages'] ?? [0, 0, 0, 0, 0, 0]); ?>,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.3
        },
        {
            label: 'אנשי קשר חדשים',
            data: <?php echo json_encode($usage_stats['monthly_contacts'] ?? [0, 0, 0, 0, 0, 0]); ?>,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.3
        }
    ]
};

// הקמת גרף השימוש
document.addEventListener('DOMContentLoaded', function() {
    const usageCtx = document.getElementById('usage-chart').getContext('2d');
    const usageChart = new Chart(usageCtx, {
        type: 'line',
        data: usageData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
});

// טיפול בלחצן חידוש אוטומטי
document.getElementById('auto-renew-toggle')?.addEventListener('click', function() {
    const currentState = <?php echo isset($active_subscription['auto_renew']) && $active_subscription['auto_renew'] ? 'true' : 'false'; ?>;
    
    // שליחת בקשת AJAX לשינוי מצב חידוש אוטומטי
    $.ajax({
        url: "<?php echo SITE_URL; ?>/admin/ajax/toggle-auto-renew.php",
        type: "POST",
        data: { 
            subscription_id: <?php echo $active_subscription ? $active_subscription['id'] : 0; ?>,
            auto_renew: !currentState,
            csrf_token: "<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>"
        },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                // עדכון טקסט הכפתור
                const button = document.getElementById('auto-renew-toggle');
                if (response.auto_renew) {
                    button.innerHTML = '<i class="ri-restart-line ml-2"></i> בטל חידוש אוטומטי';
                } else {
                    button.innerHTML = '<i class="ri-restart-line ml-2"></i> הפעל חידוש אוטומטי';
                }
                
                // הצגת הודעת הצלחה
                const successAlert = document.createElement('div');
                successAlert.className = 'bg-green-50 border-r-4 border-green-500 p-4 mx-6 mb-4 mt-4';
                successAlert.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-checkbox-circle-line text-green-500"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-green-700">חידוש אוטומטי ${response.auto_renew ? 'הופעל' : 'בוטל'} בהצלחה</p>
                        </div>
                    </div>
                `;
                
                const container = button.closest('.mt-6').parentNode;
                container.insertBefore(successAlert, container.querySelector('.mt-6'));
                
                // הסרת ההודעה לאחר 3 שניות
                setTimeout(function() {
                    successAlert.style.transition = 'opacity 0.5s ease-out';
                    successAlert.style.opacity = 0;
                    setTimeout(function() {
                        successAlert.remove();
                    }, 500);
                }, 3000);
            } else {
                // הצגת הודעת שגיאה
                alert('אירעה שגיאה: ' + response.message);
            }
        },
        error: function() {
            alert('אירעה שגיאה בתקשורת עם השרת');
        }
    });
});
</script>

<?php
/**
 * פונקציה להצגת צבע בהתאם לסטטוס המנוי
 * 
 * @param string $status סטטוס המנוי
 * @return string מחלקות CSS
 */
function subscription_status_color($status) {
    switch ($status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'expired':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * פונקציה להצגת טקסט בהתאם לסטטוס המנוי
 * 
 * @param string $status סטטוס המנוי
 * @return string תיאור הסטטוס
 */
function subscription_status_text($status) {
    switch ($status) {
        case 'active':
            return 'פעיל';
        case 'cancelled':
            return 'בוטל';
        case 'expired':
            return 'פג תוקף';
        default:
            return $status;
    }
}

/**
 * פונקציה לקבלת פרטי תוכניות זמינות
 * 
 * @return array רשימת תוכניות זמינות
 */
function get_available_plans() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE status = 'active' ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת תוכניות זמינות: " . $e->getMessage());
        return [];
    }
}

/**
 * פונקציה לקבלת היסטוריית מנויים
 * 
 * @param int $user_id מזהה המשתמש
 * @return array רשימת מנויים עם פרטים מלאים
 */
function get_subscription_history($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, p.name as plan_name, p.price
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            WHERE s.user_id = ?
            ORDER BY s.start_date DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת היסטוריית מנויים: " . $e->getMessage());
        return [];
    }
}

/**
 * פונקציה לקבלת נתוני שימוש בהקשר המנוי
 * 
 * @param int $user_id מזהה המשתמש
 * @return array נתוני שימוש
 */
function get_subscription_usage_stats($user_id) {
    global $pdo;
    
    // הגדרת ערכי ברירת מחדל למקרה של שגיאה
    $stats = [
        'landing_pages' => 0,
        'contacts' => 0,
        'views' => 0,
        'messages' => 0,
        'monthly_views' => [0, 0, 0, 0, 0, 0],
        'monthly_messages' => [0, 0, 0, 0, 0, 0],
        'monthly_contacts' => [0, 0, 0, 0, 0, 0]
    ];
    
    // בדיקה שהתקבל מזהה משתמש תקין
    if (empty($user_id) || !is_numeric($user_id) || $user_id <= 0) {
        error_log("שגיאה בקבלת נתוני שימוש מנוי: מזהה משתמש חסר או לא תקין - " . var_export($user_id, true));
        return $stats;
    }
    
    // בדיקה שיש חיבור פעיל למסד הנתונים
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        error_log("שגיאה חמורה בקבלת נתוני שימוש מנוי: חיבור למסד נתונים לא זמין");
        return $stats;
    }
    
    try {
        // בדיקה שהטבלאות קיימות לפני שימוש בהן
        $tables_to_check = ['landing_pages', 'contacts', 'page_visits', 'sent_messages', 'campaigns'];
        $missing_tables = [];
        $required_tables_exist = true;
        
        foreach ($tables_to_check as $table) {
            try {
                // תיקון: ב-SHOW TABLES אין אפשרות להשתמש בפרמטרים, לכן נוסיף את הערך ישירות למחרוזת
                $query = "SHOW TABLES LIKE '{$table}'";
                $table_check = $pdo->query($query);
                if (!$table_check || $table_check->rowCount() === 0) {
                    $missing_tables[] = $table;
                    $required_tables_exist = false;
                    error_log("שגיאה בקבלת נתוני שימוש מנוי: הטבלה {$table} חסרה");
                }
            } catch (PDOException $e) {
                $missing_tables[] = $table;
                $required_tables_exist = false;
                error_log("שגיאה בבדיקת קיום טבלה {$table}: " . $e->getMessage());
            }
        }
        
        if (!empty($missing_tables)) {
            error_log("שגיאה בקבלת נתוני שימוש מנוי: הטבלאות הבאות חסרות: " . implode(', ', $missing_tables));
            // ממשיך לפונקציה עם הטבלאות הקיימות בלבד
        }
        
        // איפוס ראשוני של המערכים החודשיים
        $stats['monthly_views'] = [0, 0, 0, 0, 0, 0];
        $stats['monthly_messages'] = [0, 0, 0, 0, 0, 0]; 
        $stats['monthly_contacts'] = [0, 0, 0, 0, 0, 0];
        
        // ספירת דפי נחיתה - רק אם הטבלה קיימת
        if (!in_array('landing_pages', $missing_tables)) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages WHERE user_id = ? AND status != 'archived'");
                $stmt->execute([$user_id]);
                $result = $stmt->fetchColumn();
                $stats['landing_pages'] = ($result !== false) ? (int)$result : 0;
            } catch (PDOException $e) {
                error_log("שגיאה בספירת דפי נחיתה: " . $e->getMessage());
                // משאיר את ערך ברירת המחדל
            }
        }
        
        // ספירת אנשי קשר - רק אם הטבלה קיימת
        if (!in_array('contacts', $missing_tables)) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ? AND status = 'active'");
                $stmt->execute([$user_id]);
                $result = $stmt->fetchColumn();
                $stats['contacts'] = ($result !== false) ? (int)$result : 0;
            } catch (PDOException $e) {
                error_log("שגיאה בספירת אנשי קשר: " . $e->getMessage());
                // משאיר את ערך ברירת המחדל
            }
        }
        
        // ספירת צפיות בחודש הנוכחי - רק אם הטבלאות קיימות
        if (!in_array('page_visits', $missing_tables) && !in_array('landing_pages', $missing_tables)) {
            try {
                $current_month = date('m');
                $current_year = date('Y');
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM page_visits pv 
                    JOIN landing_pages lp ON pv.landing_page_id = lp.id 
                    WHERE lp.user_id = ? AND MONTH(pv.created_at) = ? AND YEAR(pv.created_at) = ?
                ");
                $stmt->execute([$user_id, $current_month, $current_year]);
                $result = $stmt->fetchColumn();
                $stats['views'] = ($result !== false) ? (int)$result : 0;
            } catch (PDOException $e) {
                error_log("שגיאה בספירת צפיות בחודש הנוכחי: " . $e->getMessage());
                // משאיר את ערך ברירת המחדל
            }
        }
        
        // ספירת הודעות שנשלחו בחודש הנוכחי - רק אם הטבלאות קיימות
        if (!in_array('sent_messages', $missing_tables) && !in_array('campaigns', $missing_tables)) {
            try {
                $current_month = date('m');
                $current_year = date('Y');
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM sent_messages sm 
                    JOIN campaigns c ON sm.campaign_id = c.id 
                    WHERE c.user_id = ? AND MONTH(sm.sent_at) = ? AND YEAR(sm.sent_at) = ?
                ");
                $stmt->execute([$user_id, $current_month, $current_year]);
                $result = $stmt->fetchColumn();
                $stats['messages'] = ($result !== false) ? (int)$result : 0;
            } catch (PDOException $e) {
                error_log("שגיאה בספירת הודעות בחודש הנוכחי: " . $e->getMessage());
                // משאיר את ערך ברירת המחדל
            }
        }
        
        // נתונים חודשיים - צפיות - רק אם הטבלאות קיימות
        if (!in_array('page_visits', $missing_tables) && !in_array('landing_pages', $missing_tables)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT MONTH(pv.created_at) as month, COUNT(*) as count
                    FROM page_visits pv 
                    JOIN landing_pages lp ON pv.landing_page_id = lp.id 
                    WHERE lp.user_id = ? AND pv.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY MONTH(pv.created_at)
                    ORDER BY MONTH(pv.created_at)
                ");
                $stmt->execute([$user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $monthly_views = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    
                    // מילוי מערך של 6 חודשים אחרונים
                    $stats['monthly_views'] = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $month = date('n', strtotime("-$i months"));
                        $stats['monthly_views'][] = isset($monthly_views[$month]) ? (int)$monthly_views[$month] : 0;
                    }
                }
            } catch (PDOException $e) {
                error_log("שגיאה בקבלת נתוני צפיות חודשיים: " . $e->getMessage());
                // שומר על ערכי ברירת מחדל במקרה של שגיאה
            }
        }
        
        // נתונים חודשיים - הודעות - רק אם הטבלאות קיימות
        if (!in_array('sent_messages', $missing_tables) && !in_array('campaigns', $missing_tables)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT MONTH(sm.sent_at) as month, COUNT(*) as count
                    FROM sent_messages sm 
                    JOIN campaigns c ON sm.campaign_id = c.id 
                    WHERE c.user_id = ? AND sm.sent_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY MONTH(sm.sent_at)
                    ORDER BY MONTH(sm.sent_at)
                ");
                $stmt->execute([$user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $monthly_messages = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    
                    // מילוי מערך של 6 חודשים אחרונים
                    $stats['monthly_messages'] = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $month = date('n', strtotime("-$i months"));
                        $stats['monthly_messages'][] = isset($monthly_messages[$month]) ? (int)$monthly_messages[$month] : 0;
                    }
                }
            } catch (PDOException $e) {
                error_log("שגיאה בקבלת נתוני הודעות חודשיים: " . $e->getMessage());
                // שומר על ערכי ברירת מחדל במקרה של שגיאה
            }
        }
        
        // נתונים חודשיים - אנשי קשר חדשים - רק אם הטבלה קיימת
        if (!in_array('contacts', $missing_tables)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT MONTH(created_at) as month, COUNT(*) as count
                    FROM contacts
                    WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY MONTH(created_at)
                    ORDER BY MONTH(created_at)
                ");
                $stmt->execute([$user_id]);
                
                if ($stmt->rowCount() > 0) {
                    $monthly_contacts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    
                    // מילוי מערך של 6 חודשים אחרונים
                    $stats['monthly_contacts'] = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $month = date('n', strtotime("-$i months"));
                        $stats['monthly_contacts'][] = isset($monthly_contacts[$month]) ? (int)$monthly_contacts[$month] : 0;
                    }
                }
            } catch (PDOException $e) {
                error_log("שגיאה בקבלת נתוני אנשי קשר חודשיים: " . $e->getMessage());
                // שומר על ערכי ברירת מחדל במקרה של שגיאה
            }
        }
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("שגיאה כללית בקבלת נתוני שימוש מנוי: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return $stats;
    } catch (Exception $e) {
        error_log("שגיאה לא צפויה בקבלת נתוני שימוש מנוי: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
        return $stats;
    }
}
?>

<?php include_once '../includes/footer.php'; ?>
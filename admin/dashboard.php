<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// כותרת הדף
$page_title = 'הדשבורד שלי';

// קבלת נתוני המנוי הפעיל
$subscription = get_active_subscription($current_user['id']);

// קבלת סטטיסטיקות כלליות
$stats = get_user_dashboard_stats($current_user['id']);

// טעינת תבנית העיצוב - הדר
include_once '../includes/header.php';
?>

<!-- כרטיסיות סטטיסטיקה -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- דפי נחיתה -->
    <div class="dashboard-stat dashboard-stat-primary rounded-lg">
        <div class="flex items-center mb-2">
            <i class="ri-layout-3-line text-3xl ml-3"></i>
            <h3 class="text-xl font-bold">דפי נחיתה</h3>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <span class="text-3xl font-bold"><?php echo $stats['landing_pages']; ?></span>
                <span class="text-sm opacity-80"> / <?php echo ($subscription['landing_pages_limit'] > 0) ? $subscription['landing_pages_limit'] : 'ללא הגבלה'; ?></span>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="px-2 py-1 bg-white bg-opacity-20 rounded-md text-sm hover:bg-opacity-30">
                <i class="ri-arrow-left-line"></i> הצג
            </a>
        </div>
    </div>
    
    <!-- אנשי קשר -->
    <div class="dashboard-stat dashboard-stat-secondary rounded-lg">
        <div class="flex items-center mb-2">
            <i class="ri-user-follow-line text-3xl ml-3"></i>
            <h3 class="text-xl font-bold">אנשי קשר</h3>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <span class="text-3xl font-bold"><?php echo $stats['contacts']; ?></span>
                <span class="text-sm opacity-80"> / <?php echo ($subscription['contacts_limit'] > 0) ? $subscription['contacts_limit'] : 'ללא הגבלה'; ?></span>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="px-2 py-1 bg-white bg-opacity-20 rounded-md text-sm hover:bg-opacity-30">
                <i class="ri-arrow-left-line"></i> הצג
            </a>
        </div>
    </div>
    
    <!-- צפיות -->
    <div class="dashboard-stat dashboard-stat-warning rounded-lg">
        <div class="flex items-center mb-2">
            <i class="ri-eye-line text-3xl ml-3"></i>
            <h3 class="text-xl font-bold">צפיות החודש</h3>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <span class="text-3xl font-bold"><?php echo $stats['views']; ?></span>
                <span class="text-sm opacity-80"> / <?php echo ($subscription['views_limit'] > 0) ? $subscription['views_limit'] : 'ללא הגבלה'; ?></span>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/landing-pages/analytics.php" class="px-2 py-1 bg-white bg-opacity-20 rounded-md text-sm hover:bg-opacity-30">
                <i class="ri-arrow-left-line"></i> הצג
            </a>
        </div>
    </div>
    
    <!-- הודעות -->
    <div class="dashboard-stat dashboard-stat-danger rounded-lg">
        <div class="flex items-center mb-2">
            <i class="ri-message-3-line text-3xl ml-3"></i>
            <h3 class="text-xl font-bold">הודעות החודש</h3>
        </div>
        <div class="flex items-end justify-between">
            <div>
                <span class="text-3xl font-bold"><?php echo $stats['messages']; ?></span>
                <span class="text-sm opacity-80"> / <?php echo ($subscription['messages_limit'] > 0) ? $subscription['messages_limit'] : 'ללא הגבלה'; ?></span>
            </div>
            <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="px-2 py-1 bg-white bg-opacity-20 rounded-md text-sm hover:bg-opacity-30">
                <i class="ri-arrow-left-line"></i> הצג
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- דפי נחיתה אחרונים -->
    <div class="bg-white rounded-lg shadow col-span-2">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">דפי נחיתה אחרונים</h3>
            <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="text-sm text-indigo-600 hover:text-indigo-500">הצג הכל</a>
        </div>
        <div class="p-4">
            <?php if (!empty($stats['recent_landing_pages'])): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($stats['recent_landing_pages'] as $landing_page): ?>
                        <li class="py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="<?php echo get_status_color_class($landing_page['status']); ?> w-3 h-3 rounded-full ml-3"></div>
                                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/edit.php?id=<?php echo $landing_page['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        <?php echo htmlspecialchars($landing_page['title']); ?>
                                    </a>
                                </div>
                                <div class="flex items-center space-x-4 rtl:space-x-reverse text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="ri-eye-line ml-1"></i>
                                        <?php echo $landing_page['visits_count']; ?>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="ri-user-follow-line ml-1"></i>
                                        <?php echo $landing_page['conversions_count']; ?>
                                    </div>
                                    <div>
                                        <a href="<?php echo $landing_page['url']; ?>" target="_blank" class="text-gray-400 hover:text-gray-600 p-1">
                                            <i class="ri-external-link-line"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if ($stats['landing_page_count'] == 0): ?>
                    <div class="py-5 text-center">
                        <p class="text-gray-500">טרם יצרת דפי נחיתה</p>
                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="ri-add-line ml-2"></i>
                            צור דף נחיתה ראשון
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="py-5 text-center">
                    <p class="text-gray-500">טרם יצרת דפי נחיתה</p>
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ri-add-line ml-2"></i>
                        צור דף נחיתה ראשון
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- פרטי מנוי ומהירה -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">פרטי מנוי</h3>
        </div>
        <div class="p-4">
            <?php if ($subscription): ?>
                <div class="mb-6">
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="ri-vip-crown-line ml-1"></i>
                            <?php echo htmlspecialchars($subscription['plan_name']); ?>
                        </span>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">תאריך התחלה:</span>
                            <span class="text-gray-900"><?php echo format_date($subscription['start_date']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">תאריך סיום:</span>
                            <span class="text-gray-900"><?php echo format_date($subscription['end_date']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">מחיר:</span>
                            <span class="text-gray-900"><?php echo number_format($subscription['price'], 2); ?> ₪ לחודש</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">סטטוס:</span>
                            <span class="<?php echo $subscription['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $subscription['status'] === 'active' ? 'פעיל' : 'לא פעיל'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-900">
                            <i class="ri-information-line ml-1"></i>
                            פרטים נוספים
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="pt-4 border-t border-gray-200">
                <h4 class="text-base font-medium text-gray-900 mb-3">גישה מהירה</h4>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="inline-flex justify-center items-center p-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="ri-layout-3-line text-xl ml-2 text-indigo-500"></i>
                        <span>צור דף נחיתה</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/contacts/import.php" class="inline-flex justify-center items-center p-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="ri-contacts-book-upload-line text-xl ml-2 text-green-500"></i>
                        <span>ייבא אנשי קשר</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/messaging/create-campaign.php" class="inline-flex justify-center items-center p-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="ri-mail-send-line text-xl ml-2 text-orange-500"></i>
                        <span>צור קמפיין</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="inline-flex justify-center items-center p-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="ri-settings-3-line text-xl ml-2 text-gray-500"></i>
                        <span>הגדרות</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- הודעות אחרונות ופעילות -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- קמפיינים אחרונים -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">קמפיינים אחרונים</h3>
            <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="text-sm text-indigo-600 hover:text-indigo-500">הצג הכל</a>
        </div>
        <div class="p-4">
            <?php if (!empty($stats['recent_campaigns'])): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($stats['recent_campaigns'] as $campaign): ?>
                        <li class="py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <?php 
                                    $campaign_icon = 'ri-mail-line';
                                    if ($campaign['type'] === 'sms') $campaign_icon = 'ri-message-2-line';
                                    if ($campaign['type'] === 'whatsapp') $campaign_icon = 'ri-whatsapp-line';
                                    ?>
                                    <i class="<?php echo $campaign_icon; ?> text-gray-500 ml-2"></i>
                                    <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php?id=<?php echo $campaign['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        <?php echo htmlspecialchars($campaign['name']); ?>
                                    </a>
                                </div>
                                <div class="flex items-center space-x-2 rtl:space-x-reverse text-sm text-gray-500">
                                    <span class="<?php echo get_campaign_status_color_class($campaign['status']); ?> px-2 py-1 rounded-full text-xs font-medium">
                                        <?php echo get_campaign_status_text($campaign['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if (empty($stats['recent_campaigns'])): ?>
                    <div class="py-5 text-center">
                        <p class="text-gray-500">טרם יצרת קמפיינים</p>
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/create-campaign.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="ri-add-line ml-2"></i>
                            צור קמפיין ראשון
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="py-5 text-center">
                    <p class="text-gray-500">טרם יצרת קמפיינים</p>
                    <a href="<?php echo SITE_URL; ?>/admin/messaging/create-campaign.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ri-add-line ml-2"></i>
                        צור קמפיין ראשון
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- אנשי קשר אחרונים -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">אנשי קשר אחרונים</h3>
            <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="text-sm text-indigo-600 hover:text-indigo-500">הצג הכל</a>
        </div>
        <div class="p-4">
            <?php if (!empty($stats['recent_contacts'])): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($stats['recent_contacts'] as $contact): ?>
                        <li class="py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php?action=view&id=<?php echo $contact['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        <?php 
                                        $contact_name = '';
                                        if (!empty($contact['first_name']) || !empty($contact['last_name'])) {
                                            $contact_name = trim($contact['first_name'] . ' ' . $contact['last_name']);
                                        } else {
                                            $contact_name = $contact['email'];
                                        }
                                        echo htmlspecialchars($contact_name); 
                                        ?>
                                    </a>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($contact['email']); ?></p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo format_date($contact['created_at'], 'd/m/Y'); ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if (empty($stats['recent_contacts'])): ?>
                    <div class="py-5 text-center">
                        <p class="text-gray-500">טרם הוספת אנשי קשר</p>
                        <a href="<?php echo SITE_URL; ?>/admin/contacts/import.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="ri-add-line ml-2"></i>
                            הוסף אנשי קשר
                        </a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="py-5 text-center">
                    <p class="text-gray-500">טרם הוספת אנשי קשר</p>
                    <a href="<?php echo SITE_URL; ?>/admin/contacts/import.php" class="mt-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="ri-add-line ml-2"></i>
                        הוסף אנשי קשר
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * פונקציית עזר לקבלת צבע על פי סטטוס דף נחיתה
 */
function get_status_color_class($status) {
    switch ($status) {
        case 'published':
            return 'bg-green-500';
        case 'draft':
            return 'bg-yellow-500';
        case 'archived':
            return 'bg-gray-500';
        default:
            return 'bg-gray-300';
    }
}

/**
 * פונקציית עזר לקבלת צבע על פי סטטוס קמפיין
 */
function get_campaign_status_color_class($status) {
    switch ($status) {
        case 'sent':
            return 'bg-green-100 text-green-800';
        case 'sending':
            return 'bg-blue-100 text-blue-800';
        case 'scheduled':
            return 'bg-purple-100 text-purple-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'paused':
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * פונקציית עזר לקבלת טקסט לסטטוס קמפיין
 */
function get_campaign_status_text($status) {
    switch ($status) {
        case 'sent':
            return 'נשלח';
        case 'sending':
            return 'בשליחה';
        case 'scheduled':
            return 'מתוזמן';
        case 'draft':
            return 'טיוטה';
        case 'paused':
            return 'מושהה';
        case 'cancelled':
            return 'בוטל';
        default:
            return $status;
    }
}

/**
 * קבלת סטטיסטיקות דשבורד למשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @return array מערך עם הסטטיסטיקות
 */
function get_user_dashboard_stats($user_id) {
    global $pdo;
    
    $stats = [
        'landing_pages' => 0,
        'landing_page_count' => 0,
        'contacts' => 0,
        'views' => 0,
        'messages' => 0,
        'recent_landing_pages' => [],
        'recent_campaigns' => [],
        'recent_contacts' => []
    ];
    
    try {
        // ספירת דפי נחיתה
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages WHERE user_id = ? AND status != 'archived'");
        $stmt->execute([$user_id]);
        $stats['landing_pages'] = $stmt->fetchColumn();
        $stats['landing_page_count'] = $stats['landing_pages'];
        
        // ספירת אנשי קשר
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $stats['contacts'] = $stmt->fetchColumn();
        
        // ספירת צפיות בחודש הנוכחי
        $current_month = date('m');
        $current_year = date('Y');
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM page_visits pv 
            JOIN landing_pages lp ON pv.landing_page_id = lp.id 
            WHERE lp.user_id = ? AND MONTH(pv.created_at) = ? AND YEAR(pv.created_at) = ?
        ");
        $stmt->execute([$user_id, $current_month, $current_year]);
        $stats['views'] = $stmt->fetchColumn();
        
        // ספירת הודעות שנשלחו בחודש הנוכחי
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM sent_messages sm 
            JOIN campaigns c ON sm.campaign_id = c.id 
            WHERE c.user_id = ? AND MONTH(sm.sent_at) = ? AND YEAR(sm.sent_at) = ?
        ");
        $stmt->execute([$user_id, $current_month, $current_year]);
        $stats['messages'] = $stmt->fetchColumn();
        
        // קבלת דפי נחיתה אחרונים
        $stmt = $pdo->prepare("
            SELECT lp.*, 
                   (SELECT COUNT(*) FROM page_visits WHERE landing_page_id = lp.id) AS visits_count,
                   (SELECT COUNT(*) FROM form_submissions WHERE landing_page_id = lp.id) AS conversions_count
            FROM landing_pages lp
            WHERE lp.user_id = ? AND lp.status != 'archived'
            ORDER BY lp.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $landing_pages = $stmt->fetchAll();
        
        foreach ($landing_pages as &$landing_page) {
            $landing_page['url'] = get_landing_page_url($landing_page['slug'], $landing_page['custom_domain_id']);
        }
        
        $stats['recent_landing_pages'] = $landing_pages;
        
        // קבלת קמפיינים אחרונים
        $stmt = $pdo->prepare("
            SELECT c.* 
            FROM campaigns c
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $stats['recent_campaigns'] = $stmt->fetchAll();
        
        // קבלת אנשי קשר אחרונים
        $stmt = $pdo->prepare("
            SELECT c.* 
            FROM contacts c
            WHERE c.user_id = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $stats['recent_contacts'] = $stmt->fetchAll();
        
        return $stats;
        
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת סטטיסטיקות דשבורד: " . $e->getMessage());
        return $stats;
    }
}
?>

<?php include_once '../includes/footer.php'; ?>
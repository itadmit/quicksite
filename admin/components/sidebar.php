<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

// קבלת הדף הנוכחי
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_folder = dirname($_SERVER['PHP_SELF']);
$current_folder = basename($current_folder);

// בדיקה איזה תפריט מוצג כרגע
$is_landing_pages = strpos($current_folder, 'landing-pages') !== false;
$is_contacts = strpos($current_folder, 'contacts') !== false;
$is_messaging = strpos($current_folder, 'messaging') !== false;
$is_settings = strpos($current_folder, 'settings') !== false;
?>

<div class="h-full overflow-y-auto">
    <div class="p-4">
        <div class="text-center mb-6">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="h-8 mx-auto mb-2">
            <p class="text-xs text-gray-500">גרסה <?php echo SYSTEM_VERSION; ?></p>
        </div>
        
        <nav class="space-y-1 sidebar-nav">
            <!-- דשבורד -->
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo $current_page === 'dashboard' ? 'active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                <i class="ri-dashboard-line ml-3 text-lg <?php echo $current_page === 'dashboard' ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                דשבורד
            </a>
            
            <!-- דפי נחיתה -->
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo $is_landing_pages ? 'active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                    <i class="ri-layout-3-line ml-3 text-lg <?php echo $is_landing_pages ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                    דפי נחיתה
                </a>
                <?php if ($is_landing_pages): ?>
                    <div class="pr-8 my-1 text-xs">
                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'index' && $is_landing_pages ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-list-check ml-2 <?php echo $current_page === 'index' && $is_landing_pages ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            כל הדפים
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/create.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'create' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-add-line ml-2 <?php echo $current_page === 'create' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            יצירת דף חדש
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/analytics.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'analytics' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-line-chart-line ml-2 <?php echo $current_page === 'analytics' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            אנליטיקס
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/landing-pages/ab-testing.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'ab-testing' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-split-cells-horizontal ml-2 <?php echo $current_page === 'ab-testing' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            בדיקות A/B
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- אנשי קשר -->
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo $is_contacts ? 'active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                    <i class="ri-contacts-book-line ml-3 text-lg <?php echo $is_contacts ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                    אנשי קשר
                </a>
                <?php if ($is_contacts): ?>
                    <div class="pr-8 my-1 text-xs">
                        <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'index' && $is_contacts ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-list-check ml-2 <?php echo $current_page === 'index' && $is_contacts ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            כל אנשי הקשר
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/contacts/lists.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'lists' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-file-list-3-line ml-2 <?php echo $current_page === 'lists' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            רשימות תפוצה
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/contacts/import.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'import' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-upload-2-line ml-2 <?php echo $current_page === 'import' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            ייבוא אנשי קשר
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/contacts/export.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'export' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-download-2-line ml-2 <?php echo $current_page === 'export' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            ייצוא אנשי קשר
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- דיוור -->
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo $is_messaging ? 'active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                    <i class="ri-mail-send-line ml-3 text-lg <?php echo $is_messaging ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                    דיוור
                </a>
                <?php if ($is_messaging): ?>
                    <div class="pr-8 my-1 text-xs">
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'campaigns' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-mail-line ml-2 <?php echo $current_page === 'campaigns' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            קמפיינים
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/create-campaign.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'create-campaign' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-add-line ml-2 <?php echo $current_page === 'create-campaign' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            יצירת קמפיין
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/templates.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'templates' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-file-copy-line ml-2 <?php echo $current_page === 'templates' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            תבניות הודעות
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/automations.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'automations' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-loop-right-line ml-2 <?php echo $current_page === 'automations' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            אוטומציות
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/messaging/reports.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'reports' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-bar-chart-grouped-line ml-2 <?php echo $current_page === 'reports' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            דוחות
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- הגדרות -->
            <div>
                <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md <?php echo $is_settings ? 'active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                    <i class="ri-settings-3-line ml-3 text-lg <?php echo $is_settings ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                    הגדרות
                </a>
                <?php if ($is_settings): ?>
                    <div class="pr-8 my-1 text-xs">
                        <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'account' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-user-settings-line ml-2 <?php echo $current_page === 'account' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            חשבון
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings/domains.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'domains' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-global-line ml-2 <?php echo $current_page === 'domains' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            דומיינים
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings/api.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'api' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-code-s-slash-line ml-2 <?php echo $current_page === 'api' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            API
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/settings/integrations.php" class="group flex items-center px-3 py-2 text-gray-600 hover:text-gray-900 <?php echo $current_page === 'integrations' ? 'text-indigo-600 font-medium' : ''; ?>">
                            <i class="ri-plug-line ml-2 <?php echo $current_page === 'integrations' ? 'text-indigo-500' : 'text-gray-400'; ?>"></i>
                            אינטגרציות
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    
    <div class="p-4 border-t border-gray-200 mt-6">
        <div>
            <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="group flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-900">
                <i class="ri-user-3-line ml-2 text-gray-400 group-hover:text-gray-500"></i>
                פרופיל
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="group flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-900">
                <i class="ri-vip-crown-line ml-2 text-gray-400 group-hover:text-gray-500"></i>
                המנוי שלי
            </a>
            <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="group flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-900">
                <i class="ri-logout-box-r-line ml-2 text-gray-400 group-hover:text-gray-500"></i>
                התנתקות
            </a>
        </div>
    </div>
</div>
<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

// הגדר את הדף הנוכחי
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_section = isset($section) ? $section : '';
?>

<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                        <img class="h-9 w-auto" src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8 rtl:space-x-reverse">
                    <!-- תפריט ניווט -->
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        דשבורד
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="<?php echo strpos($current_section, 'landing') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        דפי נחיתה
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="<?php echo strpos($current_section, 'contacts') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        אנשי קשר
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="<?php echo strpos($current_section, 'messaging') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        דיוור
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="<?php echo strpos($current_section, 'settings') !== false ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                        הגדרות
                    </a>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <!-- התראות -->
                <button type="button" class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 ml-3">
                    <span class="sr-only">הצג התראות</span>
                    <i class="ri-notification-3-line text-xl"></i>
                </button>

                <!-- תפריט משתמש -->
                <div class="ml-3 relative dropdown">
                    <div>
                        <button type="button" class="flex items-center max-w-xs text-sm rounded-full focus:outline-none">
                            <span class="ml-2"><?php echo $current_user['first_name'] . ' ' . $current_user['last_name']; ?></span>
                            <img class="h-8 w-8 rounded-full" src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($current_user['email']))); ?>?d=mp&s=80" alt="">
                        </button>
                    </div>
                    <div class="dropdown-content mt-2 bg-white rounded-md shadow-lg py-1 right-0">
                        <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="ri-user-3-line ml-2"></i>
                            הפרופיל שלי
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="ri-vip-crown-line ml-2"></i>
                            המנוי שלי
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="ri-logout-box-r-line ml-2"></i>
                            התנתקות
                        </a>
                    </div>
                </div>
            </div>
            <div class="-mr-2 flex items-center sm:hidden">
                <!-- תפריט נייד -->
                <button type="button" class="mobile-menu-button bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">פתח תפריט</span>
                    <i class="ri-menu-line h-6 w-6" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- תפריט נייד -->
    <div class="mobile-menu sm:hidden hidden">
        <div class="pt-2 pb-3 space-y-1 border-b border-gray-200">
            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800'; ?> block pr-3 pl-4 py-2 border-r-4 text-base font-medium">
                דשבורד
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="<?php echo strpos($current_section, 'landing') !== false ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800'; ?> block pr-3 pl-4 py-2 border-r-4 text-base font-medium">
                דפי נחיתה
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="<?php echo strpos($current_section, 'contacts') !== false ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800'; ?> block pr-3 pl-4 py-2 border-r-4 text-base font-medium">
                אנשי קשר
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="<?php echo strpos($current_section, 'messaging') !== false ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800'; ?> block pr-3 pl-4 py-2 border-r-4 text-base font-medium">
                דיוור
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="<?php echo strpos($current_section, 'settings') !== false ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800'; ?> block pr-3 pl-4 py-2 border-r-4 text-base font-medium">
                הגדרות
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full" src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($current_user['email']))); ?>?d=mp&s=80" alt="">
                </div>
                <div class="mr-3">
                    <div class="text-base font-medium text-gray-800"><?php echo $current_user['first_name'] . ' ' . $current_user['last_name']; ?></div>
                    <div class="text-sm font-medium text-gray-500"><?php echo $current_user['email']; ?></div>
                </div>
                <button type="button" class="mr-auto flex-shrink-0 bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">הצג התראות</span>
                    <i class="ri-notification-3-line h-6 w-6" aria-hidden="true"></i>
                </button>
            </div>
            <div class="mt-3 space-y-1">
                <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                    הפרופיל שלי
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                    המנוי שלי
                </a>
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                    התנתקות
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
// תפריט נייד
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // סגירת תפריטים נפתחים בלחיצה מחוץ לתפריט
    document.addEventListener('click', function(event) {
        const dropdown = document.querySelector('.dropdown');
        const dropdownContent = document.querySelector('.dropdown-content');
        
        if (dropdown && dropdownContent && !dropdown.contains(event.target)) {
            dropdownContent.style.display = 'none';
        }
    });
});
</script>
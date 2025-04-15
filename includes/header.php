<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

// הגדר את הדף הנוכחי
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$is_admin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$is_auth = strpos($_SERVER['PHP_SELF'], '/auth/') !== false;
$is_builder = strpos($_SERVER['PHP_SELF'], '/builder/') !== false;
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? SITE_NAME; ?></title>
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Noto Sans Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Noto Sans Hebrew', sans-serif;
        }
        
        /* RTL הגדרות עבור */
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        /* סגנון כללי */
        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .content-wrapper {
            flex: 1;
        }
        
        /* סגנון תפריט הניווט */
        .main-nav .active {
            color: #4F46E5;
            font-weight: 600;
            border-bottom: 2px solid #4F46E5;
        }
        
        /* סגנון סייד-בר */
        .sidebar-nav .active {
            background-color: rgba(79, 70, 229, 0.1);
            color: #4F46E5;
            border-right: 3px solid #4F46E5;
        }
        
        /* אייקון לחצני */
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-icon i {
            margin-left: 0.5rem;
        }
        
        /* תפריט נפתח */
        .dropdown-content {
            display: none;
            position: absolute;
            min-width: 160px;
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
            z-index: 1;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* לוח מחוונים אדמין */
        .dashboard-stat {
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dashboard-stat-primary {
            background-color: #4F46E5;
            color: white;
        }
        
        .dashboard-stat-secondary {
            background-color: #10B981;
            color: white;
        }
        
        .dashboard-stat-warning {
            background-color: #F59E0B;
            color: white;
        }
        
        .dashboard-stat-danger {
            background-color: #EF4444;
            color: white;
        }
        
        /* התאמה למובייל */
        @media (max-width: 768px) {
            .mobile-hidden {
                display: none;
            }
            
            .mobile-block {
                display: block;
            }
        }
    </style>
    
    <?php if (isset($extra_css)): ?>
    <!-- CSS נוסף ספציפי לדף -->
    <style>
        <?php echo $extra_css; ?>
    </style>
    <?php endif; ?>
    
</head>
<body class="bg-gray-50">
<div class="page-wrapper">
    
    <?php if (!$is_auth): // הצג תפריט ניווט בכל הדפים מלבד דפי התחברות/הרשמה ?>
    
        <?php if ($is_admin || $is_builder): // תפריט ניווט לאזור המנהל ובילדר ?>
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="flex-shrink-0">
                                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="h-10">
                            </a>
                            <nav class="ml-10 flex items-center space-x-4 rtl:space-x-reverse main-nav">
                                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="px-3 py-2 text-sm font-medium <?php echo $current_page == 'dashboard' ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">
                                    <i class="ri-dashboard-line ml-1"></i> דשבורד
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="px-3 py-2 text-sm font-medium <?php echo (strpos($_SERVER['PHP_SELF'], '/landing-pages/') !== false) ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">
                                    <i class="ri-layout-3-line ml-1"></i> דפי נחיתה
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/contacts/index.php" class="px-3 py-2 text-sm font-medium <?php echo (strpos($_SERVER['PHP_SELF'], '/contacts/') !== false) ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">
                                    <i class="ri-contacts-book-line ml-1"></i> אנשי קשר
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/messaging/campaigns.php" class="px-3 py-2 text-sm font-medium <?php echo (strpos($_SERVER['PHP_SELF'], '/messaging/') !== false) ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">
                                    <i class="ri-mail-send-line ml-1"></i> דיוור
                                </a>
                                <a href="<?php echo SITE_URL; ?>/admin/settings/account.php" class="px-3 py-2 text-sm font-medium <?php echo (strpos($_SERVER['PHP_SELF'], '/settings/') !== false) ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">
                                    <i class="ri-settings-3-line ml-1"></i> הגדרות
                                </a>
                            </nav>
                        </div>
                        <div class="flex items-center">
                            <?php if (isLoggedIn()): ?>
                                <div class="ml-3 relative dropdown">
                                    <div>
                                        <button type="button" class="flex items-center max-w-xs text-sm rounded-full focus:outline-none">
                                            <span class="ml-2"><?php echo $current_user['first_name'] . ' ' . $current_user['last_name']; ?></span>
                                            <i class="ri-user-3-line p-2 bg-gray-100 rounded-full"></i>
                                        </button>
                                    </div>
                                    <div class="dropdown-content mt-2 bg-white rounded-md shadow-lg py-1 right-0">
                                        <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">הפרופיל שלי</a>
                                        <a href="<?php echo SITE_URL; ?>/admin/subscription.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">המנוי שלי</a>
                                        <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">התנתקות</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-gray-600 hover:text-gray-900">התחברות</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>
            
            <?php if ($is_admin && !$is_builder): // הצג סיידבר רק באזור המנהל ?>
                <div class="flex flex-1">
                    <div class="w-64 bg-white shadow-inner min-h-screen">
                        <?php include_once __DIR__ . '/../admin/components/sidebar.php'; ?>
                    </div>
                    <div class="flex-1">
                        <main class="p-6">
            <?php else: ?>
                <main class="py-6">
            <?php endif; ?>
        
        <?php else: // תפריט ניווט לדפים רגילים ?>
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex items-center">
                            <a href="<?php echo SITE_URL; ?>" class="flex-shrink-0">
                                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="h-10">
                            </a>
                            <nav class="ml-10 flex items-center space-x-4 rtl:space-x-reverse main-nav">
                                <a href="<?php echo SITE_URL; ?>" class="px-3 py-2 text-sm font-medium <?php echo $current_page == 'index' ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">ראשי</a>
                                <a href="<?php echo SITE_URL; ?>/features.php" class="px-3 py-2 text-sm font-medium <?php echo $current_page == 'features' ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">תכונות</a>
                                <a href="<?php echo SITE_URL; ?>/pricing.php" class="px-3 py-2 text-sm font-medium <?php echo $current_page == 'pricing' ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">מחירים</a>
                                <a href="<?php echo SITE_URL; ?>/contact.php" class="px-3 py-2 text-sm font-medium <?php echo $current_page == 'contact' ? 'active' : 'text-gray-600 hover:text-gray-900'; ?>">צור קשר</a>
                            </nav>
                        </div>
                        <div class="flex items-center">
                            <?php if (isLoggedIn()): ?>
                                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="text-sm text-indigo-600 hover:text-indigo-900 ml-4">הפאנל שלי</a>
                                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="text-sm text-gray-600 hover:text-gray-900 ml-4">התנתקות</a>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-sm text-gray-600 hover:text-gray-900 ml-4">התחברות</a>
                                <a href="<?php echo SITE_URL; ?>/auth/register.php" class="ml-4 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">הרשמה</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php endif; ?>
    
    <?php else: // דפי התחברות/הרשמה ?>
        <main class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <h2 class="text-center text-3xl font-extrabold text-gray-900">
                    <a href="<?php echo SITE_URL; ?>" class="text-center">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="h-12 mx-auto">
                    </a>
                </h2>
                <h3 class="mt-2 text-center text-xl font-bold text-gray-900">
                    <?php echo $page_title ?? ''; ?>
                </h3>
            </div>
    <?php endif; ?>
    
    <div class="content-wrapper">
        <?php 
        // הצג הודעות מערכת
        echo display_flash_messages(); 
        ?>
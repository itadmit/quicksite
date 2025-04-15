<?php
require_once '../config/init.php';

// טעינת הקומפוננטות
// טעינת הקומפוננטות
require_once 'widgets/text.php';
require_once 'widgets/button.php';
require_once 'widgets/form.php';
require_once 'widgets/row.php';
require_once 'widgets/image.php';
require_once 'widgets/video.php';
require_once 'widgets/testimonial.php';
require_once 'widgets/countdown.php';
// וידוא שמשתמש מחובר
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// קבלת מזהה דף נחיתה/תבנית אימייל אם קיים
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'landing';

// טעינת התוכן הקיים אם יש
$content = '[]';
$css = '';
$title = '';
$template_id = 0;

if ($id > 0) {
    try {
        if ($type === 'landing') {
            $stmt = $pdo->prepare("
                SELECT lp.*, lpc.content, lpc.css
                FROM landing_pages lp
                LEFT JOIN landing_page_contents lpc ON lp.id = lpc.landing_page_id AND lpc.is_current = 1
                WHERE lp.id = :id AND lp.user_id = :user_id
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT et.*, etc.content, etc.css
                FROM email_templates et
                LEFT JOIN email_template_contents etc ON et.id = etc.template_id AND etc.is_current = 1
                WHERE et.id = :id AND et.user_id = :user_id
            ");
        }
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $current_user['id']
        ]);
        
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $content = !empty($item['content']) ? $item['content'] : '[]';
            if (!isJson($content)) {
                $content = '[]';
            }
            $css = !empty($item['css']) ? $item['css'] : '';
            $title = !empty($item['title']) ? $item['title'] : '';
            $template_id = !empty($item['template_id']) ? $item['template_id'] : 0;
        }
    } catch (PDOException $e) {
        error_log('Error loading content: ' . $e->getMessage());
        $content = '[]';
        set_flash_message('אירעה שגיאה בטעינת התוכן: ' . $e->getMessage(), 'error');
    }
}

// קבלת רשימת התבניות הזמינות
$subscription = get_active_subscription($current_user['id']);
$plan_level = $subscription && isset($subscription['plan_level']) ? $subscription['plan_level'] : 'all';

if ($type === 'landing') {
    $templates = get_landing_page_templates($plan_level);
} else {
    $templates = get_email_templates($plan_level);
}

// כותרת הדף
$page_title = $id > 0 ? 'עריכת ' : 'יצירת ';
$page_title .= $type === 'landing' ? 'דף נחיתה' : 'תבנית אימייל';
if ($title) {
    $page_title .= ': ' . $title;
}
?>
<!-- builder/index.php -->
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Noto Sans Hebrew -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Hebrew:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['"Noto Sans Hebrew"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                    },
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-100 h-screen overflow-hidden">
    <?php
    // טעינת כל הווידג'טים מהתיקייה
    $widgets = [];
    $widget_files = glob('widgets/*.php');
    foreach ($widget_files as $file) {
        include_once $file;
        $widget_name = basename($file, '.php');
        if (function_exists('register_' . $widget_name . '_widget')) {
            $register_func = 'register_' . $widget_name . '_widget';
            $widgets[$widget_name] = $register_func();
        }
    }
    ?>

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 shadow-sm z-10">
        <div class="flex items-center">
            <a href="../dashboard.php" class="text-gray-500 hover:text-primary-600 ml-4">
                <i class="fas fa-arrow-right text-lg"></i>
            </a>
            <h1 class="text-xl font-bold"><?php echo htmlspecialchars($page_title); ?></h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- צפייה מקדימה -->
            <button id="btn-preview" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm transition-colors">
                <i class="far fa-eye ml-1"></i>תצוגה מקדימה
            </button>
            
            <!-- שמירה -->
            <button id="btn-save" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded text-sm font-medium transition-colors">
                <i class="far fa-save ml-1"></i>שמירה
            </button>
            
            <!-- הגדרות -->
            <button id="btn-settings" class="text-gray-500 hover:text-primary-600 p-2">
                <i class="fas fa-cog text-lg"></i>
            </button>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex h-[calc(100vh-4rem)]">
        <!-- Panel ימני - ווידג'טים -->
        <div id="widgets-panel" class="w-64 bg-white border-l border-gray-200 overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-700">רכיבים</h2>
            </div>
            <div class="p-4">
                <div class="grid gap-3">
                    <?php foreach ($widgets as $widget_id => $widget): ?>
                    <div class="widget-item" data-widget-type="<?php echo $widget_id; ?>">
                        <div class="p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 cursor-move flex items-center">
                            <i class="<?php echo $widget['icon']; ?> text-primary-500 ml-3"></i>
                            <span><?php echo $widget['title']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Canvas - אזור המרכזי -->
        <div id="canvas-container" class="flex-1 bg-gray-100 overflow-y-auto p-4">
            <div id="canvas" class="mx-auto bg-white shadow-md min-h-full" style="width: 800px; max-width: 100%;">
                <!-- כאן יתווספו השורות והעמודות -->
                <div class="flex justify-center items-center h-32 border-2 border-dashed border-gray-300 rounded bg-gray-50 text-gray-400">
                    <div class="text-center">
                        <i class="fas fa-plus-circle text-2xl mb-2"></i>
                        <p>גרור רכיבים לכאן כדי להתחיל</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel שמאלי - הגדרות -->
        <div id="settings-panel" class="w-64 bg-white border-r border-gray-200 overflow-y-auto hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-bold text-gray-700">הגדרות</h2>
            </div>
            <div id="widget-settings" class="p-4">
                <!-- כאן יוצגו הגדרות הרכיב הנבחר -->
                <div class="text-center text-gray-400 p-4">
                    <i class="fas fa-hand-pointer text-xl mb-2"></i>
                    <p>בחר רכיב כדי לערוך את ההגדרות שלו</p>
                </div>
            </div>
        </div>
    </div>

    <!-- תבניות שורה -->
    <div id="row-templates" class="hidden">
        <div class="row-template" data-columns="1">
            <div class="grid grid-cols-1 gap-4">
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
            </div>
        </div>
        <div class="row-template" data-columns="2">
            <div class="grid grid-cols-2 gap-4">
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
            </div>
        </div>
        <div class="row-template" data-columns="3">
            <div class="grid grid-cols-3 gap-4">
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
                <div class="column bg-gray-50 border border-gray-200 p-2 min-h-16 relative"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        // מטען לטעינת הסקריפטים
        document.addEventListener('DOMContentLoaded', function() {
            // טעינת הסקריפטים בסדר הנכון
            loadScript('core.js', function() {
                loadScript('settings.js', function() {
                    loadScript('common_widgets.js', function() {
                        <?php if ($type === 'landing'): ?>
                        loadScript('landing_pages.js', function() {
                            // אתחול אחרי טעינת כל הסקריפטים
                            initBuilder('landing', <?php echo json_encode($content); ?>, '<?php echo $id; ?>');
                        });
                        <?php else: ?>
                        loadScript('email.js', function() {
                            // אתחול אחרי טעינת כל הסקריפטים
                            initBuilder('email', <?php echo json_encode($content); ?>, '<?php echo $id; ?>');
                        });
                        <?php endif; ?>
                    });
                });
            });
        });

        function loadScript(src, callback) {
            const script = document.createElement('script');
            script.src = src;
            script.onload = callback;
            document.head.appendChild(script);
        }
    </script>
</body>
</html>
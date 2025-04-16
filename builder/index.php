<?php
require_once '../config/init.php';


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
$content_json = '[]'; // Renamed to avoid conflict with tab name
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
            $content_json = !empty($item['content']) ? $item['content'] : '[]';
            if (!isJson($content_json)) {
                $content_json = '[]'; // Ensure valid JSON
            }
            $css = !empty($item['css']) ? $item['css'] : '';
            $title = !empty($item['title']) ? $item['title'] : '';
            $template_id = !empty($item['template_id']) ? $item['template_id'] : 0;
        }
    } catch (PDOException $e) {
        error_log('Error loading content: ' . $e->getMessage());
        $content_json = '[]';
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
    $page_title .= ': ' . htmlspecialchars($title);
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Remix Icon CDN -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Basic Tailwind config (can be expanded)
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                       sans: ['Noto Sans Hebrew', 'sans-serif'],
                    },
                    colors: {
                      primary: { // Example primary color
                        50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e'
                      }
                    },
                    borderRadius: {
                      'xl': '1rem',
                      '2xl': '1.5rem',
                    }
                }
            }
        }
    </script>

    <!-- Builder Styles -->
    <link rel="stylesheet" href="style.css">

    <!-- Interact.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>

    <!-- SortableJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

</head>
<body class="bg-gray-50 flex flex-col h-screen">

    <!-- Header -->
    <div id="builder-header" class="bg-white shadow-sm px-4 py-3 flex justify-between items-center z-10">
        <h1 class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($page_title); ?></h1>

        <!-- Responsive View Buttons -->
        <div id="responsive-controls" class="flex items-center gap-1 bg-gray-50 rounded-xl p-1.5">
             <button class="responsive-button icon-button" data-view="desktop" data-active="true" title="תצוגת מחשב">
                <i class="ri-computer-line text-lg"></i>
             </button>
             <button class="responsive-button icon-button" data-view="tablet" data-active="false" title="תצוגת טאבלט">
                 <i class="ri-tablet-line text-lg"></i>
             </button>
             <button class="responsive-button icon-button" data-view="mobile" data-active="false" title="תצוגת מובייל">
                 <i class="ri-smartphone-line text-lg"></i>
             </button>
        </div>

        <div>
            <!-- Add other buttons here: Preview, Back to Dashboard, etc. -->
            <button id="save-button" class="bg-green-500 hover:bg-green-600 text-white py-2.5 px-5 rounded-xl shadow-sm transition-all ease-in-out flex items-center gap-2">
                <i class="ri-save-line"></i> שמור שינויים
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div id="builder-main" class="flex flex-1 overflow-hidden">

         <!-- Widget List Panel (Right - Start in RTL) -->
        <div id="widget-list-panel" class="w-64 bg-white shadow-sm h-full overflow-y-auto p-5 flex flex-col">
             <h2 class="text-lg font-medium mb-5 text-gray-800">רכיבים</h2>
             <!-- This list will be the source for dragging new widgets -->
             <div id="widget-source-list" class="widget-list flex-grow space-y-3">
                 <div data-widget-type="text" class="widget-source-item w-full flex items-center justify-center text-left bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium py-3 px-4 rounded-xl transition-colors duration-150 ease-in-out cursor-grab">
                    <i class="ri-text text-lg ml-2 text-primary-500"></i> <span>טקסט</span>
                 </div>
                 <div data-widget-type="image" class="widget-source-item w-full flex items-center justify-center text-left bg-gray-50 text-gray-400 font-medium py-3 px-4 rounded-xl cursor-not-allowed opacity-70">
                     <i class="ri-image-line text-lg ml-2"></i> <span>תמונה (בקרוב)</span>
                 </div>
                  <div data-widget-type="button" class="widget-source-item w-full flex items-center justify-center text-left bg-gray-50 text-gray-400 font-medium py-3 px-4 rounded-xl cursor-not-allowed opacity-70">
                     <i class="ri-button-line text-lg ml-2"></i> <span>כפתור (בקרוב)</span>
                 </div>
                 <!-- etc. -->
             </div>
        </div>

        <!-- Preview Area (Center) -->
        <div id="preview-area" class="flex-grow bg-gray-100 p-8 overflow-auto relative">

            <!-- Column Choice Popover (Hidden by default) -->
            <div id="column-choice-popover" class="absolute z-30 bg-white rounded-xl shadow-lg p-4 hidden" style="bottom: 90px; /* Position above the add button */ left: 50%; transform: translateX(-50%);">
                <p class="text-sm font-medium text-gray-700 mb-3 text-center">בחר מבנה עמודות:</p>
                <div class="grid grid-cols-4 gap-3">
                    <button data-columns="1" class="column-choice-button flex items-center justify-center p-3 rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-colors duration-150 bg-gray-50">
                        <div class="w-full h-7 bg-gray-300 rounded-md"></div>
                    </button>
                     <button data-columns="2" class="column-choice-button flex items-center justify-center gap-1.5 p-3 rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-colors duration-150 bg-gray-50">
                        <div class="w-1/2 h-7 bg-gray-300 rounded-md"></div>
                        <div class="w-1/2 h-7 bg-gray-300 rounded-md"></div>
                    </button>
                     <button data-columns="3" class="column-choice-button flex items-center justify-center gap-1.5 p-3 rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-colors duration-150 bg-gray-50">
                        <div class="w-1/3 h-7 bg-gray-300 rounded-md"></div>
                        <div class="w-1/3 h-7 bg-gray-300 rounded-md"></div>
                        <div class="w-1/3 h-7 bg-gray-300 rounded-md"></div>
                    </button>
                    <button data-columns="4" class="column-choice-button flex items-center justify-center gap-1.5 p-3 rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-colors duration-150 bg-gray-50">
                        <div class="w-1/4 h-7 bg-gray-300 rounded-md"></div>
                        <div class="w-1/4 h-7 bg-gray-300 rounded-md"></div>
                        <div class="w-1/4 h-7 bg-gray-300 rounded-md"></div>
                         <div class="w-1/4 h-7 bg-gray-300 rounded-md"></div>
                    </button>
                </div>
            </div>

             <!-- Add Row Button (Fixed or at the end) -->
            <button id="add-row-button" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 bg-primary-500 hover:bg-primary-600 text-white rounded-full w-14 h-14 shadow-lg transition-colors duration-150 ease-in-out flex items-center justify-center" title="הוסף שורה חדשה">
                <i class="ri-add-line text-2xl"></i>
            </button>

            <!-- Page Content Container (replaces canvas) -->
            <div id="page-content" class="relative min-h-full bg-white rounded-xl shadow-sm mx-auto" style="width: 100%; max-width: 100%; /* Start with desktop width */">
                 <!-- Rows and columns will be rendered here by JS -->
                 <!-- Widget Side Toolbar Template (will be cloned by JS) -->
                 <template id="widget-toolbar-template">
                    <div class="widget-toolbar">
                        <button class="widget-toolbar-button" data-action="edit" title="ערוך">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="widget-toolbar-button" data-action="duplicate" title="שכפל">
                            <i class="ri-file-copy-line"></i>
                        </button>
                        <button class="widget-toolbar-button" data-action="delete" title="מחק">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        <button class="widget-toolbar-button" data-action="move" title="הזז">
                            <i class="ri-drag-move-line"></i>
                        </button>
                    </div>
                 </template>
            </div>
        </div>

        <!-- Settings Panel (Left - End in RTL) -->
        <div id="settings-panel" class="w-80 bg-white shadow-sm flex flex-col h-full overflow-y-auto p-5">
            <h2 class="text-xl font-medium mb-5 text-gray-800">הגדרות</h2>
            <div class="settings-tabs flex mb-5">
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg mr-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="content" data-active="true">תוכן</button>
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg mx-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="design" data-active="false">עיצוב</button>
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg ml-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="advanced" data-active="false">מתקדם</button>
            </div>
            <div id="tab-content-area" class="tab-content flex-grow">
                <!-- הוספת ה-divs הנדרשים עבור תוכן הטאבים -->
                <div id="tab-content-content" class="tab-panel" style="display: block;">
                    <!-- תוכן טאב 'תוכן' יופיע כאן -->
                    <p class="text-gray-500 text-sm p-4 text-center">בחר אלמנט לעריכה.</p>
                </div>
                <div id="tab-content-design" class="tab-panel" style="display: none;">
                    <!-- תוכן טאב 'עיצוב' יופיע כאן -->
                </div>
                <div id="tab-content-advanced" class="tab-panel" style="display: none;">
                    <!-- תוכן טאב 'מתקדם' יופיע כאן -->
                </div>

                <!-- Templates for Controls (can stay here or be moved) -->
                <template id="padding-template">
                    <div class="settings-accordion mb-4">
                        <div class="settings-accordion-header flex justify-between items-center mb-3">
                            <h3 class="text-sm font-medium text-gray-700">ריפוד</h3>
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                        <div class="settings-accordion-content">
                            <div class="icon-group mb-3 mx-auto w-fit">
                                <button class="icon-button active" data-padding="all" title="כל הצדדים">
                                    <i class="ri-border-all"></i>
                                </button>
                                <button class="icon-button" data-padding="vertical" title="למעלה ולמטה">
                                    <i class="ri-border-top bottom"></i>
                                </button>
                                <button class="icon-button" data-padding="horizontal" title="צדדים">
                                    <i class="ri-border-right-left"></i>
                                </button>
                                <button class="icon-button" data-padding="individual" title="פרטני">
                                    <i class="ri-layout-bottom-line"></i>
                                </button>
                            </div>
                            <div class="padding-inputs grid grid-cols-4 gap-2">
                                <div class="flex flex-col items-center">
                                    <label class="text-xs text-gray-500 mb-1">למעלה</label>
                                    <input type="text" class="settings-input text-center py-1 px-2 h-8" value="0" data-padding="top">
                                </div>
                                <div class="flex flex-col items-center">
                                    <label class="text-xs text-gray-500 mb-1">ימין</label>
                                    <input type="text" class="settings-input text-center py-1 px-2 h-8" value="0" data-padding="right">
                                </div>
                                <div class="flex flex-col items-center">
                                    <label class="text-xs text-gray-500 mb-1">למטה</label>
                                    <input type="text" class="settings-input text-center py-1 px-2 h-8" value="0" data-padding="bottom">
                                </div>
                                <div class="flex flex-col items-center">
                                    <label class="text-xs text-gray-500 mb-1">שמאל</label>
                                    <input type="text" class="settings-input text-center py-1 px-2 h-8" value="0" data-padding="left">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template id="layer-template">
                    <div class="settings-accordion mb-4">
                        <div class="settings-accordion-header flex justify-between items-center mb-3">
                            <h3 class="text-sm font-medium text-gray-700">שכבה</h3>
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                        <div class="settings-accordion-content">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-600">מצב שילוב:</span>
                                <div class="relative w-32">
                                    <select class="settings-select appearance-none pr-8">
                                        <option value="normal">רגיל</option>
                                        <option value="multiply">כפל</option>
                                        <option value="screen">מסך</option>
                                        <option value="overlay">שכבה עליונה</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                        <i class="ri-arrow-down-s-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="flex justify-between mb-1">
                                    <label class="text-sm text-gray-600">אטימות:</label>
                                    <span class="text-sm font-medium text-gray-700">100%</span>
                                </div>
                                <input type="range" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer" min="0" max="1" step="0.01" value="1">
                            </div>
                        </div>
                    </div>
                </template>

                <template id="stroke-template">
                    <div class="settings-accordion mb-4">
                        <div class="settings-accordion-header flex justify-between items-center mb-3">
                            <h3 class="text-sm font-medium text-gray-700">קו מתאר</h3>
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                        <div class="settings-accordion-content">
                            <div class="flex justify-between mb-3">
                                <div class="relative w-32">
                                    <div class="flex items-center h-9 px-3 rounded-lg bg-gray-50">
                                        <span class="text-xs mr-2 w-16 uppercase">#EEEEEE</span>
                                        <div class="w-5 h-5 bg-gray-300 rounded-sm ml-auto"></div>
                                    </div>
                                    <input type="color" class="absolute inset-0 opacity-0 cursor-pointer" value="#EEEEEE">
                                </div>
                                <input type="number" class="settings-input w-16 text-center" value="100" min="0" max="100">
                                <button class="icon-button">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template id="shadow-template">
                    <div class="settings-accordion mb-4">
                        <div class="settings-accordion-header flex justify-between items-center mb-3">
                            <h3 class="text-sm font-medium text-gray-700">צל</h3>
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                        <div class="settings-accordion-content">
                            <div class="relative w-full mb-3">
                                <select class="settings-select appearance-none pr-8">
                                    <option value="drop-shadow">Drop shadow</option>
                                    <option value="none">ללא</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                    <i class="ri-arrow-down-s-line"></i>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">X</label>
                                    <input type="number" class="settings-input" value="0">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">Y</label>
                                    <input type="number" class="settings-input" value="0">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">Blur</label>
                                    <input type="number" class="settings-input" value="0">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 mb-1 block">Spread</label>
                                    <input type="number" class="settings-input" value="0">
                                </div>
                            </div>

                            <div class="relative w-full">
                                <div class="flex items-center h-9 px-3 rounded-lg bg-gray-50">
                                    <span class="text-xs mr-2 w-24 uppercase">#000000</span>
                                    <div class="w-5 h-5 bg-black rounded-sm ml-auto"></div>
                                </div>
                                <input type="color" class="absolute inset-0 opacity-0 cursor-pointer" value="#000000">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <!-- Add/Save buttons were moved -->
        </div>

    </div> <!-- End Builder Main -->

    <!-- Initial content for the builder -->
    <script>
        const initialContent = <?php echo $content_json; ?>;
        const builderType = '<?php echo $type; ?>';
        const itemId = <?php echo $id; ?>;
    </script>

    <!-- Load Core JS Module -->
    <script type="module" src="core.js"></script>
</body>
</html>
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
            <!-- Give ID and hide initially -->
            <div id="settings-tabs-container" class="settings-tabs flex mb-5 hidden">
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg mr-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="content" data-active="true">תוכן</button>
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg mx-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="design" data-active="false">עיצוב</button>
                 <button class="tab-button flex-1 py-2.5 px-4 text-center text-sm font-medium text-gray-500 hover:text-gray-700 rounded-lg ml-1 transition-colors focus:outline-none data-[active=true]:text-primary-600 data-[active=true]:bg-primary-50" data-tab="advanced" data-active="false">מתקדם</button>
            </div>
             <!-- Placeholder message moved here, visible initially -->
            <div id="settings-panel-placeholder" class="text-center text-gray-500 text-sm p-4 flex flex-col items-center justify-center flex-grow">
                <i class="ri-settings-3-line text-4xl text-gray-300 mb-3"></i>
                <span>בחר אלמנט בעמוד כדי לערוך את ההגדרות שלו.</span>
            </div>
             <!-- Hide tab content area initially -->
            <div id="tab-content-area" class="tab-content flex-grow hidden">
                <div id="tab-content-content" class="tab-panel" style="display: block;">
                    <!-- Content removed - placeholder is now outside -->
                </div>
                <div id="tab-content-design" class="tab-panel" style="display: none;">
                    <!-- תוכן טאב 'עיצוב' יופיע כאן -->
                </div>
                <div id="tab-content-advanced" class="tab-panel" style="display: none;">
                    <!-- תוכן טאב 'מתקדם' יופיע כאן -->
                </div>
                 <!-- Templates for Controls ... -->
             </div>
         </div>

    </div> <!-- End Builder Main -->

    <!-- Initial content for the builder -->
    <script>
        const initialContent = <?php echo $content_json; ?>;
        const builderType = '<?php echo $type; ?>';
        const itemId = <?php echo $id; ?>;
        window.itemSlug = <?php echo json_encode(isset($item["slug"]) ? $item["slug"] : "new-" . $type); ?>; // Use json_encode for safety
        console.log('Item Slug set in index.php:', window.itemSlug); // Verify value on load
    </script>

    <!-- Load Core JS Module -->
    <script type="module" src="core.js"></script>

    <!-- Media Library Modal -->
    <div id="media-library-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-[999] hidden p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-[80vh] flex flex-col overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-800">ספריית מדיה</h3>
                <button id="media-library-close-btn" type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <!-- Tabs & Content -->
            <div class="flex flex-grow overflow-hidden">
                <!-- Sidebar/Tabs -->
                <div class="w-48 border-r border-gray-200 bg-gray-50 flex flex-col flex-shrink-0">
                    <button data-tab="upload" class="media-tab-button p-3 text-sm text-right font-medium border-b border-gray-200 bg-white text-primary-600">
                        <i class="ri-upload-cloud-line ml-2"></i>העלאת קבצים
                    </button>
                    <button data-tab="library" class="media-tab-button p-3 text-sm text-right font-medium text-gray-600 hover:bg-gray-100">
                        <i class="ri-image-line ml-2"></i>ספריית מדיה
                    </button>
                    <!-- Add more tabs if needed -->
                </div>

                <!-- Tab Content -->
                <div class="flex-grow p-4 overflow-y-auto bg-white">
                    <!-- Upload Tab Content -->
                    <div id="media-tab-content-upload" class="media-tab-content">
                        <div id="media-upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-primary-400 bg-gray-50 mb-4">
                            <input type="file" id="media-file-input" name="image_upload" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden">
                            <i class="ri-upload-cloud-2-line text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">גרור קובץ לכאן או לחץ לבחירה</p>
                            <p class="text-xs text-gray-400 mt-1">מקסימום 5MB. סוגים מותרים: JPG, PNG, GIF, WebP</p>
                        </div>
                        <div id="media-upload-preview-area" class="hidden">
                            <p class="text-sm font-medium mb-2">תצוגה מקדימה:</p>
                            <img id="media-upload-preview-img" src="" alt="Uploaded Image Preview" class="max-w-xs max-h-40 object-contain border border-gray-200 rounded">
                            <p id="media-upload-url" class="text-xs text-gray-500 mt-1 break-all"></p>
                        </div>
                        <div id="media-upload-error" class="text-red-600 text-sm mt-2 hidden"></div>
                        <div id="media-upload-progress" class="mt-2 hidden">
                             <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-primary-500 h-1.5 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Library Tab Content -->
                    <div id="media-tab-content-library" class="media-tab-content hidden">
                        <div id="media-library-loading" class="text-center p-8 text-gray-500 hidden">
                            <i class="ri-loader-4-line text-2xl animate-spin"></i> טוען ספרייה...
                        </div>
                         <div id="media-library-error" class="text-red-600 text-sm hidden"></div>
                        <div id="media-library-grid" class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-8 gap-3">
                            <!-- Images will be loaded here -->
                        </div>
                         <div id="media-library-empty" class="text-center p-8 text-gray-500 hidden">
                            הספרייה ריקה.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end p-3 border-t border-gray-200 bg-gray-50 gap-3">
                <button id="media-library-cancel-btn" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-400">
                    ביטול
                </button>
                <button id="media-library-insert-btn" type="button" class="px-4 py-2 text-sm font-medium text-white bg-primary-500 border border-transparent rounded-md shadow-sm hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    הכנס תמונה
                </button>
            </div>
        </div>
    </div>

    <!-- Load Media Library Script -->
    <script type="module" src="media-library.js"></script>
</body>
</html>
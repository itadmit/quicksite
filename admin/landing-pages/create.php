<?php
// קבוע לזיהוי גישה ישירה לקבצים
define('QUICKSITE', true);

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// כותרת הדף
$page_title = 'יצירת דף נחיתה חדש';

// קבלת תבניות זמינות
$subscription = get_active_subscription($current_user['id']);
$plan_level = $subscription && isset($subscription['plan_level']) ? $subscription['plan_level'] : 'all';
$templates = get_landing_page_templates($plan_level);

// בדיקה למגבלת דפי נחיתה
$has_reached_limit = has_reached_limit('landing_pages');
if ($has_reached_limit) {
    set_flash_message('הגעת למגבלת דפי הנחיתה במנוי שלך. שדרג את המנוי כדי ליצור דפי נחיתה נוספים.', 'warning');
    redirect(SITE_URL . '/admin/landing-pages/');
}

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        set_flash_message('שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.', 'error');
    } else {
        // וידוא שדות חובה
        $required_fields = ['title', 'slug', 'template_id'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "שדה " . $field . " הוא שדה חובה";
            }
        }
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                set_flash_message($error, 'error');
            }
        } else {
            // קבלת התבנית הנבחרת
            $template_id = intval($_POST['template_id']);
            $template = null;
            foreach ($templates as $t) {
                if ($t['id'] === $template_id) {
                    $template = $t;
                    break;
                }
            }
            
            if (!$template) {
                set_flash_message('התבנית שנבחרה אינה זמינה', 'error');
            } else {
                try {
                    // הכנת נתוני דף הנחיתה
                    $page_data = [
                        'title' => trim($_POST['title']),
                        'slug' => create_slug($_POST['slug']),
                        'description' => trim($_POST['description'] ?? ''),
                        'template_id' => $template_id,
                        'seo_title' => trim($_POST['seo_title'] ?? ''),
                        'seo_description' => trim($_POST['seo_description'] ?? ''),
                        'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
                        'status' => 'draft',
                        'user_id' => $current_user['id']
                    ];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO landing_pages 
                        (title, slug, description, template_id, seo_title, seo_description, seo_keywords, status, user_id, created_at, updated_at)
                        VALUES 
                        (:title, :slug, :description, :template_id, :seo_title, :seo_description, :seo_keywords, :status, :user_id, NOW(), NOW())
                    ");
                    
                    if ($stmt->execute($page_data)) {
                        $landing_page_id = $pdo->lastInsertId();
                        
                        // יצירת תוכן ראשוני מהתבנית
                        $content_stmt = $pdo->prepare("
                            INSERT INTO landing_page_contents 
                            (landing_page_id, content, css, version, is_current, created_at) 
                            VALUES 
                            (:landing_page_id, :content, :css, 1, 1, NOW())
                        ");
                        
                        $content_data = [
                            'landing_page_id' => $landing_page_id,
                            'content' => $template['html_content'],
                            'css' => $template['css_content']
                        ];
                        
                        $content_stmt->execute($content_data);
                        
                        set_flash_message('דף הנחיתה נוצר בהצלחה!', 'success');
                        redirect(SITE_URL . '/admin/landing-pages/edit.php?id=' . $landing_page_id);
                    } else {
                        set_flash_message('אירעה שגיאה ביצירת דף הנחיתה', 'error');
                    }
                } catch (PDOException $e) {
                    set_flash_message('אירעה שגיאה בשמירת דף הנחיתה: ' . $e->getMessage(), 'error');
                }
            }
        }
    }
}

// כותרת העמוד
include_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    יצירת דף נחיתה חדש
                </h1>
            </div>
            
            <form method="POST" class="space-y-8 p-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <!-- בחירת תבנית -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">בחר תבנית</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($templates as $template): ?>
                            <div class="relative">
                                <input type="radio" 
                                       name="template_id" 
                                       id="template_<?php echo $template['id']; ?>" 
                                       value="<?php echo $template['id']; ?>"
                                       class="sr-only peer" 
                                       <?php echo isset($_POST['template_id']) && $_POST['template_id'] == $template['id'] ? 'checked' : ''; ?> 
                                       required>
                                <label for="template_<?php echo $template['id']; ?>" 
                                       class="relative block p-4 bg-white border-2 rounded-lg cursor-pointer transition-all duration-200 hover:border-indigo-500 peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500">
                                    <div class="absolute top-2 left-2 w-6 h-6 rounded-full border-2 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 transition-all duration-200 flex items-center justify-center">
                                        <svg class="hidden peer-checked:block w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="relative pb-[60%] mb-4 bg-gray-100 rounded overflow-hidden">
                                        <?php if ($template['thumbnail']): ?>
                                            <img src="<?php echo htmlspecialchars($template['thumbnail']); ?>" 
                                                 alt="<?php echo htmlspecialchars($template['name']); ?>"
                                                 class="absolute inset-0 w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900 peer-checked:text-indigo-600"><?php echo htmlspecialchars($template['name']); ?></h3>
                                            <?php if ($template['is_premium']): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    Premium
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="space-y-6">
                        <!-- כותרת -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">כותרת</label>
                            <input type="text" name="title" id="title" 
                                   class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   required>
                        </div>

                        <!-- מזהה URL -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">מזהה URL</label>
                            <input type="text" name="slug" id="slug" 
                                   class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                   value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>"
                                   required>
                        </div>

                        <!-- תיאור -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4 py-3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <!-- SEO -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">הגדרות SEO</h3>
                            
                            <div>
                                <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-2">כותרת SEO</label>
                                <input type="text" name="seo_title" id="seo_title" 
                                       class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                       value="<?php echo isset($_POST['seo_title']) ? htmlspecialchars($_POST['seo_title']) : ''; ?>">
                            </div>

                            <div>
                                <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור SEO</label>
                                <textarea name="seo_description" id="seo_description" rows="2"
                                          class="mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4 py-3"><?php echo isset($_POST['seo_description']) ? htmlspecialchars($_POST['seo_description']) : ''; ?></textarea>
                            </div>

                            <div>
                                <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-2">מילות מפתח SEO</label>
                                <input type="text" name="seo_keywords" id="seo_keywords" 
                                       class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                       value="<?php echo isset($_POST['seo_keywords']) ? htmlspecialchars($_POST['seo_keywords']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 space-x-reverse border-t border-gray-200 pt-6">
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/" 
                       class="inline-flex justify-center rounded-lg border-2 border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        ביטול
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center rounded-lg border-2 border-transparent bg-indigo-600 px-6 py-3 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        צור דף נחיתה
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// עדכון אוטומטי של שדה ה-slug בהתאם לכותרת
document.getElementById('title').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value.trim()) {
        slugField.value = this.value
            .trim()
            .toLowerCase()
            .replace(/[^א-ת\w\s-]/g, '')
            .replace(/\s+/g, '-');
    }
});

// הוספת אנימציה לבחירת תבנית
document.querySelectorAll('input[name="template_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // הסרת הסימון מכל התבניות
        document.querySelectorAll('.template-selected').forEach(el => {
            el.classList.remove('template-selected');
        });
        
        // הוספת סימון לתבנית הנבחרת
        if (this.checked) {
            this.closest('.relative').querySelector('label').classList.add('template-selected');
        }
    });
});
</script>

<style>
.template-selected {
    border-color: #4F46E5;
    box-shadow: 0 0 0 2px #4F46E5;
    transform: scale(1.02);
}

input[type="radio"]:checked + label .absolute {
    border-color: #4F46E5;
    background-color: #4F46E5;
}

input[type="radio"]:checked + label svg {
    display: block;
    color: white;
}
</style>

<?php include_once '../../includes/footer.php'; ?>
<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// כותרת הדף
$page_title = 'יצירת דף נחיתה חדש';

// בדיקה למגבלת דפי נחיתה
$has_reached_limit = has_reached_limit('landing_pages');
if ($has_reached_limit) {
    set_flash_message('הגעת למגבלת דפי הנחיתה במנוי שלך. שדרג את המנוי כדי ליצור דפי נחיתה נוספים.', 'warning');
    redirect(SITE_URL . '/admin/landing-pages/index.php');
}

// קבלת תבניות דפי נחיתה
$templates = get_landing_page_templates();

// קבלת רשימת דומיינים מותאמים אישית
$custom_domains = [];
if (has_permission('custom_domain')) {
    $custom_domains = get_user_custom_domains($current_user['id']);
}

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        set_flash_message('שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.', 'error');
    } else {
        $page_data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'template_id' => isset($_POST['template_id']) ? intval($_POST['template_id']) : 0,
            'custom_domain_id' => isset($_POST['custom_domain_id']) ? intval($_POST['custom_domain_id']) : null,
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
            'status' => 'draft'
        ];
        
        // בדיקת שדות חובה
        $errors = [];
        
        if (empty($page_data['title'])) {
            $errors[] = 'כותרת דף הנחיתה היא שדה חובה';
        }
        
        if (empty($page_data['slug'])) {
            // יצירת slug אוטומטי אם לא צוין
            $page_data['slug'] = create_slug($page_data['title']);
        } else {
            $page_data['slug'] = create_slug($page_data['slug']);
        }
        
        // בדיקה שה-slug ייחודי
        if (!is_slug_unique($page_data['slug'], $current_user['id'])) {
            $errors[] = 'כתובת ה-URL כבר קיימת. אנא בחר כתובת אחרת.';
        }
        
        if (empty($errors)) {
            // יצירת דף נחיתה חדש
            $result = create_landing_page($page_data, $current_user['id']);
            
            if ($result['success']) {
                set_flash_message('דף הנחיתה נוצר בהצלחה!', 'success');
                redirect(SITE_URL . '/admin/landing-pages/edit.php?id=' . $result['landing_page_id']);
            } else {
                set_flash_message('אירעה שגיאה: ' . $result['message'], 'error');
            }
        } else {
            // הצגת שגיאות
            foreach ($errors as $error) {
                set_flash_message($error, 'error');
            }
        }
    }
}

// טעינת תבנית העיצוב - הדר
include_once '../../includes/header.php';
?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg leading-6 font-medium text-gray-900">יצירת דף נחיתה חדש</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">הזן את פרטי הדף ובחר תבנית להתחלה</p>
        </div>
        <div>
            <a href="<?php echo SITE_URL; ?>/admin/landing-pages/index.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200">
                <i class="ri-arrow-right-line ml-2"></i>
                חזרה לרשימה
            </a>
        </div>
    </div>
    
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="border-t border-gray-200">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
        
        <div class="px-4 py-5 sm:p-6 space-y-6">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <!-- פרטי הדף -->
                <div class="sm:col-span-6">
                    <h3 class="text-lg font-medium text-gray-900 pb-2 mb-4 border-b border-gray-200">פרטי דף נחיתה</h3>
                </div>
                
                <div class="sm:col-span-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">כותרת <span class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <input type="text" id="title" name="title" value="<?php echo isset($page_data['title']) ? htmlspecialchars($page_data['title']) : ''; ?>" required class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="sm:col-span-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700">כתובת URL (slug)</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                            <?php echo SITE_URL . '/view/'; ?>
                        </span>
                        <input type="text" id="slug" name="slug" value="<?php echo isset($page_data['slug']) ? htmlspecialchars($page_data['slug']) : ''; ?>" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">השאר ריק ליצירה אוטומטית מכותרת הדף</p>
                </div>
                
                <?php if (!empty($custom_domains)): ?>
                <div class="sm:col-span-4">
                    <label for="custom_domain_id" class="block text-sm font-medium text-gray-700">דומיין מותאם אישית</label>
                    <div class="mt-1">
                        <select id="custom_domain_id" name="custom_domain_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">ללא (השתמש בדומיין ברירת המחדל)</option>
                            <?php foreach ($custom_domains as $domain): ?>
                                <option value="<?php echo $domain['id']; ?>" <?php echo (isset($page_data['custom_domain_id']) && $page_data['custom_domain_id'] == $domain['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($domain['domain']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <div class="sm:col-span-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">תיאור</label>
                    <div class="mt-1">
                        <textarea id="description" name="description" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($page_data['description']) ? htmlspecialchars($page_data['description']) : ''; ?></textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">תיאור קצר של דף הנחיתה לשימוש פנימי</p>
                </div>
                
                <!-- SEO -->
                <div class="sm:col-span-6">
                    <h3 class="text-lg font-medium text-gray-900 pb-2 mb-4 border-b border-gray-200">הגדרות SEO</h3>
                </div>
                
                <div class="sm:col-span-4">
                    <label for="seo_title" class="block text-sm font-medium text-gray-700">כותרת SEO</label>
                    <div class="mt-1">
                        <input type="text" id="seo_title" name="seo_title" value="<?php echo isset($page_data['seo_title']) ? htmlspecialchars($page_data['seo_title']) : ''; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">כותרת התג title לדף. השאר ריק לשימוש בכותרת הדף</p>
                </div>
                
                <div class="sm:col-span-6">
                    <label for="seo_description" class="block text-sm font-medium text-gray-700">תיאור SEO</label>
                    <div class="mt-1">
                        <textarea id="seo_description" name="seo_description" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($page_data['seo_description']) ? htmlspecialchars($page_data['seo_description']) : ''; ?></textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">תיאור meta לדף</p>
                </div>
                
                <div class="sm:col-span-6">
                    <label for="seo_keywords" class="block text-sm font-medium text-gray-700">מילות מפתח</label>
                    <div class="mt-1">
                        <input type="text" id="seo_keywords" name="seo_keywords" value="<?php echo isset($page_data['seo_keywords']) ? htmlspecialchars($page_data['seo_keywords']) : ''; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">מילות מפתח מופרדות בפסיקים</p>
                </div>
            </div>
        </div>
        
        <!-- בחירת תבנית -->
        <div class="px-4 py-5 sm:p-6 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">בחר תבנית לדף הנחיתה</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                <?php if (empty($templates)): ?>
                    <p class="col-span-3 text-gray-500 text-center py-8">לא נמצאו תבניות זמינות</p>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                        <div class="border rounded-lg overflow-hidden hover:shadow-md <?php echo (isset($page_data['template_id']) && $page_data['template_id'] == $template['id']) ? 'ring-2 ring-indigo-500' : ''; ?>">
                            <div class="relative pb-[65%] bg-gray-100">
                                <?php if (!empty($template['thumbnail'])): ?>
                                    <img src="<?php echo htmlspecialchars($template['thumbnail']); ?>" alt="<?php echo htmlspecialchars($template['name']); ?>" class="absolute h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                        <i class="ri-image-line text-4xl"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($template['is_premium'] && $template['plan_level'] == 'pro'): ?>
                                    <div class="absolute top-2 left-2 bg-indigo-500 text-white text-xs font-bold px-2 py-1 rounded">Premium</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-md font-medium text-gray-900"><?php echo htmlspecialchars($template['name']); ?></h4>
                                    <div class="mr-3">
                                        <input type="radio" id="template_<?php echo $template['id']; ?>" name="template_id" value="<?php echo $template['id']; ?>" <?php echo (isset($page_data['template_id']) && $page_data['template_id'] == $template['id']) ? 'checked' : ''; ?> class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500"><?php echo htmlspecialchars($template['description'] ?? ''); ?></p>
                                
                                <a href="#" class="template-preview mt-3 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900" data-template-id="<?php echo $template['id']; ?>">
                                    <i class="ri-eye-line ml-1"></i>
                                    תצוגה מקדימה
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="px-4 py-3 bg-gray-50 text-left sm:px-6 border-t border-gray-200">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="ri-save-line ml-2"></i>
                צור דף נחיתה
            </button>
        </div>
    </form>
</div>

<!-- תצוגה מקדימה של תבנית -->
<div id="template-preview-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-right w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            תצוגה מקדימה של תבנית
                        </h3>
                        <div class="mt-4">
                            <div id="template-preview-content" class="bg-gray-100 p-4 rounded-md border h-[500px] overflow-auto">
                                <div class="flex items-center justify-center h-full text-gray-500">
                                    <i class="ri-loader-4-line text-3xl animate-spin ml-2"></i>
                                    טוען תצוגה מקדימה...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" id="close-preview-modal">
                    סגור
                </button>
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" id="select-template-btn">
                    בחר תבנית זו
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// עדכון אוטומטי של שדה ה-slug בהתאם לכותרת
document.getElementById('title').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    if (slugField.value.trim() === '') {
        // שליחת בקשה לשרת ליצירת slug
        fetch('<?php echo SITE_URL; ?>/admin/ajax/create-slug.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'text=' + encodeURIComponent(this.value) + '&csrf_token=<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                slugField.value = data.slug;
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

// הצגת תצוגה מקדימה של תבנית
const previewLinks = document.querySelectorAll('.template-preview');
const modal = document.getElementById('template-preview-modal');
const closeButton = document.getElementById('close-preview-modal');
const selectButton = document.getElementById('select-template-btn');
const previewContent = document.getElementById('template-preview-content');
let currentTemplateId = null;

previewLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        currentTemplateId = this.getAttribute('data-template-id');
        
        // איפוס תוכן התצוגה המקדימה
        previewContent.innerHTML = `
            <div class="flex items-center justify-center h-full text-gray-500">
                <i class="ri-loader-4-line text-3xl animate-spin ml-2"></i>
                טוען תצוגה מקדימה...
            </div>
        `;
        
        // הצגת המודל
        modal.classList.remove('hidden');
        
        // טעינת תוכן התבנית
        fetch('<?php echo SITE_URL; ?>/admin/ajax/template-preview.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'template_id=' + currentTemplateId + '&csrf_token=<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                previewContent.innerHTML = data.html;
            } else {
                previewContent.innerHTML = `
                    <div class="flex items-center justify-center h-full text-red-500">
                        <i class="ri-error-warning-line text-3xl ml-2"></i>
                        שגיאה בטעינת התבנית: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            previewContent.innerHTML = `
                <div class="flex items-center justify-center h-full text-red-500">
                    <i class="ri-error-warning-line text-3xl ml-2"></i>
                    שגיאה בטעינת התבנית
                </div>
            `;
        });
    });
});

// סגירת המודל
closeButton.addEventListener('click', function() {
    modal.classList.add('hidden');
});

// סגירה בלחיצה מחוץ למודל
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        modal.classList.add('hidden');
    }
});

// בחירת תבנית מהתצוגה המקדימה
selectButton.addEventListener('click', function() {
    if (currentTemplateId) {
        const radioButton = document.getElementById('template_' + currentTemplateId);
        radioButton.checked = true;
        modal.classList.add('hidden');
        
        // גלילה אל כפתור השמירה
        const saveButton = document.querySelector('button[type="submit"]');
        saveButton.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<?php
/**
 * קבלת תבניות דפי נחיתה זמינות
 * 
 * @return array רשימת תבניות
 */
function get_landing_page_templates() {
    global $pdo, $current_user;
    
    try {
        // קבלת מנוי פעיל
        $subscription = get_active_subscription($current_user['id']);
        $plan_level = $subscription ? $subscription['plan_level'] ?? 'all' : 'all';
        
        // שליפת תבניות לפי רמת מנוי
        $query = "
            SELECT * FROM templates 
            WHERE type = 'landing_page' 
            AND (plan_level = 'all' OR plan_level = ?)
            ORDER BY is_premium DESC, name ASC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$plan_level]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת תבניות דפי נחיתה: " . $e->getMessage());
        return [];
    }
}

/**
 * בדיקה אם slug ייחודי למשתמש
 * 
 * @param string $slug ה-slug לבדיקה
 * @param int $user_id מזהה המשתמש
 * @return bool האם ה-slug ייחודי
 */
function is_slug_unique($slug, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM landing_pages 
            WHERE slug = ? AND user_id = ?
        ");
        $stmt->execute([$slug, $user_id]);
        return $stmt->fetchColumn() == 0;
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת ייחודיות slug: " . $e->getMessage());
        return false;
    }
}

/**
 * יצירת דף נחיתה חדש
 * 
 * @param array $page_data נתוני הדף
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message, landing_page_id]
 */

?>

<?php include_once '../../includes/footer.php'; ?>
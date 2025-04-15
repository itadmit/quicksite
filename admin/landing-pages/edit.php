<?php
// קבוע לזיהוי גישה ישירה לקבצים
define('QUICKSITE', true);

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// וידוא שהמשתמש מחובר
require_login();

// וידוא שיש למשתמש מנוי פעיל
require_subscription();

// בדיקת קיום מזהה דף הנחיתה
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash_message('מזהה דף הנחיתה חסר או לא תקין', 'error');
    redirect(SITE_URL . '/admin/landing-pages/');
}

$landing_page_id = intval($_GET['id']);

// קבלת פרטי דף הנחיתה
try {
    $stmt = $pdo->prepare("
        SELECT lp.*, lpc.content, lpc.css
        FROM landing_pages lp
        LEFT JOIN landing_page_contents lpc ON lp.id = lpc.landing_page_id AND lpc.is_current = 1
        WHERE lp.id = :id AND lp.user_id = :user_id
    ");
    
    $stmt->execute([
        'id' => $landing_page_id,
        'user_id' => $current_user['id']
    ]);
    
    $landing_page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$landing_page) {
        set_flash_message('דף הנחיתה לא נמצא', 'error');
        redirect(SITE_URL . '/admin/landing-pages/');
    }
} catch (PDOException $e) {
    set_flash_message('אירעה שגיאה בטעינת דף הנחיתה: ' . $e->getMessage(), 'error');
    redirect(SITE_URL . '/admin/landing-pages/');
}

// קבלת תבניות זמינות
$subscription = get_active_subscription($current_user['id']);
$plan_level = $subscription && isset($subscription['plan_level']) ? $subscription['plan_level'] : 'all';
$templates = get_landing_page_templates($plan_level);

// טיפול בשליחת טופס
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        set_flash_message('שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.', 'error');
    } else {
        // וידוא שדות חובה
        $required_fields = ['title', 'slug'];
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
            try {
                // הכנת נתוני העדכון
                $page_data = [
                    'id' => $landing_page_id,
                    'title' => trim($_POST['title']),
                    'slug' => create_slug($_POST['slug']),
                    'description' => trim($_POST['description'] ?? ''),
                    'seo_title' => trim($_POST['seo_title'] ?? ''),
                    'seo_description' => trim($_POST['seo_description'] ?? ''),
                    'seo_keywords' => trim($_POST['seo_keywords'] ?? ''),
                    'status' => $_POST['status'] ?? $landing_page['status']
                ];
                
                // עדכון פרטי דף הנחיתה
                $stmt = $pdo->prepare("
                    UPDATE landing_pages 
                    SET title = :title,
                        slug = :slug,
                        description = :description,
                        seo_title = :seo_title,
                        seo_description = :seo_description,
                        seo_keywords = :seo_keywords,
                        status = :status,
                        updated_at = NOW()
                    WHERE id = :id AND user_id = :user_id
                ");
                
                $stmt->execute(array_merge($page_data, ['user_id' => $current_user['id']]));
                
                // עדכון תוכן אם נשלח
                if (isset($_POST['content']) && isset($_POST['css'])) {
                    // יצירת גרסה חדשה של התוכן
                    $content_stmt = $pdo->prepare("
                        INSERT INTO landing_page_contents 
                        (landing_page_id, content, css, version, is_current, created_at)
                        VALUES 
                        (:landing_page_id, :content, :css, 
                         (SELECT COALESCE(MAX(version), 0) + 1 FROM landing_page_contents WHERE landing_page_id = :landing_page_id2),
                         1, NOW())
                    ");
                    
                    // עדכון הגרסה הנוכחית ל-0
                    $pdo->prepare("
                        UPDATE landing_page_contents 
                        SET is_current = 0 
                        WHERE landing_page_id = :landing_page_id
                    ")->execute(['landing_page_id' => $landing_page_id]);
                    
                    // הוספת הגרסה החדשה
                    $content_stmt->execute([
                        'landing_page_id' => $landing_page_id,
                        'landing_page_id2' => $landing_page_id,
                        'content' => $_POST['content'],
                        'css' => $_POST['css']
                    ]);
                }
                
                set_flash_message('דף הנחיתה עודכן בהצלחה!', 'success');
                redirect(SITE_URL . '/admin/landing-pages/edit.php?id=' . $landing_page_id);
                
            } catch (PDOException $e) {
                set_flash_message('אירעה שגיאה בשמירת דף הנחיתה: ' . $e->getMessage(), 'error');
            }
        }
    }
}

// כותרת הדף
$page_title = 'עריכת דף נחיתה: ' . htmlspecialchars($landing_page['title']);

// טעינת ממשק המשתמש
include_once '../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        עריכת דף נחיתה
                    </h1>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <a href="<?php echo SITE_URL . '/preview.php?id=' . $landing_page_id; ?>" 
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 ml-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            תצוגה מקדימה
                        </a>
                        <a href="<?php echo SITE_URL . '/builder/?type=landing&id=' . $landing_page_id; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 ml-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                            עריכה בבילדר
                        </a>
                    </div>
                </div>
            </div>
            
            <form method="POST" class="space-y-8 p-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="space-y-6">
                    <!-- כותרת -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">כותרת</label>
                        <input type="text" name="title" id="title" 
                               class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                               value="<?php echo htmlspecialchars($landing_page['title']); ?>"
                               required>
                    </div>

                    <!-- מזהה URL -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">מזהה URL</label>
                        <input type="text" name="slug" id="slug" 
                               class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                               value="<?php echo htmlspecialchars($landing_page['slug']); ?>"
                               required>
                    </div>

                    <!-- תיאור -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">תיאור</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4 py-3"><?php echo htmlspecialchars($landing_page['description']); ?></textarea>
                    </div>

                    <!-- סטטוס -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">סטטוס</label>
                        <select name="status" id="status"
                                class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4">
                            <option value="draft" <?php echo $landing_page['status'] === 'draft' ? 'selected' : ''; ?>>טיוטה</option>
                            <option value="published" <?php echo $landing_page['status'] === 'published' ? 'selected' : ''; ?>>מפורסם</option>
                        </select>
                    </div>

                    <!-- SEO -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900">הגדרות SEO</h3>
                        
                        <div>
                            <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-2">כותרת SEO</label>
                            <input type="text" name="seo_title" id="seo_title" 
                                   class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                   value="<?php echo htmlspecialchars($landing_page['seo_title']); ?>">
                        </div>

                        <div>
                            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-2">תיאור SEO</label>
                            <textarea name="seo_description" id="seo_description" rows="2"
                                      class="mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4 py-3"><?php echo htmlspecialchars($landing_page['seo_description']); ?></textarea>
                        </div>

                        <div>
                            <label for="seo_keywords" class="block text-sm font-medium text-gray-700 mb-2">מילות מפתח SEO</label>
                            <input type="text" name="seo_keywords" id="seo_keywords" 
                                   class="h-12 mt-1 block w-full rounded-lg border-2 border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white hover:bg-gray-50 px-4"
                                   value="<?php echo htmlspecialchars($landing_page['seo_keywords']); ?>">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 space-x-reverse border-t border-gray-200 pt-6">
                    <a href="<?php echo SITE_URL; ?>/admin/landing-pages/" 
                       class="inline-flex justify-center rounded-lg border-2 border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        חזרה לרשימה
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center rounded-lg border-2 border-transparent bg-indigo-600 px-6 py-3 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        שמור שינויים
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
</script>

<?php include_once '../../includes/footer.php'; ?>

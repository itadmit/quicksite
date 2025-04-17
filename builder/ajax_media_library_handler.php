<?php
// session_start(); // Removed: Assuming init.php already starts the session

// Include init file
require_once __DIR__ . '/../config/init.php'; // Adjust path as needed

$debug_info = []; // Initialize debug array

// --- שינוי: קבלת ה-slug מהבקשה --- 
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    echo json_encode(['success' => false, 'error' => 'שגיאה: מזהה הדף (slug) חסר.', 'images' => [], 'debug' => ['error_point' => 'Missing slug']]);
    exit;
}
$slug = $_GET['slug'];
$debug_info['raw_slug'] = $slug;

// ניקוי בסיסי של ה-slug (כמו בקובץ ההעלאה)
$slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug);
$slug = trim($slug, '-');
$debug_info['cleaned_slug'] = $slug;

if (empty($slug)) {
    echo json_encode(['success' => false, 'error' => 'שגיאה: מזהה הדף (slug) לא תקין.', 'images' => [], 'debug' => $debug_info]);
    exit;
}
// ------------------------------------

// Validate user authentication (assuming init.php handles this and provides $current_user)
if (!isset($current_user) || empty($current_user['id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.', 'images' => [], 'debug' => ['error_point' => 'User not authenticated']]);
    exit;
}
$debug_info['user_id'] = $current_user['id'];
//$userId = $current_user['id']; // אפשר עדיין להשתמש בו לאימותים אחרים אם צריך, אבל לא לנתיב

header('Content-Type: application/json');

// --- שינוי: נתיב מבוסס slug --- 
$baseUploadDir = rtrim(UPLOAD_DIR, '/');
$baseUploadUrl = rtrim(UPLOAD_URL, '/');

$itemUploadDir = $baseUploadDir . '/' . $slug;
$itemUploadUrl = $baseUploadUrl . '/' . $slug;
$debug_info['item_upload_dir'] = $itemUploadDir;
$debug_info['item_upload_url'] = $itemUploadUrl;
// ------------------------------------

$images = [];

// --- שינוי: בדיקה אם תיקיית ה-slug קיימת --- 
$debug_info['is_dir_check'] = is_dir($itemUploadDir);
if ($debug_info['is_dir_check']) {
    $files = scandir($itemUploadDir);
    $debug_info['scandir_result'] = $files; // Dump the result of scandir
    if ($files) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && !is_dir($itemUploadDir . '/' . $file)) {
                // Check if it's a valid image file (you might want more robust checks)
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $debug_info['files_processed'][] = ['name' => $file, 'ext' => $ext, 'is_allowed' => in_array($ext, $allowedExtensions)];
                if (in_array($ext, $allowedExtensions)) {
                    // --- שינוי: שימוש ב-URL של ה-slug ---
                    $images[] = $itemUploadUrl . '/' . $file;
                    // -------------------------------------
                }
            }
        }
        // Sort images, maybe newest first? (Optional - based on file modification time)
        // This basic example just returns them as scandir finds them.
    } else {
        $debug_info['scandir_error'] = 'scandir returned false';
    }
} else {
    $debug_info['dir_not_found'] = $itemUploadDir;
}
// ------------------------------------

$debug_info['final_images_array'] = $images;

echo json_encode(['success' => true, 'images' => $images, 'debug' => $debug_info]);

exit;
?> 
<?php
// Set header for JSON response
header('Content-Type: application/json');

// --- שינוי: טעינת init.php לזיהוי משתמש ---
// הנחה: init.php נמצא בתיקייה הראשית ומגדיר $current_user
require_once __DIR__ . '/../config/init.php'; 

// וידוא שמשתמש מחובר (אמור להיות מכוסה על ידי init.php/דפים קודמים, אבל בדיקה נוספת)
if (!isLoggedIn() || !isset($current_user['id'])) {
    echo json_encode(['success' => false, 'error' => 'גישה נדחתה. יש להתחבר.']);
    exit;
}
$userId = $current_user['id'];
// ---------------------------------------------

// --- שינוי: קבלת ה-slug מהבקשה --- 
if (!isset($_POST['slug']) || empty($_POST['slug'])) {
    echo json_encode(['success' => false, 'error' => 'שגיאה: מזהה הדף (slug) חסר.']);
    exit;
}
$slug = $_POST['slug'];
// ניקוי בסיסי של ה-slug (אפשר להוסיף עוד חוקים אם צריך)
$slug = preg_replace('/[^a-zA-Z0-9_-]/', '-', $slug);
$slug = trim($slug, '-');
if (empty($slug)) {
     echo json_encode(['success' => false, 'error' => 'שגיאה: מזהה הדף (slug) לא תקין.']);
    exit;
}
// ------------------------------------

// Basic security: Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// --- File Handling ---
// Check if file was uploaded without errors
if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
    
    $file = $_FILES['image_upload'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type']; // MIME type

    // --- Validation ---
    // 1. Size validation
    if ($fileSize > UPLOAD_MAX_SIZE) {
        echo json_encode(['success' => false, 'error' => 'הקובץ גדול מדי (מקסימום ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB).']);
        exit;
    }

    // 2. Type validation (using MIME type and allowed extensions from config)
    $allowedExtensions = explode(',', UPLOAD_ALLOWED_EXTENSIONS);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']; // Explicitly allow image types

    if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'error' => 'סוג הקובץ אינו נתמך.']);
        exit;
    }

    // --- שינוי: נתיבים ספציפיים לפי slug --- 
    $baseUploadDir = rtrim(UPLOAD_DIR, '/'); 
    $baseUploadUrl = rtrim(UPLOAD_URL, '/');
    
    // שימוש ב-$slug במקום ב-$userId
    $itemUploadDir = $baseUploadDir . '/' . $slug;
    $itemUploadUrl = $baseUploadUrl . '/' . $slug;

    // ודא שהתיקייה הספציפית קיימת
    if (!is_dir($itemUploadDir)) {
        if (!mkdir($itemUploadDir, 0755, true)) {
             echo json_encode(['success' => false, 'error' => 'שגיאה: לא ניתן ליצור את תיקיית הדף.']);
             exit;
        }
    }
    // ----------------------------------------
    
    // Use hash of the original name and timestamp for uniqueness, keep original extension temporarily
    $uniquePrefix = hash('sha256', $fileName . time()); 
    $newWebpFilename = $uniquePrefix . '.webp';
    // --- שינוי: שימוש בנתיב ה-slug --- 
    $destinationPath = $itemUploadDir . '/' . $newWebpFilename;
    $destinationUrl = $itemUploadUrl . '/' . $newWebpFilename;
    // ----------------------------------

    // --- Image Conversion to WebP using GD ---
    $image = null;
    $success = false;

    try {
        switch ($fileType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($fileTmpName);
                break;
            case 'image/png':
                $image = imagecreatefrompng($fileTmpName);
                // Preserve transparency for PNG
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($fileTmpName);
                // Preserve transparency for GIF
                imagepalettetotruecolor($image);
                $transparentIndex = imagecolortransparent($image);
                if ($transparentIndex >= 0) {
                   $transparentColor = imagecolorsforindex($image, $transparentIndex);
                   $transparentNew = imagecolorallocatealpha($image, $transparentColor['red'], $transparentColor['green'], $transparentColor['blue'], 127);
                   imagefill($image, 0, 0, $transparentNew);
                   imagecolortransparent($image, $transparentNew);
                   imagealphablending($image, true); // May not be needed after fill
                   imagesavealpha($image, true); 
                }
                break;
             case 'image/webp': // If already webp, just copy it (or re-save to apply quality settings if desired)
                // For now, let's just copy it if it's already WebP
                if (copy($fileTmpName, $destinationPath)) {
                   $success = true; 
                   $image = false; // Prevent further processing below
                } else {
                    throw new Exception("שגיאה בהעתקת קובץ WebP קיים.");
                }
                break; 
            default:
                // This should not happen due to earlier validation, but as a fallback
                throw new Exception("סוג קובץ לא נתמך להמרה (GD).");
        }

        // If image resource was created (and not just copied)
        if ($image !== null && $image !== false) {
             // Save as WebP (quality 80 is a good balance)
            if (imagewebp($image, $destinationPath, 80)) {
                $success = true;
            } else {
                throw new Exception("שגיאה בשמירת קובץ WebP.");
            }
        } elseif($image === false && $success) {
            // This means it was already webp and successfully copied
        } 
        else {
             // This case handles if imagecreatefrom* failed silently or other issues
             if(!$success) { // double check success flag wasn't set by copy
                throw new Exception("שגיאה ביצירת אובייקט תמונה.");
             }
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        if ($image && is_resource($image)) { // Clean up image resource if created
            imagedestroy($image);
        }
        exit;
    } finally {
        // Always destroy the image resource if it was created
        if ($image && is_resource($image)) { 
            imagedestroy($image);
        }
    }

    // --- Final Response ---
    if ($success) {
        echo json_encode(['success' => true, 'url' => $destinationUrl]);
    } else {
        // If success is still false here, something unexpected happened
        echo json_encode(['success' => false, 'error' => 'שגיאה לא ידועה במהלך העיבוד.']);
    }

} elseif (isset($_FILES['image_upload']['error'])) {
    // Handle specific upload errors
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'הקובץ חורג מהגודל המוגדר בשרת (php.ini).',
        UPLOAD_ERR_FORM_SIZE => 'הקובץ חורג מהגודל המוגדר בטופס.',
        UPLOAD_ERR_PARTIAL => 'הקובץ הועלה באופן חלקי.',
        UPLOAD_ERR_NO_FILE => 'לא נבחר קובץ להעלאה.',
        UPLOAD_ERR_NO_TMP_DIR => 'חסרה תיקייה זמנית בשרת.',
        UPLOAD_ERR_CANT_WRITE => 'כשל בכתיבת הקובץ לדיסק.',
        UPLOAD_ERR_EXTENSION => 'הרחבת PHP עצרה את העלאת הקובץ.',
    ];
    $errorCode = $_FILES['image_upload']['error'];
    $errorMessage = $uploadErrors[$errorCode] ?? 'שגיאת העלאה לא ידועה.';
    echo json_encode(['success' => false, 'error' => $errorMessage]);
} else {
    // No file uploaded
    echo json_encode(['success' => false, 'error' => 'לא נשלח קובץ.']);
}

exit; // Ensure script termination
?> 
<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * העלאת קובץ למערכת
 * 
 * @param array $file מערך הקובץ מ-$_FILES
 * @param string $type סוג הקובץ (images, files)
 * @param int $user_id מזהה המשתמש
 * @return array תוצאות ההעלאה [success, message, file_data]
 */
function upload_file($file, $type = 'images', $user_id = null) {
    global $pdo, $current_user;
    
    $result = [
        'success' => false,
        'message' => '',
        'file_data' => null
    ];
    
    // אם לא התקבל מזהה משתמש, השתמש במשתמש הנוכחי
    if ($user_id === null) {
        if (!$current_user) {
            $result['message'] = 'המשתמש אינו מחובר';
            return $result;
        }
        
        $user_id = $current_user['id'];
    }
    
    // בדיקה אם הקובץ קיים
    if (!isset($file) || !is_array($file) || empty($file['name']) || $file['error'] != UPLOAD_ERR_OK) {
        $result['message'] = 'לא נבחר קובץ או שאירעה שגיאה בהעלאה';
        return $result;
    }
    
    // בדיקת גודל הקובץ
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $result['message'] = 'הקובץ גדול מדי. הגודל המקסימלי המותר הוא ' . format_file_size(UPLOAD_MAX_SIZE);
        return $result;
    }
    
    // קבלת סיומת הקובץ
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // בדיקה אם הסיומת מותרת
    $allowed_exts = explode(',', UPLOAD_ALLOWED_EXTENSIONS);
    if (!in_array($file_ext, $allowed_exts)) {
        $result['message'] = 'סוג הקובץ אינו מותר. הסיומות המותרות הן: ' . UPLOAD_ALLOWED_EXTENSIONS;
        return $result;
    }
    
    // יצירת שם ייחודי לקובץ
    $new_filename = generate_random_string(10) . '_' . time() . '.' . $file_ext;
    
    // הגדרת יעד ההעלאה
    $upload_dir = UPLOAD_DIR . '/' . $type;
    
    // וידוא שתיקיית היעד קיימת
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . '/' . $new_filename;
    
    // העלאת הקובץ
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $result['message'] = 'אירעה שגיאה בהעלאת הקובץ';
        return $result;
    }
    
    // טיפול בקבצי תמונה - המרה ל-WebP וכו'
    $dimensions = '';
    $webp_filename = '';
    
    if ($type === 'images' && in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        // קבלת ממדי התמונה
        list($width, $height) = getimagesize($upload_path);
        $dimensions = $width . 'x' . $height;
        
        // המרה ל-WebP אם PHP תומך בכך
        if (function_exists('imagewebp')) {
            $webp_filename = pathinfo($new_filename, PATHINFO_FILENAME) . '.webp';
            $webp_path = $upload_dir . '/' . $webp_filename;
            
            // יצירת תמונת מקור בהתאם לסוג הקובץ
            switch ($file_ext) {
                case 'jpg':
                case 'jpeg':
                    $source_image = imagecreatefromjpeg($upload_path);
                    break;
                case 'png':
                    $source_image = imagecreatefrompng($upload_path);
                    // שמירה על שקיפות
                    imagepalettetotruecolor($source_image);
                    imagealphablending($source_image, true);
                    imagesavealpha($source_image, true);
                    break;
                case 'gif':
                    $source_image = imagecreatefromgif($upload_path);
                    break;
            }
            
            // המרה ל-WebP
            if (isset($source_image) && $source_image !== false) {
                imagewebp($source_image, $webp_path, 80); // איכות 80%
                imagedestroy($source_image);
            }
        }
    }
    
    // שמירת פרטי הקובץ במסד הנתונים
    try {
        $stmt = $pdo->prepare("
            INSERT INTO media (
                user_id, filename, original_filename, file_path, file_type, 
                file_size, dimensions, webp_filename, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $file_path = '/' . $type . '/' . $new_filename;
        
        $stmt->execute([
            $user_id,
            $new_filename,
            $file['name'],
            $file_path,
            $file['type'],
            $file['size'],
            $dimensions,
            $webp_filename
        ]);
        
        $file_id = $pdo->lastInsertId();
        
        // הכנת נתוני הקובץ להחזרה
        $file_data = [
            'id' => $file_id,
            'filename' => $new_filename,
            'original_filename' => $file['name'],
            'file_path' => $file_path,
            'file_type' => $file['type'],
            'file_size' => $file['size'],
            'file_size_formatted' => format_file_size($file['size']),
            'dimensions' => $dimensions,
            'webp_filename' => $webp_filename,
            'url' => UPLOAD_URL . $file_path,
            'webp_url' => $webp_filename ? UPLOAD_URL . '/' . $type . '/' . $webp_filename : ''
        ];
        
        $result['success'] = true;
        $result['message'] = 'הקובץ הועלה בהצלחה';
        $result['file_data'] = $file_data;
        
        // אם מוגדר AWS, העלה גם לשם
        if (AWS_ENABLED) {
            $s3_result = upload_to_s3($upload_path, $type . '/' . $new_filename);
            
            if ($s3_result['success'] && !empty($webp_filename) && file_exists($webp_path)) {
                upload_to_s3($webp_path, $type . '/' . $webp_filename);
            }
            
            // עדכון המיקום של הקובץ ב-S3
            if ($s3_result['success']) {
                $update = $pdo->prepare("UPDATE media SET s3_path = ? WHERE id = ?");
                $update->execute([$s3_result['s3_path'], $file_id]);
                
                $file_data['s3_url'] = AWS_S3_URL . '/' . $s3_result['s3_path'];
                $result['file_data'] = $file_data;
            }
        }
        
        return $result;
        
    } catch (PDOException $e) {
        // מחיקת הקובץ שהועלה במקרה של שגיאה
        @unlink($upload_path);
        if (!empty($webp_filename) && file_exists($webp_path)) {
            @unlink($webp_path);
        }
        
        $result['message'] = 'אירעה שגיאה בשמירת פרטי הקובץ';
        error_log("שגיאה בהעלאת קובץ: " . $e->getMessage());
        return $result;
    }
}

/**
 * העלאת קובץ ל-AWS S3
 * 
 * @param string $file_path נתיב הקובץ המקומי
 * @param string $s3_path נתיב היעד ב-S3
 * @return array תוצאות ההעלאה [success, message, s3_path]
 */
function upload_to_s3($file_path, $s3_path) {
    $result = [
        'success' => false,
        'message' => '',
        's3_path' => ''
    ];
    
    // בדיקה אם AWS מוגדר
    if (!AWS_ENABLED) {
        $result['message'] = 'AWS לא מוגדר במערכת';
        return $result;
    }
    
    // בדיקה אם הספריות הנדרשות קיימות
    if (!class_exists('Aws\S3\S3Client')) {
        $result['message'] = 'ספריית AWS SDK חסרה';
        error_log("ספריית AWS SDK חסרה. התקן את הספרייה באמצעות Composer.");
        return $result;
    }
    
    try {
        // יצירת לקוח S3
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => AWS_S3_REGION,
            'credentials' => [
                'key' => AWS_ACCESS_KEY,
                'secret' => AWS_SECRET_KEY
            ]
        ]);
        
        // העלאת הקובץ
        $s3->putObject([
            'Bucket' => AWS_S3_BUCKET,
            'Key' => $s3_path,
            'SourceFile' => $file_path,
            'ACL' => 'public-read'
        ]);
        
        $result['success'] = true;
        $result['message'] = 'הקובץ הועלה ל-S3 בהצלחה';
        $result['s3_path'] = $s3_path;
        
        return $result;
        
    } catch (Exception $e) {
        $result['message'] = 'שגיאה בהעלאת הקובץ ל-S3';
        error_log("שגיאה בהעלאת קובץ ל-S3: " . $e->getMessage());
        return $result;
    }
}

/**
 * מחיקת קובץ מהמערכת
 * 
 * @param int $file_id מזהה הקובץ
 * @param int $user_id מזהה המשתמש (לצורך אבטחה)
 * @return array תוצאות המחיקה [success, message]
 */
function delete_file($file_id, $user_id = null) {
    global $pdo, $current_user;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    // אם לא התקבל מזהה משתמש, השתמש במשתמש הנוכחי
    if ($user_id === null) {
        if (!$current_user) {
            $result['message'] = 'המשתמש אינו מחובר';
            return $result;
        }
        
        $user_id = $current_user['id'];
    }
    
    try {
        // קבלת פרטי הקובץ
        $stmt = $pdo->prepare("SELECT * FROM media WHERE id = ? AND user_id = ?");
        $stmt->execute([$file_id, $user_id]);
        $file = $stmt->fetch();
        
        if (!$file) {
            $result['message'] = 'הקובץ לא נמצא או שאין הרשאה למחוק אותו';
            return $result;
        }
        
        // מחיקת הקובץ מהדיסק
        $file_path = UPLOAD_DIR . $file['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // מחיקת גרסת WebP אם קיימת
        if (!empty($file['webp_filename'])) {
            $webp_path = dirname($file_path) . '/' . $file['webp_filename'];
            if (file_exists($webp_path)) {
                unlink($webp_path);
            }
        }
        
        // מחיקה מ-S3 אם מוגדר
        if (AWS_ENABLED && !empty($file['s3_path'])) {
            delete_from_s3($file['s3_path']);
            
            // מחיקת גרסת WebP מ-S3
            if (!empty($file['webp_filename'])) {
                $webp_s3_path = dirname($file['s3_path']) . '/' . $file['webp_filename'];
                delete_from_s3($webp_s3_path);
            }
        }
        
        // מחיקת הרשומה ממסד הנתונים
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$file_id]);
        
        $result['success'] = true;
        $result['message'] = 'הקובץ נמחק בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'אירעה שגיאה במחיקת הקובץ';
        error_log("שגיאה במחיקת קובץ: " . $e->getMessage());
        return $result;
    }
}

/**
 * מחיקת קובץ מ-AWS S3
 * 
 * @param string $s3_path נתיב הקובץ ב-S3
 * @return array תוצאות המחיקה [success, message]
 */
function delete_from_s3($s3_path) {
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    // בדיקה אם AWS מוגדר
    if (!AWS_ENABLED) {
        $result['message'] = 'AWS לא מוגדר במערכת';
        return $result;
    }
    
    // בדיקה אם הספריות הנדרשות קיימות
    if (!class_exists('Aws\S3\S3Client')) {
        $result['message'] = 'ספריית AWS SDK חסרה';
        error_log("ספריית AWS SDK חסרה. התקן את הספרייה באמצעות Composer.");
        return $result;
    }
    
    try {
        // יצירת לקוח S3
        $s3 = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => AWS_S3_REGION,
            'credentials' => [
                'key' => AWS_ACCESS_KEY,
                'secret' => AWS_SECRET_KEY
            ]
        ]);
        
        // מחיקת הקובץ
        $s3->deleteObject([
            'Bucket' => AWS_S3_BUCKET,
            'Key' => $s3_path
        ]);
        
        $result['success'] = true;
        $result['message'] = 'הקובץ נמחק מ-S3 בהצלחה';
        
        return $result;
        
    } catch (Exception $e) {
        $result['message'] = 'שגיאה במחיקת הקובץ מ-S3';
        error_log("שגיאה במחיקת קובץ מ-S3: " . $e->getMessage());
        return $result;
    }
}

/**
 * קבלת רשימת הקבצים של משתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @param string $type סוג הקבצים (images, files, all)
 * @param int $page מספר העמוד
 * @param int $per_page מספר פריטים בעמוד
 * @return array רשימת הקבצים והנתונים הקשורים [files, total_pages, total_files]
 */
function get_user_files($user_id, $type = 'all', $page = 1, $per_page = 20) {
    global $pdo;
    
    $result = [
        'files' => [],
        'total_pages' => 0,
        'total_files' => 0
    ];
    
    try {
        // תנאי סינון לפי סוג
        $type_condition = ($type !== 'all') ? " AND file_path LIKE '/$type/%'" : "";
        
        // ספירת סך הקבצים
        $count_query = "SELECT COUNT(*) FROM media WHERE user_id = ?" . $type_condition;
        $stmt = $pdo->prepare($count_query);
        $stmt->execute([$user_id]);
        $total_files = $stmt->fetchColumn();
        
        // חישוב דפים
        $total_pages = ceil($total_files / $per_page);
        $page = max(1, min($page, $total_pages));
        $offset = ($page - 1) * $per_page;
        
        // שליפת הקבצים
        $query = "
            SELECT * FROM media 
            WHERE user_id = ?" . $type_condition . " 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $per_page, $offset]);
        $files = $stmt->fetchAll();
        
        // עיבוד פרטי הקבצים
        foreach ($files as &$file) {
            $file['file_size_formatted'] = format_file_size($file['file_size']);
            $file['created_at_formatted'] = format_date($file['created_at']);
            $file['url'] = UPLOAD_URL . $file['file_path'];
            $file['webp_url'] = !empty($file['webp_filename']) ? 
                UPLOAD_URL . '/' . dirname(ltrim($file['file_path'], '/')) . '/' . $file['webp_filename'] : '';
            
            // אם יש מיקום ב-S3
            if (AWS_ENABLED && !empty($file['s3_path'])) {
                $file['s3_url'] = AWS_S3_URL . '/' . $file['s3_path'];
                $file['webp_s3_url'] = !empty($file['webp_filename']) ? 
                    AWS_S3_URL . '/' . dirname($file['s3_path']) . '/' . $file['webp_filename'] : '';
            }
        }
        
        $result['files'] = $files;
        $result['total_pages'] = $total_pages;
        $result['total_files'] = $total_files;
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת רשימת קבצים: " . $e->getMessage());
        return $result;
    }
}

/**
 * הוספת עמודת webp_filename לטבלת media אם היא חסרה
 * 
 * @return void
 */
function add_webp_filename_column() {
    global $pdo;
    
    try {
        // בדיקה אם העמודה קיימת
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'media' AND COLUMN_NAME = 'webp_filename'
        ");
        $stmt->execute([DB_NAME]);
        
        if ($stmt->fetchColumn() == 0) {
            // הוספת העמודה
            $pdo->query("ALTER TABLE `media` ADD COLUMN `webp_filename` VARCHAR(255) NULL");
        }
    } catch (PDOException $e) {
        error_log("שגיאה בהוספת עמודת webp_filename: " . $e->getMessage());
    }
}

/**
 * הוספת עמודת s3_path לטבלת media אם היא חסרה
 * 
 * @return void
 */
function add_s3_path_column() {
    global $pdo;
    
    try {
        // בדיקה אם העמודה קיימת
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'media' AND COLUMN_NAME = 's3_path'
        ");
        $stmt->execute([DB_NAME]);
        
        if ($stmt->fetchColumn() == 0) {
            // הוספת העמודה
            $pdo->query("ALTER TABLE `media` ADD COLUMN `s3_path` VARCHAR(255) NULL");
        }
    } catch (PDOException $e) {
        error_log("שגיאה בהוספת עמודת s3_path: " . $e->getMessage());
    }
}

// וידוא שהעמודות הנדרשות קיימות
add_webp_filename_column();
add_s3_path_column();

// יצירת ספריות העלאה אם לא קיימות
create_upload_directories();
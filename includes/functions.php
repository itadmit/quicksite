<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * פונקציות כלליות למערכת
 */

/**
 * הדפסת פלט עם סינון XSS
 * 
 * @param string $text טקסט להדפסה
 * @return void
 */
function e($text) {
    echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * הפנייה לכתובת מסוימת
 * 
 * @param string $url כתובת היעד
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * הוספת הודעה לסשן
 * 
 * @param string $message תוכן ההודעה
 * @param string $type סוג ההודעה - success, warning, danger, info
 * @return void
 */
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * הצגת ההודעות שמורות בסשן
 * 
 * @return string קוד HTML עם כל ההודעות
 */
function display_flash_messages() {
    if (empty($_SESSION['flash_messages'])) {
        return '';
    }

    $output = '<div class="flash-messages">';
    
    foreach ($_SESSION['flash_messages'] as $message) {
        $output .= sprintf(
            '<div class="alert alert-%s %s p-4 mb-4 rounded-lg flex items-center justify-between" role="alert">
                <div class="flex items-center">
                    <i class="%s mr-2"></i>
                    <span>%s</span>
                </div>
                <button type="button" class="close-alert text-lg" onclick="this.parentElement.remove()">
                    <i class="ri-close-line"></i>
                </button>
            </div>',
            $message['type'],
            get_alert_color_class($message['type']),
            get_alert_icon_class($message['type']),
            $message['message']
        );
    }
    
    $output .= '</div>';
    
    // ניקוי ההודעות מהסשן
    $_SESSION['flash_messages'] = [];
    
    return $output;
}

/**
 * קבלת המחלקה המתאימה לסוג ההתראה
 * 
 * @param string $type סוג ההתראה
 * @return string מחלקת CSS
 */
function get_alert_color_class($type) {
    switch ($type) {
        case 'success':
            return 'bg-green-100 text-green-800 border border-green-200';
        case 'warning':
            return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        case 'danger':
            return 'bg-red-100 text-red-800 border border-red-200';
        case 'info':
        default:
            return 'bg-blue-100 text-blue-800 border border-blue-200';
    }
}

/**
 * קבלת אייקון Remix לסוג ההתראה
 * 
 * @param string $type סוג ההתראה
 * @return string מחלקת אייקון
 */
function get_alert_icon_class($type) {
    switch ($type) {
        case 'success':
            return 'ri-check-line';
        case 'warning':
            return 'ri-alert-line';
        case 'danger':
            return 'ri-error-warning-line';
        case 'info':
        default:
            return 'ri-information-line';
    }
}

/**
 * קיצור תוכן לאורך מסוים
 * 
 * @param string $content התוכן המקורי
 * @param int $length האורך המקסימלי הרצוי
 * @param string $suffix סיומת להוסיף כשמקצרים
 * @return string התוכן המקוצר
 */
function truncate($content, $length = 100, $suffix = '...') {
    if (mb_strlen($content, 'UTF-8') <= $length) {
        return $content;
    }
    
    return mb_substr($content, 0, $length, 'UTF-8') . $suffix;
}

/**
 * המרת תאריך למבנה מקומי
 * 
 * @param string $date התאריך במבנה MySQL (Y-m-d H:i:s)
 * @param string $format פורמט התצוגה הרצוי
 * @return string התאריך בפורמט המבוקש
 */
function format_date($date, $format = 'd/m/Y H:i') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '';
    }
    
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * המרת מחרוזת לשימוש ב-URL (slug)
 * 
 * @param string $string המחרוזת המקורית
 * @return string המחרוזת לאחר המרה ל-slug
 */
function create_slug($string) {
    // הסרת תווים מיוחדים
    $string = mb_strtolower(trim($string), 'UTF-8');
    
    // מילון תווים לטיניים למקרה של עברית או תווים מיוחדים
    $char_map = [
        // עברית
        'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd', 'ה' => 'h', 'ו' => 'v', 'ז' => 'z', 
        'ח' => 'ch', 'ט' => 't', 'י' => 'y', 'כ' => 'k', 'ל' => 'l', 'מ' => 'm', 'נ' => 'n', 
        'ס' => 's', 'ע' => 'a', 'פ' => 'p', 'צ' => 'ts', 'ק' => 'k', 'ר' => 'r', 'ש' => 'sh', 'ת' => 't',
        'ך' => 'k', 'ם' => 'm', 'ן' => 'n', 'ף' => 'p', 'ץ' => 'ts',
        
        // תווים נוספים
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
        'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
        'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', 'ß' => 'ss'
    ];
    
    // החלפת תווים לפי המילון
    foreach ($char_map as $char => $replacement) {
        $string = str_replace($char, $replacement, $string);
    }
    
    // הסרת תווים שאינם אלפאנומריים והחלפתם במקף
    $string = preg_replace('/[^\p{L}\p{Nd}]+/u', '-', $string);
    
    // הסרת מקפים כפולים ומקפים בהתחלה ובסוף
    $string = trim(preg_replace('/-+/', '-', $string), '-');
    
    return $string;
}

/**
 * יצירת מספר רנדומלי לשם קובץ
 * 
 * @param int $length אורך המחרוזת הרצויה
 * @return string מחרוזת רנדומלית
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $string;
}

/**
 * בדיקה אם מחרוזת מכילה קוד HTML
 * 
 * @param string $string המחרוזת לבדיקה
 * @return bool האם המחרוזת מכילה HTML
 */
function contains_html($string) {
    return $string !== strip_tags($string);
}

/**
 * הדפסת טקסט רב שורות בצורה בטוחה
 * 
 * @param string $text הטקסט להדפסה
 * @return void
 */
function print_nl2br($text) {
    echo nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}

/**
 * קבלת גודל קובץ בפורמט אנושי
 * 
 * @param int $bytes גודל הקובץ בבייטים
 * @param int $decimals מספר הספרות אחרי הנקודה העשרונית
 * @return string הגודל בפורמט אנושי
 */
function format_file_size($bytes, $decimals = 2) {
    $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
}

/**
 * הסרת כל התגים מלבד אלו המותרים
 * 
 * @param string $html התוכן המקורי
 * @return string התוכן המסונן
 */
function sanitize_html($html) {
    $allowed_tags = '<p><br><a><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><img><table><tr><td><th><thead><tbody><span><div><hr><iframe>';
    return strip_tags($html, $allowed_tags);
}

/**
 * יצירת סשן חדש (התנתקות והתחברות מחדש)
 * 
 * @return void
 */
function regenerate_session() {
    // שמירת פרטי הסשן הנוכחי
    $old_session_data = $_SESSION;
    
    // מחיקת קובץ הסשן הישן
    session_destroy();
    
    // יצירת סשן חדש
    session_start();
    
    // שחזור פרטי הסשן
    $_SESSION = $old_session_data;
}

/**
 * פונקציה להצגת דף הנחיתה הנוכחי בתפריט דפדוף
 * 
 * @param int $current_page העמוד הנוכחי
 * @param int $total_pages מספר העמודים הכולל
 * @param string $url_pattern תבנית הכתובת לדפי הדפדוף
 * @return string קוד HTML של רכיב הדפדוף
 */
function pagination($current_page, $total_pages, $url_pattern) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $output = '<nav class="flex justify-center my-6"><ul class="inline-flex items-center -space-x-px">';
    
    // כפתור הקודם
    if ($current_page > 1) {
        $prev_url = sprintf($url_pattern, $current_page - 1);
        $output .= '<li><a href="' . $prev_url . '" class="block px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 rtl:rounded-l-none rtl:rounded-r-lg"><i class="ri-arrow-right-s-line"></i> הקודם</a></li>';
    } else {
        $output .= '<li><span class="block px-3 py-2 ml-0 leading-tight text-gray-300 bg-white border border-gray-300 rounded-l-lg cursor-not-allowed rtl:rounded-l-none rtl:rounded-r-lg"><i class="ri-arrow-right-s-line"></i> הקודם</span></li>';
    }
    
    // חישוב טווח העמודים להצגה
    $range = 2; // מספר העמודים מכל צד
    $start_page = max(1, $current_page - $range);
    $end_page = min($total_pages, $current_page + $range);
    
    // הוספת עמוד ראשון וכו' אם צריך
    if ($start_page > 1) {
        $first_url = sprintf($url_pattern, 1);
        $output .= '<li><a href="' . $first_url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">1</a></li>';
        
        if ($start_page > 2) {
            $output .= '<li><span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>';
        }
    }
    
    // דפי הדפדוף הרגילים
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $output .= '<li><span class="px-3 py-2 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700">' . $i . '</span></li>';
        } else {
            $page_url = sprintf($url_pattern, $i);
            $output .= '<li><a href="' . $page_url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">' . $i . '</a></li>';
        }
    }
    
    // הוספת עמוד אחרון וכו' אם צריך
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $output .= '<li><span class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>';
        }
        
        $last_url = sprintf($url_pattern, $total_pages);
        $output .= '<li><a href="' . $last_url . '" class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">' . $total_pages . '</a></li>';
    }
    
    // כפתור הבא
    if ($current_page < $total_pages) {
        $next_url = sprintf($url_pattern, $current_page + 1);
        $output .= '<li><a href="' . $next_url . '" class="block px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 rtl:rounded-r-none rtl:rounded-l-lg">הבא <i class="ri-arrow-left-s-line"></i></a></li>';
    } else {
        $output .= '<li><span class="block px-3 py-2 leading-tight text-gray-300 bg-white border border-gray-300 rounded-r-lg cursor-not-allowed rtl:rounded-r-none rtl:rounded-l-lg">הבא <i class="ri-arrow-left-s-line"></i></span></li>';
    }
    
    $output .= '</ul></nav>';
    
    return $output;
}

/**
 * יצירת token ייחודי עם פג תוקף
 * 
 * @param string $type סוג הטוקן
 * @param int $length אורך הטוקן
 * @param int $expiry זמן פקיעת תוקף בשעות
 * @return string מחרוזת הטוקן
 */
function generate_token($type, $length = 32, $expiry = 24) {
    $token = bin2hex(random_bytes($length / 2));
    $expiry_time = date('Y-m-d H:i:s', time() + $expiry * 3600);
    
    return $token . '|' . $expiry_time;
}

/**
 * בדיקת תקינות טוקן
 * 
 * @param string $token הטוקן לבדיקה
 * @return bool האם הטוקן תקף
 */
function validate_token($token) {
    $parts = explode('|', $token);
    
    if (count($parts) !== 2) {
        return false;
    }
    
    $expiry_time = strtotime($parts[1]);
    
    return $expiry_time > time();
}

/**
 * הסרת תחביר javascript מטקסט
 * 
 * @param string $text הטקסט לניקוי
 * @return string הטקסט לאחר ניקוי
 */
function remove_js($text) {
    // הסרת תגי script
    $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
    
    // הסרת אירועי javascript
    $text = preg_replace('/on\w+=".*?"/i', '', $text);
    $text = preg_replace('/on\w+=\'.*?\'/i', '', $text);
    
    // הסרת javascript: מכתובות
    $text = preg_replace('/href\s*=\s*["\']?\s*javascript:.*?["\']?/i', 'href="#"', $text);
    
    return $text;
}

/**
 * יצירת לינק לדף הנחיתה
 * 
 * @param string $slug ה-slug של דף הנחיתה
 * @param int $custom_domain_id מזהה דומיין מותאם אישית (אם קיים)
 * @return string כתובת URL מלאה לדף הנחיתה
 */
function get_landing_page_url($slug, $custom_domain_id = null) {
    global $pdo;
    
    if ($custom_domain_id) {
        try {
            $stmt = $pdo->prepare("SELECT domain FROM custom_domains WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$custom_domain_id]);
            $domain = $stmt->fetchColumn();
            
            if ($domain) {
                return 'https://' . $domain . '/' . $slug;
            }
        } catch (PDOException $e) {
            // במקרה של שגיאה, חזור לכתובת הרגילה
        }
    }
    
    return SITE_URL . '/view/?slug=' . $slug;
}

/**
 * יצירת ספריות העלאה למערכת אם לא קיימות
 * 
 * @return void
 */
function create_upload_directories() {
    $directories = [
        UPLOAD_DIR,
        UPLOAD_DIR . '/images',
        UPLOAD_DIR . '/files',
        UPLOAD_DIR . '/temp'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            
            // יצירת קובץ index.html ריק למניעת גישה ישירה
            file_put_contents($dir . '/index.html', '');
        }
    }
}

/**
 * בדיקה האם יש למשתמש הרשאה לפעולה מסוימת על בסיס מנוי
 * 
 * @param string $feature התכונה לבדיקה
 * @param array $user_data פרטי המשתמש
 * @return bool האם יש הרשאה
 */
function has_permission($feature, $user_data = null) {
    global $pdo, $current_user;
    
    if (!$user_data) {
        $user_data = $current_user;
    }
    
    if (!$user_data) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM plans p 
            JOIN subscriptions s ON p.id = s.plan_id 
            WHERE s.user_id = ? AND s.status = 'active' 
            ORDER BY s.end_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_data['id']]);
        $plan = $stmt->fetch();
        
        if (!$plan) {
            return false;
        }
        
        // בדיקת התכונה הרצויה
        switch ($feature) {
            case 'custom_domain':
                return (bool)$plan['custom_domain'];
            case 'ab_testing':
                return $plan['ab_testing_limit'] > 0;
            case 'advanced_analytics':
                return (bool)$plan['advanced_analytics'];
            case 'api_access':
                return (bool)$plan['api_access'];
            case 'css_js_customization':
                return (bool)$plan['css_js_customization'];
            default:
                return false;
        }
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת הרשאות: " . $e->getMessage());
        return false;
    }
}

/**
 * בדיקה האם המשתמש הגיע למגבלת השימוש לפי המנוי
 * 
 * @param string $limit_type סוג המגבלה (landing_pages, contacts, messages, views)
 * @param array $user_data פרטי המשתמש
 * @return bool האם הגיע למגבלה
 */
function has_reached_limit($limit_type, $user_data = null) {
    global $pdo, $current_user;
    
    if (!$user_data) {
        $user_data = $current_user;
    }
    
    if (!$user_data) {
        return true;
    }
    
    try {
        // קבלת פרטי המנוי הפעיל של המשתמש
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM plans p 
            JOIN subscriptions s ON p.id = s.plan_id 
            WHERE s.user_id = ? AND s.status = 'active' 
            ORDER BY s.end_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_data['id']]);
        $plan = $stmt->fetch();
        
        if (!$plan) {
            return true; // אם אין מנוי פעיל, הגיע למגבלה
        }
        
        // בדיקה אם המגבלה היא ללא הגבלה
        $limit_field = $limit_type . '_limit';
        if ($plan[$limit_field] === 0) {
            return false; // ללא הגבלה
        }
        
        // ספירת השימוש הנוכחי לפי סוג המגבלה
        switch ($limit_type) {
            case 'landing_pages':
                $count_query = "SELECT COUNT(*) FROM landing_pages WHERE user_id = ? AND status != 'archived'";
                break;
            case 'contacts':
                $count_query = "SELECT COUNT(*) FROM contacts WHERE user_id = ? AND status = 'active'";
                break;
            case 'views':
                // חישוב צפיות בחודש הנוכחי
                $current_month = date('m');
                $current_year = date('Y');
                $count_query = "
                    SELECT COUNT(*) FROM page_visits pv 
                    JOIN landing_pages lp ON pv.landing_page_id = lp.id 
                    WHERE lp.user_id = ? AND MONTH(pv.created_at) = ? AND YEAR(pv.created_at) = ?
                ";
                $stmt = $pdo->prepare($count_query);
                $stmt->execute([$user_data['id'], $current_month, $current_year]);
                break;
            case 'messages':
                // חישוב הודעות שנשלחו בחודש הנוכחי
                $current_month = date('m');
                $current_year = date('Y');
                $count_query = "
                    SELECT COUNT(*) FROM sent_messages sm 
                    JOIN campaigns c ON sm.campaign_id = c.id 
                    WHERE c.user_id = ? AND MONTH(sm.sent_at) = ? AND YEAR(sm.sent_at) = ?
                ";
                $stmt = $pdo->prepare($count_query);
                $stmt->execute([$user_data['id'], $current_month, $current_year]);
                break;
            default:
                return false;
        }
        
        // בדיקת מגבלות views ו-messages (כבר בוצעה למעלה)
        if ($limit_type === 'views' || $limit_type === 'messages') {
            $current_count = $stmt->fetchColumn();
        } else {
            // ביצוע ספירה לשאר סוגי המגבלות
            $stmt = $pdo->prepare($count_query);
            $stmt->execute([$user_data['id']]);
            $current_count = $stmt->fetchColumn();
        }
        
        // השוואה למגבלה
        return $current_count >= $plan[$limit_field];
        
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת מגבלות: " . $e->getMessage());
        return true; // במקרה של שגיאה, נחשב למוגבל מטעמי בטיחות
    }
}

/**
 * בודק אם מחרוזת היא JSON תקין
 * 
 * @param mixed $string המחרוזת לבדיקה
 * @return bool האם המחרוזת היא JSON תקין
 */
function isJson($string) {
    if (!is_string($string)) return false;
    json_decode($string);
    return (json_last_error() === JSON_ERROR_NONE);
}
<?php
// קבוע לזיהוי גישה ישירה לקבצים
if (!defined('QUICKSITE')) {
    define('QUICKSITE', true);
}

// טעינת קבצי הגדרות ותצורה
require_once '../../config/init.php';

// בדיקה אם זו בקשת AJAX
if (!is_ajax_request()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'גישה לא מורשית']);
    exit;
}

// בדיקה שהמשתמש מחובר
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'יש להתחבר כדי לבצע פעולה זו']);
    exit;
}

// קבלת פרמטרים
$days = isset($_GET['days']) ? intval($_GET['days']) : 7;
$days = max(7, min(90, $days)); // הגבלת הימים ל-7 עד 90

// קבלת נתוני ביצועים
$performance_data = get_performance_data($current_user['id'], $days);

// החזרת נתונים כJSON
header('Content-Type: application/json');
echo json_encode($performance_data);
exit;

/**
 * קבלת נתוני ביצועים עבור גרף
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $days מספר ימים אחורה
 * @return array נתוני ביצועים מעובדים [labels, views, conversions]
 */
function get_performance_data($user_id, $days = 7) {
    global $pdo;
    
    $result = [
        'labels' => [],
        'views' => [],
        'conversions' => []
    ];
    
    try {
        // יצירת מערך תאריכים לתצוגה
        $date_format = 'd/m';
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date($date_format, strtotime("-$i days"));
            $result['labels'][] = $date;
            $result['views'][] = 0;
            $result['conversions'][] = 0;
        }
        
        // קבלת נתוני צפיות
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(pv.created_at, '%d/%m') AS date_label, COUNT(*) AS count
            FROM page_visits pv
            JOIN landing_pages lp ON pv.landing_page_id = lp.id
            WHERE lp.user_id = ? AND pv.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE_FORMAT(pv.created_at, '%d/%m')
            ORDER BY pv.created_at
        ");
        $stmt->execute([$user_id, $days]);
        $views_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // מילוי נתוני צפיות
        foreach ($views_data as $row) {
            $index = array_search($row['date_label'], $result['labels']);
            if ($index !== false) {
                $result['views'][$index] = (int)$row['count'];
            }
        }
        
        // קבלת נתוני המרות
        $stmt = $pdo->prepare("
            SELECT DATE_FORMAT(fs.created_at, '%d/%m') AS date_label, COUNT(*) AS count
            FROM form_submissions fs
            JOIN landing_pages lp ON fs.landing_page_id = lp.id
            WHERE lp.user_id = ? AND fs.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE_FORMAT(fs.created_at, '%d/%m')
            ORDER BY fs.created_at
        ");
        $stmt->execute([$user_id, $days]);
        $conversions_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // מילוי נתוני המרות
        foreach ($conversions_data as $row) {
            $index = array_search($row['date_label'], $result['labels']);
            if ($index !== false) {
                $result['conversions'][$index] = (int)$row['count'];
            }
        }
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת נתוני ביצועים: " . $e->getMessage());
        return $result;
    }
}
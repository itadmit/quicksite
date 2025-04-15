<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * קבלת כל התבניות לפי סוג
 * 
 * @param string $type סוג התבנית (landing_page/email/sms/whatsapp)
 * @param int $user_id מזהה המשתמש (לסינון תבניות לפי מנוי)
 * @param int $page מספר העמוד
 * @param int $limit מספר פריטים בעמוד
 * @param int $category_id מזהה קטגוריה (אופציונלי)
 * @return array רשימת התבניות
 */
function get_templates($type, $user_id = null, $page = 1, $limit = TEMPLATES_PER_PAGE, $category_id = null) {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    $params = [$type];
    
    $sql_suffix = "";
    
    // אם סופק מזהה משתמש, סנן תבניות לפי רמת המנוי
    if ($user_id) {
        $sql_suffix .= " AND (t.plan_level = 'all'";
        
        // קבלת רמת המנוי של המשתמש
        $stmt = $pdo->prepare("
            SELECT p.name as plan_level 
            FROM subscriptions s 
            JOIN plans p ON s.plan_id = p.id 
            WHERE s.user_id = ? AND s.status = 'active' 
            ORDER BY s.id DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            $plan_level = strtolower($subscription['plan_level']);
            
            if ($plan_level == 'פרו' || $plan_level == 'pro') {
                $sql_suffix .= " OR t.plan_level = 'popular' OR t.plan_level = 'pro'";
            } elseif ($plan_level == 'פופולרי' || $plan_level == 'popular') {
                $sql_suffix .= " OR t.plan_level = 'popular'";
            }
        }
        
        $sql_suffix .= ")";
    }
    
    // אם סופק מזהה קטגוריה, סנן לפי קטגוריה
    if ($category_id) {
        $sql_suffix .= " AND t.category_id = ?";
        $params[] = $category_id;
    }
    
    try {
        // שליפת התבניות
        $sql = "
            SELECT t.*, tc.name as category_name 
            FROM templates t 
            JOIN template_categories tc ON t.category_id = tc.id 
            WHERE t.type = ? $sql_suffix
            ORDER BY t.is_premium DESC, t.name 
            LIMIT ?, ?
        ";
        
        $params[] = $offset;
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $templates = $stmt->fetchAll();
        
        // ספירת סך כל התבניות
        $count_sql = "
            SELECT COUNT(*) 
            FROM templates t 
            WHERE t.type = ? $sql_suffix
        ";
        
        $count_params = [$type];
        if ($category_id) {
            $count_params[] = $category_id;
        }
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($count_params);
        $total = $count_stmt->fetchColumn();
        
        return [
            'templates' => $templates,
            'total' => $total,
            'pages_count' => ceil($total / $limit),
            'current_page' => $page
        ];
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת תבניות: " . $e->getMessage());
        return [
            'templates' => [],
            'total' => 0,
            'pages_count' => 0,
            'current_page' => 1
        ];
    }
}

/**
 * קבלת תבנית לפי מזהה
 * 
 * @param int $template_id מזהה התבנית
 * @param int $user_id מזהה המשתמש (לבדיקת הרשאות)
 * @return array|false פרטי התבנית או false אם לא נמצאה
 */
function get_template($template_id, $user_id = null) {
    global $pdo;
    
    try {
        // שליפת פרטי התבנית
        $sql = "
            SELECT t.*, tc.name as category_name 
            FROM templates t 
            JOIN template_categories tc ON t.category_id = tc.id 
            WHERE t.id = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$template_id]);
        $template = $stmt->fetch();
        
        if (!$template) {
            return false;
        }
        
        // אם סופק מזהה משתמש, בדוק הרשאות גישה
        if ($user_id) {
            // בדיקה אם התבנית מוגבלת לרמת מנוי
            if ($template['plan_level'] != 'all') {
                // קבלת רמת המנוי של המשתמש
                $stmt = $pdo->prepare("
                    SELECT p.name as plan_level 
                    FROM subscriptions s 
                    JOIN plans p ON s.plan_id = p.id 
                    WHERE s.user_id = ? AND s.status = 'active' 
                    ORDER BY s.id DESC 
                    LIMIT 1
                ");
                $stmt->execute([$user_id]);
                $subscription = $stmt->fetch();
                
                if (!$subscription) {
                    return ['error' => 'אין לך מנוי פעיל הנדרש לגישה לתבנית זו'];
                }
                
                $plan_level = strtolower($subscription['plan_level']);
                
                // בדיקה אם רמת המנוי מתאימה
                if ($template['plan_level'] == 'pro' && $plan_level != 'pro' && $plan_level != 'אולטרה') {
                    return ['error' => 'תבנית זו זמינה רק במנוי פרו ומעלה'];
                } elseif ($template['plan_level'] == 'popular' && $plan_level == 'לייט') {
                    return ['error' => 'תבנית זו זמינה רק במנוי פופולרי ומעלה'];
                }
            }
        }
        
        return $template;
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת תבנית: " . $e->getMessage());
        return false;
    }
}

/**
 * קבלת כל קטגוריות התבניות
 * 
 * @return array רשימת הקטגוריות
 */
function get_template_categories() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT tc.*, 
                  (SELECT COUNT(*) FROM templates WHERE category_id = tc.id) as templates_count
            FROM template_categories tc
            ORDER BY tc.name
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת קטגוריות תבניות: " . $e->getMessage());
        return [];
    }
}

/**
 * יצירת תבנית הודעה חדשה
 * 
 * @param int $user_id מזהה המשתמש
 * @param array $template_data נתוני התבנית
 * @return int|array מזהה התבנית החדשה או מערך עם שגיאה
 */
function create_message_template($user_id, $template_data) {
    global $pdo;
    
    // ודא שכל השדות הנדרשים קיימים
    $required_fields = ['name', 'type', 'content'];
    foreach ($required_fields as $field) {
        if (!isset($template_data[$field]) || empty($template_data[$field])) {
            return ['error' => "השדה $field הוא שדה חובה"];
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO message_templates 
            (user_id, name, type, subject, content) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $template_data['name'],
            $template_data['type'],
            $template_data['subject'] ?? '',
            $template_data['content']
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת תבנית הודעה: " . $e->getMessage());
        return ['error' => 'שגיאה ביצירת תבנית הודעה. אנא נסה שוב.'];
    }
}

/**
 * קבלת תבניות הודעה של המשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @param string $type סוג התבנית (email/sms/whatsapp)
 * @return array רשימת תבניות ההודעה
 */
function get_user_message_templates($user_id, $type = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT * FROM message_templates 
            WHERE user_id = ?
        ";
        
        $params = [$user_id];
        
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY updated_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת תבניות הודעה: " . $e->getMessage());
        return [];
    }
}

/**
 * קבלת תבנית הודעה לפי מזהה
 * 
 * @param int $template_id מזהה התבנית
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @return array|false פרטי התבנית או false אם לא נמצאה
 */
function get_message_template($template_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM message_templates 
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$template_id, $user_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת תבנית הודעה: " . $e->getMessage());
        return false;
    }
}

/**
 * עדכון תבנית הודעה קיימת
 * 
 * @param int $template_id מזהה התבנית
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @param array $template_data נתוני העדכון
 * @return bool|array האם העדכון הצליח או מערך עם שגיאה
 */
function update_message_template($template_id, $user_id, $template_data) {
    global $pdo;
    
    try {
        // בדיקה שהתבנית שייכת למשתמש
        $check_stmt = $pdo->prepare("
            SELECT id FROM message_templates 
            WHERE id = ? AND user_id = ?
        ");
        $check_stmt->execute([$template_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            return ['error' => 'התבנית לא נמצאה או שאין לך הרשאות לערוך אותה'];
        }
        
        // עדכון פרטי התבנית
        $fields = [];
        $params = [];
        
        $allowed_fields = ['name', 'subject', 'content'];
        
        foreach ($allowed_fields as $field) {
            if (isset($template_data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $template_data[$field];
            }
        }
        
        if (!empty($fields)) {
            $sql = "UPDATE message_templates SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND user_id = ?";
            $params[] = $template_id;
            $params[] = $user_id;
            
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute($params);
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("שגיאה בעדכון תבנית הודעה: " . $e->getMessage());
        return ['error' => 'שגיאה בעדכון תבנית הודעה. אנא נסה שוב.'];
    }
}

/**
 * מחיקת תבנית הודעה
 * 
 * @param int $template_id מזהה התבנית
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @return bool|array האם המחיקה הצליחה או מערך עם שגיאה
 */
function delete_message_template($template_id, $user_id) {
    global $pdo;
    
    try {
        // בדיקה שהתבנית שייכת למשתמש
        $check_stmt = $pdo->prepare("
            SELECT id FROM message_templates 
            WHERE id = ? AND user_id = ?
        ");
        $check_stmt->execute([$template_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            return ['error' => 'התבנית לא נמצאה או שאין לך הרשאות למחוק אותה'];
        }
        
        // בדיקה אם התבנית בשימוש בקמפיינים
        $check_campaigns = $pdo->prepare("
            SELECT id FROM campaigns 
            WHERE template_id = ? AND status IN ('draft', 'scheduled', 'sending')
        ");
        $check_campaigns->execute([$template_id]);
        
        if ($check_campaigns->fetch()) {
            return ['error' => 'לא ניתן למחוק תבנית זו כיוון שהיא בשימוש בקמפיינים פעילים'];
        }
        
        // מחיקת התבנית
        $delete_stmt = $pdo->prepare("
            DELETE FROM message_templates 
            WHERE id = ? AND user_id = ?
        ");
        $delete_stmt->execute([$template_id, $user_id]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("שגיאה במחיקת תבנית הודעה: " . $e->getMessage());
        return ['error' => 'שגיאה במחיקת תבנית הודעה. אנא נסה שוב.'];
    }
}

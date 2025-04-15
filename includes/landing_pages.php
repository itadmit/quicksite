<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * קבלת כל דפי הנחיתה של המשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $page מספר העמוד
 * @param int $limit מספר פריטים בעמוד
 * @return array רשימת דפי הנחיתה
 */
function get_user_landing_pages($user_id, $page = 1, $limit = LANDING_PAGES_PER_PAGE) {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    
    try {
        // שליפת דפי הנחיתה
        $stmt = $pdo->prepare("
            SELECT lp.*, 
                   (SELECT COUNT(*) FROM page_visits WHERE landing_page_id = lp.id) AS views,
                   (SELECT COUNT(*) FROM form_submissions WHERE landing_page_id = lp.id) AS conversions
            FROM landing_pages lp 
            WHERE lp.user_id = ? 
            ORDER BY lp.updated_at DESC 
            LIMIT ?, ?
        ");
        $stmt->execute([$user_id, $offset, $limit]);
        $pages = $stmt->fetchAll();
        
        // ספירת סך כל דפי הנחיתה של המשתמש
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $total = $count_stmt->fetchColumn();
        
        return [
            'pages' => $pages,
            'total' => $total,
            'pages_count' => ceil($total / $limit),
            'current_page' => $page
        ];
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת דפי נחיתה: " . $e->getMessage());
        return [
            'pages' => [],
            'total' => 0,
            'pages_count' => 0,
            'current_page' => 1
        ];
    }
}

/**
 * קבלת דף נחיתה לפי מזהה
 * 
 * @param int $page_id מזהה הדף
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @return array|false פרטי דף הנחיתה או false אם לא נמצא
 */
function get_landing_page($page_id, $user_id = null) {
    global $pdo;
    
    try {
        $sql = "
            SELECT lp.*, 
                  lpc.content, lpc.css, lpc.js,
                  cd.domain as custom_domain
            FROM landing_pages lp 
            LEFT JOIN landing_page_contents lpc ON lp.id = lpc.landing_page_id AND lpc.is_current = 1
            LEFT JOIN custom_domains cd ON lp.custom_domain_id = cd.id
            WHERE lp.id = ?
        ";
        
        $params = [$page_id];
        
        // אם מזהה משתמש סופק, בדוק בעלות
        if ($user_id !== null) {
            $sql .= " AND lp.user_id = ?";
            $params[] = $user_id;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת דף נחיתה: " . $e->getMessage());
        return false;
    }
}

/**
 * יצירת דף נחיתה חדש
 * 
 * @param int $user_id מזהה המשתמש
 * @param array $page_data נתוני הדף
 * @return int|false מזהה הדף החדש או false במקרה של שגיאה
 */
function create_landing_page($user_id, $page_data) {
    global $pdo;
    
    // בדיקה שלמשתמש יש מנוי פעיל
    if (!has_active_subscription($user_id)) {
        return ['error' => 'נדרש מנוי פעיל ליצירת דף נחיתה'];
    }
    
    // בדיקה שהמשתמש לא חרג ממגבלת דפי הנחיתה במנוי שלו
    $subscription = get_active_subscription($user_id);
    $plan_stmt = $pdo->prepare("SELECT landing_pages_limit FROM plans WHERE id = ?");
    $plan_stmt->execute([$subscription['plan_id']]);
    $plan = $plan_stmt->fetch();
    
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM landing_pages 
        WHERE user_id = ? AND status != 'archived'
    ");
    $count_stmt->execute([$user_id]);
    $current_pages_count = $count_stmt->fetchColumn();
    
    if ($current_pages_count >= $plan['landing_pages_limit']) {
        return ['error' => 'הגעת למגבלת דפי הנחיתה במנוי שלך. שדרג את המנוי כדי ליצור דפים נוספים.'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // יצירת רשומת דף נחיתה
        $stmt = $pdo->prepare("
            INSERT INTO landing_pages 
            (user_id, title, slug, description, template_id, seo_title, seo_description, seo_keywords, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft')
        ");
        
        $stmt->execute([
            $user_id,
            $page_data['title'],
            $page_data['slug'],
            $page_data['description'] ?? '',
            $page_data['template_id'] ?? null,
            $page_data['seo_title'] ?? $page_data['title'],
            $page_data['seo_description'] ?? '',
            $page_data['seo_keywords'] ?? '',
        ]);
        
        $page_id = $pdo->lastInsertId();
        
        // יצירת תוכן ראשוני לדף
        $content_stmt = $pdo->prepare("
            INSERT INTO landing_page_contents 
            (landing_page_id, content, css, js, version, is_current) 
            VALUES (?, ?, ?, ?, 1, 1)
        ");
        
        $content = $page_data['content'] ?? '<div class="container"><h1>דף נחיתה חדש</h1></div>';
        $css = $page_data['css'] ?? '';
        $js = $page_data['js'] ?? '';
        
        $content_stmt->execute([$page_id, $content, $css, $js]);
        
        $pdo->commit();
        return $page_id;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("שגיאה ביצירת דף נחיתה: " . $e->getMessage());
        return ['error' => 'שגיאה ביצירת דף נחיתה. אנא נסה שוב.'];
    }
}

/**
 * עדכון דף נחיתה קיים
 * 
 * @param int $page_id מזהה הדף
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @param array $page_data נתוני העדכון
 * @return bool האם העדכון הצליח
 */
function update_landing_page($page_id, $user_id, $page_data) {
    global $pdo;
    
    try {
        // בדיקה שהדף שייך למשתמש
        $check_stmt = $pdo->prepare("SELECT id FROM landing_pages WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$page_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            return ['error' => 'דף הנחיתה לא נמצא או שאין לך הרשאות לערוך אותו'];
        }
        
        $pdo->beginTransaction();
        
        // עדכון פרטי דף הנחיתה
        $fields = [];
        $params = [];
        
        $allowed_fields = ['title', 'slug', 'description', 'status', 'seo_title', 'seo_description', 'seo_keywords', 'custom_domain_id'];
        
        foreach ($allowed_fields as $field) {
            if (isset($page_data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $page_data[$field];
            }
        }
        
        if (!empty($fields)) {
            $sql = "UPDATE landing_pages SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND user_id = ?";
            $params[] = $page_id;
            $params[] = $user_id;
            
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute($params);
        }
        
        // עדכון תוכן דף הנחיתה אם סופק
        if (isset($page_data['content']) || isset($page_data['css']) || isset($page_data['js'])) {
            // בדיקה אם יש צורך בגרסה חדשה או עדכון הקיימת
            $create_new_version = isset($page_data['create_new_version']) && $page_data['create_new_version'];
            
            if ($create_new_version) {
                // קבלת הגרסה הנוכחית
                $version_stmt = $pdo->prepare("
                    SELECT MAX(version) FROM landing_page_contents 
                    WHERE landing_page_id = ?
                ");
                $version_stmt->execute([$page_id]);
                $current_version = $version_stmt->fetchColumn();
                
                // סימון כל הגרסאות כלא נוכחיות
                $update_versions = $pdo->prepare("
                    UPDATE landing_page_contents 
                    SET is_current = 0 
                    WHERE landing_page_id = ?
                ");
                $update_versions->execute([$page_id]);
                
                // יצירת גרסה חדשה
                $new_version = $current_version + 1;
                $insert_stmt = $pdo->prepare("
                    INSERT INTO landing_page_contents 
                    (landing_page_id, content, css, js, version, is_current) 
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                
                $insert_stmt->execute([
                    $page_id,
                    $page_data['content'] ?? '',
                    $page_data['css'] ?? '',
                    $page_data['js'] ?? '',
                    $new_version
                ]);
            } else {
                // עדכון הגרסה הנוכחית
                $fields = [];
                $params = [];
                
                if (isset($page_data['content'])) {
                    $fields[] = "content = ?";
                    $params[] = $page_data['content'];
                }
                
                if (isset($page_data['css'])) {
                    $fields[] = "css = ?";
                    $params[] = $page_data['css'];
                }
                
                if (isset($page_data['js'])) {
                    $fields[] = "js = ?";
                    $params[] = $page_data['js'];
                }
                
                $fields[] = "updated_at = NOW()";
                
                $sql = "UPDATE landing_page_contents SET " . implode(', ', $fields) . " WHERE landing_page_id = ? AND is_current = 1";
                $params[] = $page_id;
                
                $update_stmt = $pdo->prepare($sql);
                $update_stmt->execute($params);
            }
        }
        
        // אם סטטוס הדף הוא 'published', עדכן את זמן הפרסום אם זה הפרסום הראשון
        if (isset($page_data['status']) && $page_data['status'] === 'published') {
            $publish_stmt = $pdo->prepare("
                UPDATE landing_pages 
                SET published_at = CASE WHEN published_at IS NULL THEN NOW() ELSE published_at END 
                WHERE id = ?
            ");
            $publish_stmt->execute([$page_id]);
        }
        
        $pdo->commit();
        return true;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("שגיאה בעדכון דף נחיתה: " . $e->getMessage());
        return ['error' => 'שגיאה בעדכון דף נחיתה. אנא נסה שוב.'];
    }
}

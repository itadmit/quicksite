<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * קבלת אנשי הקשר של המשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $page מספר העמוד
 * @param int $limit מספר פריטים בעמוד
 * @param string $search מחרוזת חיפוש (אופציונלי)
 * @return array רשימת אנשי הקשר
 */
function get_user_contacts($user_id, $page = 1, $limit = CONTACTS_PER_PAGE, $search = '') {
    global $pdo;
    
    $offset = ($page - 1) * $limit;
    $params = [$user_id];
    
    $sql = "
        SELECT c.* 
        FROM contacts c 
        WHERE c.user_id = ?
    ";
    
    $count_sql = "
        SELECT COUNT(*) 
        FROM contacts c 
        WHERE c.user_id = ?
    ";
    
    // הוספת תנאי חיפוש אם יש
    if (!empty($search)) {
        $sql .= " AND (c.email LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.phone LIKE ?)";
        $count_sql .= " AND (c.email LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.phone LIKE ?)";
        
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $sql .= " ORDER BY c.created_at DESC LIMIT ?, ?";
    $query_params = $params;
    $query_params[] = $offset;
    $query_params[] = $limit;
    
    try {
        // שליפת אנשי הקשר
        $stmt = $pdo->prepare($sql);
        $stmt->execute($query_params);
        $contacts = $stmt->fetchAll();
        
        // ספירת סך הכל
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total = $count_stmt->fetchColumn();
        
        return [
            'contacts' => $contacts,
            'total' => $total,
            'pages_count' => ceil($total / $limit),
            'current_page' => $page
        ];
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת אנשי קשר: " . $e->getMessage());
        return [
            'contacts' => [],
            'total' => 0,
            'pages_count' => 0,
            'current_page' => 1
        ];
    }
}

/**
 * קבלת איש קשר לפי מזהה
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $user_id מזהה המשתמש (לאימות בעלות)
 * @return array|false פרטי איש הקשר או false אם לא נמצא
 */
function get_contact($contact_id, $user_id) {
    global $pdo;
    
    try {
        // שליפת פרטי איש הקשר הבסיסיים
        $stmt = $pdo->prepare("
            SELECT * FROM contacts 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$contact_id, $user_id]);
        $contact = $stmt->fetch();
        
        if (!$contact) {
            return false;
        }
        
        // שליפת שדות מותאמים אישית
        $custom_fields_stmt = $pdo->prepare("
            SELECT cf.field_name, cf.field_type, cfv.value
            FROM contact_custom_field_values cfv
            JOIN contact_custom_fields cf ON cfv.field_id = cf.id
            WHERE cfv.contact_id = ?
        ");
        $custom_fields_stmt->execute([$contact_id]);
        $custom_fields = $custom_fields_stmt->fetchAll();
        
        $contact['custom_fields'] = [];
        foreach ($custom_fields as $field) {
            $contact['custom_fields'][$field['field_name']] = $field['value'];
        }
        
        // שליפת רשימות תפוצה של איש הקשר
        $lists_stmt = $pdo->prepare("
            SELECT cl.id, cl.name 
            FROM contact_lists cl
            JOIN list_contacts lc ON cl.id = lc.list_id
            WHERE lc.contact_id = ? AND cl.user_id = ?
        ");
        $lists_stmt->execute([$contact_id, $user_id]);
        $contact['lists'] = $lists_stmt->fetchAll();
        
        return $contact;
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת איש קשר: " . $e->getMessage());
        return false;
    }
}

/**
 * הוספת איש קשר חדש
 * 
 * @param int $user_id מזהה המשתמש
 * @param array $contact_data נתוני איש הקשר
 * @return int|array מזהה איש הקשר החדש או מערך עם שגיאה
 */
function add_contact($user_id, $contact_data) {
    global $pdo;
    
    // בדיקה שלמשתמש יש מנוי פעיל
    if (!has_active_subscription($user_id)) {
        return ['error' => 'נדרש מנוי פעיל להוספת אנשי קשר'];
    }
    
    // בדיקה שהמשתמש לא חרג ממגבלת אנשי הקשר במנוי שלו
    $subscription = get_active_subscription($user_id);
    $plan_stmt = $pdo->prepare("SELECT contacts_limit FROM plans WHERE id = ?");
    $plan_stmt->execute([$subscription['plan_id']]);
    $plan = $plan_stmt->fetch();
    
    // אם מגבלת אנשי הקשר אינה 0 (ללא הגבלה), בדוק את המספר הנוכחי
    if ($plan['contacts_limit'] > 0) {
        $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $current_contacts_count = $count_stmt->fetchColumn();
        
        if ($current_contacts_count >= $plan['contacts_limit']) {
            return ['error' => 'הגעת למגבלת אנשי הקשר במנוי שלך. שדרג את המנוי כדי להוסיף אנשי קשר נוספים.'];
        }
    }
    
    try {
        $pdo->beginTransaction();
        
        // בדיקה אם כתובת האימייל כבר קיימת למשתמש זה
        if (!empty($contact_data['email'])) {
            $check_stmt = $pdo->prepare("
                SELECT id FROM contacts 
                WHERE user_id = ? AND email = ?
            ");
            $check_stmt->execute([$user_id, $contact_data['email']]);
            
            if ($check_stmt->fetch()) {
                return ['error' => 'כתובת האימייל כבר קיימת ברשימת אנשי הקשר שלך'];
            }
        }
        
        // הוספת איש הקשר
        $stmt = $pdo->prepare("
            INSERT INTO contacts 
            (user_id, email, phone, first_name, last_name, whatsapp, source) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $user_id,
            $contact_data['email'] ?? '',
            $contact_data['phone'] ?? '',
            $contact_data['first_name'] ?? '',
            $contact_data['last_name'] ?? '',
            $contact_data['whatsapp'] ?? '',
            $contact_data['source'] ?? 'manual'
        ]);
        
        $contact_id = $pdo->lastInsertId();
        
        // הוספה לרשימות תפוצה אם צוינו
        if (isset($contact_data['lists']) && is_array($contact_data['lists'])) {
            foreach ($contact_data['lists'] as $list_id) {
                // ודא שהרשימה שייכת למשתמש
                $check_list = $pdo->prepare("SELECT id FROM contact_lists WHERE id = ? AND user_id = ?");
                $check_list->execute([$list_id, $user_id]);
                
                if ($check_list->fetch()) {
                    $insert_list = $pdo->prepare("
                        INSERT INTO list_contacts (list_id, contact_id) 
                        VALUES (?, ?)
                    ");
                    $insert_list->execute([$list_id, $contact_id]);
                }
            }
        }
        
        // הוספת שדות מותאמים אישית אם יש
        if (isset($contact_data['custom_fields']) && is_array($contact_data['custom_fields'])) {
            foreach ($contact_data['custom_fields'] as $field_id => $value) {
                // ודא שהשדה שייך למשתמש
                $check_field = $pdo->prepare("SELECT id FROM contact_custom_fields WHERE id = ? AND user_id = ?");
                $check_field->execute([$field_id, $user_id]);
                
                if ($check_field->fetch()) {
                    $insert_field = $pdo->prepare("
                        INSERT INTO contact_custom_field_values (contact_id, field_id, value) 
                        VALUES (?, ?, ?)
                    ");
                    $insert_field->execute([$contact_id, $field_id, $value]);
                }
            }
        }
        
        $pdo->commit();
        return $contact_id;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("שגיאה בהוספת איש קשר: " . $e->getMessage());
        return ['error' => 'שגיאה בהוספת איש קשר. אנא נסה שוב.'];
    }
}

/**
 * עדכון איש קשר קיים
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $user_id מזהה המשתמש
 * @param array $contact_data נתוני העדכון
 * @return bool|array האם העדכון הצליח או מערך עם שגיאה
 */
function update_contact($contact_id, $user_id, $contact_data) {
    global $pdo;
    
    try {
        // בדיקה שאיש הקשר שייך למשתמש
        $check_stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE id = ? AND user_id = ?
        ");
        $check_stmt->execute([$contact_id, $user_id]);
        
        if (!$check_stmt->fetch()) {
            return ['error' => 'איש הקשר לא נמצא או שאין לך הרשאות לערוך אותו'];
        }
        
        $pdo->beginTransaction();
        
        // בדיקה אם כתובת האימייל כבר קיימת למשתמש זה (ולא שייכת לאיש קשר זה)
        if (!empty($contact_data['email'])) {
            $check_email_stmt = $pdo->prepare("
                SELECT id FROM contacts 
                WHERE user_id = ? AND email = ? AND id != ?
            ");
            $check_email_stmt->execute([$user_id, $contact_data['email'], $contact_id]);
            
            if ($check_email_stmt->fetch()) {
                return ['error' => 'כתובת האימייל כבר קיימת ברשימת אנשי הקשר שלך'];
            }
        }
        
        // עדכון פרטי איש הקשר
        $fields = [];
        $params = [];
        
        $allowed_fields = ['email', 'phone', 'first_name', 'last_name', 'whatsapp', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($contact_data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $contact_data[$field];
            }
        }
        
        if (!empty($fields)) {
            $sql = "UPDATE contacts SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ? AND user_id = ?";
            $params[] = $contact_id;
            $params[] = $user_id;
            
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute($params);
        }
        
        // עדכון רשימות תפוצה אם צוינו
        if (isset($contact_data['lists']) && is_array($contact_data['lists'])) {
            // מחיקת כל הרשימות הקיימות
            $delete_lists = $pdo->prepare("DELETE FROM list_contacts WHERE contact_id = ?");
            $delete_lists->execute([$contact_id]);
            
            // הוספה לרשימות חדשות
            foreach ($contact_data['lists'] as $list_id) {
                // ודא שהרשימה שייכת למשתמש
                $check_list = $pdo->prepare("SELECT id FROM contact_lists WHERE id = ? AND user_id = ?");
                $check_list->execute([$list_id, $user_id]);
                
                if ($check_list->fetch()) {
                    $insert_list = $pdo->prepare("
                        INSERT INTO list_contacts (list_id, contact_id) 
                        VALUES (?, ?)
                    ");
                    $insert_list->execute([$list_id, $contact_id]);
                }
            }
        }
        
        // עדכון שדות מותאמים אישית אם יש
        if (isset($contact_data['custom_fields']) && is_array($contact_data['custom_fields'])) {
            foreach ($contact_data['custom_fields'] as $field_id => $value) {
                // ודא שהשדה שייך למשתמש
                $check_field = $pdo->prepare("SELECT id FROM contact_custom_fields WHERE id = ? AND user_id = ?");
                $check_field->execute([$field_id, $user_id]);
                
                if ($check_field->fetch()) {
                    // בדיקה אם ערך כבר קיים
                    $check_value = $pdo->prepare("
                        SELECT id FROM contact_custom_field_values 
                        WHERE contact_id = ? AND field_id = ?
                    ");
                    $check_value->execute([$contact_id, $field_id]);
                    
                    if ($check_value->fetch()) {
                        // עדכון ערך קיים
                        $update_field = $pdo->prepare("
                            UPDATE contact_custom_field_values 
                            SET value = ?, updated_at = NOW()
                            WHERE contact_id = ? AND field_id = ?
                        ");
                        $update_field->execute([$value, $contact_id, $field_id]);
                    } else {
                        // הוספת ערך חדש
                        $insert_field = $pdo->prepare("
                            INSERT INTO contact_custom_field_values (contact_id, field_id, value) 
                            VALUES (?, ?, ?)
                        ");
                        $insert_field->execute([$contact_id, $field_id, $value]);
                    }
                }
            }
        }
        
        $pdo->commit();
        return true;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("שגיאה בעדכון איש קשר: " . $e->getMessage());
        return ['error' => 'שגיאה בעדכון איש קשר. אנא נסה שוב.'];
    }
}

/**
 * יצירת רשימת תפוצה חדשה
 * 
 * @param int $user_id מזהה המשתמש
 * @param string $name שם הרשימה
 * @param string $description תיאור הרשימה (אופציונלי)
 * @return int|array מזהה הרשימה החדשה או מערך עם שגיאה
 */
function create_contact_list($user_id, $name, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO contact_lists (user_id, name, description) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$user_id, $name, $description]);
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת רשימת תפוצה: " . $e->getMessage());
        return ['error' => 'שגיאה ביצירת רשימת תפוצה. אנא נסה שוב.'];
    }
}

/**
 * קבלת רשימות התפוצה של המשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @return array רשימות התפוצה
 */
function get_user_contact_lists($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT cl.*, 
                  (SELECT COUNT(*) FROM list_contacts lc WHERE lc.list_id = cl.id) as contacts_count
            FROM contact_lists cl
            WHERE cl.user_id = ?
            ORDER BY cl.name
        ");
        
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליפת רשימות תפוצה: " . $e->getMessage());
        return [];
    }
}

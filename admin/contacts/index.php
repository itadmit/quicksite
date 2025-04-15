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
$page_title = 'ניהול אנשי קשר';

// בדיקה למגבלת אנשי קשר
$subscription = get_active_subscription($current_user['id']);
$has_reached_limit = has_reached_limit('contacts');

// בדיקה אם יש פעולה ספציפית
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$contact_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// קבלת פרמטרים לסינון ומיון
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$status = isset($_GET['status']) ? $_GET['status'] : 'active';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$list_id = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';

// מספר פריטים בעמוד
$per_page = CONTACTS_PER_PAGE;

// טיפול בפעולת POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // בדיקת CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
        set_flash_message('שגיאת אבטחה. נא לרענן את הדף ולנסות שוב.', 'error');
    } else {
        // טיפול בעדכון איש קשר
        if (isset($_POST['update_contact']) && $contact_id > 0) {
            $contact_data = [
                'id' => $contact_id,
                'email' => trim($_POST['email'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // טיפול בשדות מותאמים אישית
            $custom_fields = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $field_id = substr($key, 13); // מיקום אחרי "custom_field_"
                    $custom_fields[$field_id] = $value;
                }
            }
            
            $result = update_contact($contact_data, $custom_fields, $current_user['id']);
            
            if ($result['success']) {
                set_flash_message('איש הקשר עודכן בהצלחה', 'success');
                redirect(SITE_URL . '/admin/contacts/index.php?action=view&id=' . $contact_id);
            } else {
                set_flash_message('אירעה שגיאה: ' . $result['message'], 'error');
            }
        }
        
        // טיפול בהוספת איש קשר חדש
        elseif (isset($_POST['add_contact'])) {
            // בדיקה שהמשתמש לא הגיע למגבלת אנשי הקשר
            if ($has_reached_limit) {
                set_flash_message('הגעת למגבלת אנשי הקשר במנוי שלך. שדרג את המנוי כדי להוסיף יותר אנשי קשר.', 'warning');
                redirect(SITE_URL . '/admin/contacts/index.php');
            }
            
            $contact_data = [
                'email' => trim($_POST['email'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'status' => 'active',
                'source' => 'manual'
            ];
            
            // בדיקת שדות חובה
            if (empty($contact_data['email'])) {
                set_flash_message('כתובת דוא"ל היא שדה חובה', 'error');
                $_SESSION['form_data'] = $contact_data;
                redirect(SITE_URL . '/admin/contacts/index.php?action=add');
            }
            
            // בדיקת תקינות אימייל
            if (!filter_var($contact_data['email'], FILTER_VALIDATE_EMAIL)) {
                set_flash_message('כתובת דוא"ל אינה תקינה', 'error');
                $_SESSION['form_data'] = $contact_data;
                redirect(SITE_URL . '/admin/contacts/index.php?action=add');
            }
            
            // טיפול בשדות מותאמים אישית
            $custom_fields = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $field_id = substr($key, 13);
                    $custom_fields[$field_id] = $value;
                }
            }
            
            $result = add_contact($contact_data, $custom_fields, $current_user['id']);
            
            if ($result['success']) {
                set_flash_message('איש הקשר נוסף בהצלחה', 'success');
                redirect(SITE_URL . '/admin/contacts/index.php?action=view&id=' . $result['contact_id']);
            } else {
                set_flash_message('אירעה שגיאה: ' . $result['message'], 'error');
                $_SESSION['form_data'] = $contact_data;
                redirect(SITE_URL . '/admin/contacts/index.php?action=add');
            }
        }
        
        // טיפול במחיקת איש קשר
        elseif (isset($_POST['delete_contact']) && $contact_id > 0) {
            $result = delete_contact($contact_id, $current_user['id']);
            
            if ($result['success']) {
                set_flash_message('איש הקשר נמחק בהצלחה', 'success');
                redirect(SITE_URL . '/admin/contacts/index.php');
            } else {
                set_flash_message('אירעה שגיאה: ' . $result['message'], 'error');
                redirect(SITE_URL . '/admin/contacts/index.php?action=view&id=' . $contact_id);
            }
        }
        
        // טיפול בהוספה/הסרה מרשימת תפוצה
        elseif (isset($_POST['update_list_membership']) && $contact_id > 0) {
            $list_to_add = isset($_POST['add_to_list']) ? intval($_POST['add_to_list']) : 0;
            $list_to_remove = isset($_POST['remove_from_list']) ? intval($_POST['remove_from_list']) : 0;
            
            if ($list_to_add > 0) {
                $result = add_contact_to_list($contact_id, $list_to_add, $current_user['id']);
                
                if ($result['success']) {
                    set_flash_message('איש הקשר נוסף לרשימה בהצלחה', 'success');
                } else {
                    set_flash_message('אירעה שגיאה בהוספה לרשימה: ' . $result['message'], 'error');
                }
            }
            
            if ($list_to_remove > 0) {
                $result = remove_contact_from_list($contact_id, $list_to_remove, $current_user['id']);
                
                if ($result['success']) {
                    set_flash_message('איש הקשר הוסר מהרשימה בהצלחה', 'success');
                } else {
                    set_flash_message('אירעה שגיאה בהסרה מהרשימה: ' . $result['message'], 'error');
                }
            }
            
            redirect(SITE_URL . '/admin/contacts/index.php?action=view&id=' . $contact_id);
        }
        
        // טיפול בפעולה על מספר אנשי קשר בו-זמנית
        elseif (isset($_POST['bulk_action']) && isset($_POST['selected_contacts'])) {
            $selected_contacts = $_POST['selected_contacts'];
            $bulk_action = $_POST['bulk_action'];
            
            if (!empty($selected_contacts) && $bulk_action !== '') {
                $result = process_bulk_action($bulk_action, $selected_contacts, $current_user['id']);
                
                if ($result['success']) {
                    set_flash_message('הפעולה בוצעה בהצלחה על ' . $result['count'] . ' אנשי קשר', 'success');
                } else {
                    set_flash_message('אירעה שגיאה: ' . $result['message'], 'error');
                }
            } else {
                set_flash_message('יש לבחור אנשי קשר ופעולה לביצוע', 'warning');
            }
            
            redirect(SITE_URL . '/admin/contacts/index.php');
        }
    }
}

// ביצוע פעולות בהתאם לדף המבוקש
switch ($action) {
    case 'view':
        // טעינת פרטי איש הקשר
        $contact = get_contact_details($contact_id, $current_user['id']);
        
        if (!$contact) {
            set_flash_message('איש הקשר לא נמצא או אינו שייך למשתמש זה', 'error');
            redirect(SITE_URL . '/admin/contacts/index.php');
        }
        
        // קבלת רשימות תפוצה של איש הקשר
        $contact_lists = get_contact_lists($contact_id, $current_user['id']);
        
        // קבלת כל רשימות התפוצה של המשתמש
        $all_lists = get_user_contact_lists($current_user['id']);
        
        // קבלת שדות מותאמים אישית של המשתמש
        $custom_fields_definitions = get_user_custom_fields($current_user['id']);
        
        // קבלת ערכי שדות מותאמים אישית של איש הקשר
        $custom_fields_values = get_contact_custom_fields($contact_id);
        
        // קבלת היסטוריית תקשורת עם איש הקשר
        $communication_history = get_contact_communication_history($contact_id);
        
        break;
        
    case 'add':
        // מטען תוכן טופס קודם אם קיים
        $contact = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone' => '',
            'whatsapp' => ''
        ];
        
        // ניקוי נתונים מהסשן
        if (isset($_SESSION['form_data'])) {
            unset($_SESSION['form_data']);
        }
        
        // קבלת שדות מותאמים אישית של המשתמש
        $custom_fields_definitions = get_user_custom_fields($current_user['id']);
        
        break;
        
    case 'edit':
        // טעינת פרטי איש הקשר
        $contact = get_contact_details($contact_id, $current_user['id']);
        
        if (!$contact) {
            set_flash_message('איש הקשר לא נמצא או אינו שייך למשתמש זה', 'error');
            redirect(SITE_URL . '/admin/contacts/index.php');
        }
        
        // קבלת שדות מותאמים אישית של המשתמש
        $custom_fields_definitions = get_user_custom_fields($current_user['id']);
        
        // קבלת ערכי שדות מותאמים אישית של איש הקשר
        $custom_fields_values = get_contact_custom_fields($contact_id);
        
        break;
        
    case 'list':
    default:
        // ברירת מחדל - רשימת אנשי קשר
        
        // קבלת רשימות התפוצה
        $contact_lists = get_user_contact_lists($current_user['id']);
        
        // קבלת רשימת אנשי קשר
        $contacts_data = get_user_contacts(
            $current_user['id'],
            $page,
            $per_page,
            $status,
            $search,
            $list_id,
            $sort_by,
            $sort_order
        );
        
        $contacts = $contacts_data['contacts'];
        $total_pages = $contacts_data['total_pages'];
        $total_count = $contacts_data['total_count'];
        
        break;
}

// טעינת תבנית העיצוב - הדר
include_once '../../includes/header.php';

// הצגת התצוגה המתאימה בהתאם לפעולה הנוכחית
switch ($action) {
    case 'view':
        include 'views/view.php';
        break;
        
    case 'add':
        include 'views/add.php';
        break;
        
    case 'edit':
        include 'views/edit.php';
        break;
        
    case 'list':
    default:
        include 'views/list.php';
        break;
}

/**
 * פונקציה לקבלת רשימת אנשי קשר של משתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $page מספר עמוד
 * @param int $per_page פריטים בעמוד
 * @param string $status סטטוס לסינון
 * @param string $search מחרוזת חיפוש
 * @param int $list_id מזהה רשימת תפוצה לסינון
 * @param string $sort_by שדה למיון
 * @param string $sort_order סדר מיון
 * @return array מערך עם אנשי הקשר, מספר עמודים ומספר כולל
 */
function get_user_contacts($user_id, $page = 1, $per_page = 20, $status = 'active', $search = '', $list_id = 0, $sort_by = 'created_at', $sort_order = 'desc') {
    global $pdo;
    
    $result = [
        'contacts' => [],
        'total_pages' => 0,
        'total_count' => 0
    ];
    
    try {
        // בניית תנאי WHERE
        $where_conditions = ["c.user_id = :user_id"];
        $params = [':user_id' => $user_id];
        
        if ($status !== 'all') {
            $where_conditions[] = "c.status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($search)) {
            $where_conditions[] = "(
                c.email LIKE :search OR 
                c.first_name LIKE :search OR 
                c.last_name LIKE :search OR 
                c.phone LIKE :search OR
                c.whatsapp LIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }
        
        // חיבור SQL
        $join = '';
        
        // אם נבחרה רשימת תפוצה
        if ($list_id > 0) {
            $join = "JOIN list_contacts lc ON c.id = lc.contact_id";
            $where_conditions[] = "lc.list_id = :list_id";
            $params[':list_id'] = $list_id;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // ספירה כוללת
        $count_query = "
            SELECT COUNT(DISTINCT c.id) 
            FROM contacts c
            $join
            WHERE $where_clause
        ";
        $stmt = $pdo->prepare($count_query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $total_count = $stmt->fetchColumn();
        
        // חישוב מספר עמודים
        $total_pages = ceil($total_count / $per_page);
        $page = max(1, min($page, $total_pages > 0 ? $total_pages : 1));
        $offset = ($page - 1) * $per_page;
        
        // וידוא שדה המיון תקין
        $allowed_sort_fields = ['created_at', 'email', 'first_name', 'last_name', 'status'];
        if (!in_array($sort_by, $allowed_sort_fields)) {
            $sort_by = 'created_at';
        }
        
        // וידוא סדר המיון תקין
        $sort_order = strtolower($sort_order) === 'asc' ? 'ASC' : 'DESC';
        
        // בניית שאילתה
        $query = "
            SELECT DISTINCT c.*,
                (SELECT COUNT(*) FROM list_contacts WHERE contact_id = c.id) AS list_count
            FROM contacts c
            $join
            WHERE $where_clause
            ORDER BY c.$sort_by $sort_order
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $result['contacts'] = $stmt->fetchAll();
        $result['total_pages'] = $total_pages;
        $result['total_count'] = $total_count;
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת אנשי קשר: " . $e->getMessage());
        return $result;
    }
}

/**
 * קבלת פרטי איש קשר ספציפי
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $user_id מזהה המשתמש
 * @return array|false פרטי איש הקשר או false אם לא נמצא
 */
function get_contact_details($contact_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM contacts 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$contact_id, $user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת פרטי איש קשר: " . $e->getMessage());
        return false;
    }
}

/**
 * קבלת רשימות תפוצה של איש קשר
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $user_id מזהה המשתמש
 * @return array רשימת רשימות התפוצה
 */
function get_contact_lists($contact_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT cl.* 
            FROM contact_lists cl
            JOIN list_contacts lc ON cl.id = lc.list_id
            WHERE lc.contact_id = ? AND cl.user_id = ?
        ");
        $stmt->execute([$contact_id, $user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת רשימות תפוצה של איש קשר: " . $e->getMessage());
        return [];
    }
}

/**
 * קבלת כל רשימות התפוצה של משתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @return array רשימת רשימות התפוצה
 */
function get_user_contact_lists($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT cl.*, 
                  (SELECT COUNT(*) FROM list_contacts lc WHERE lc.list_id = cl.id) AS contact_count
            FROM contact_lists cl
            WHERE cl.user_id = ?
            ORDER BY cl.name
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת רשימות תפוצה: " . $e->getMessage());
        return [];
    }
}

/**
 * קבלת שדות מותאמים אישית של משתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @return array רשימת שדות מותאמים אישית
 */
function get_user_custom_fields($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM contact_custom_fields
            WHERE user_id = ?
            ORDER BY field_name
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת שדות מותאמים אישית: " . $e->getMessage());
        return [];
    }
}

/**
 * קבלת ערכי שדות מותאמים אישית של איש קשר
 * 
 * @param int $contact_id מזהה איש הקשר
 * @return array מערך של ערכי שדות מותאמים אישית
 */
function get_contact_custom_fields($contact_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT cfv.*, cf.field_name, cf.field_type
            FROM contact_custom_field_values cfv
            JOIN contact_custom_fields cf ON cfv.field_id = cf.id
            WHERE cfv.contact_id = ?
        ");
        $stmt->execute([$contact_id]);
        
        $result = [];
        $rows = $stmt->fetchAll();
        
        foreach ($rows as $row) {
            $result[$row['field_id']] = [
                'value' => $row['value'],
                'field_name' => $row['field_name'],
                'field_type' => $row['field_type']
            ];
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת ערכי שדות מותאמים אישית: " . $e->getMessage());
        return [];
    }
}

/**
 * קבלת היסטוריית תקשורת עם איש קשר
 * 
 * @param int $contact_id מזהה איש הקשר
 * @return array רשימת פעולות תקשורת
 */
function get_contact_communication_history($contact_id) {
    global $pdo;
    
    try {
        // בדיקה אם הטבלה קיימת
        $table_exists = false;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'sent_messages'
            ");
            $stmt->execute([DB_NAME]);
            $table_exists = ($stmt->fetchColumn() > 0);
        } catch (PDOException $e) {
            // התעלם משגיאות בבדיקת קיום הטבלה
            $table_exists = false;
        }
        
        if (!$table_exists) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT sm.*, c.name as campaign_name, c.type as message_type
            FROM sent_messages sm
            JOIN campaigns c ON sm.campaign_id = c.id
            WHERE sm.contact_id = ?
            ORDER BY sm.sent_at DESC
            LIMIT 50
        ");
        $stmt->execute([$contact_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת היסטוריית תקשורת: " . $e->getMessage());
        return [];
    }
}

/**
 * הוספת איש קשר חדש
 * 
 * @param array $contact_data נתוני איש הקשר
 * @param array $custom_fields שדות מותאמים אישית
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message, contact_id]
 */
function add_contact($contact_data, $custom_fields, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => '',
        'contact_id' => 0
    ];
    
    try {
        // בדיקה שאין כפילות אימייל
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM contacts 
            WHERE user_id = ? AND email = ?
        ");
        $stmt->execute([$user_id, $contact_data['email']]);
        
        if ($stmt->fetchColumn() > 0) {
            $result['message'] = 'איש קשר עם כתובת אימייל זו כבר קיים במערכת';
            return $result;
        }
        
        // הוספת איש הקשר
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO contacts (
                user_id, email, phone, first_name, last_name, whatsapp,
                source, status, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        $stmt->execute([
            $user_id,
            $contact_data['email'],
            $contact_data['phone'] ?? '',
            $contact_data['first_name'] ?? '',
            $contact_data['last_name'] ?? '',
            $contact_data['whatsapp'] ?? '',
            $contact_data['source'] ?? 'manual',
            $contact_data['status'] ?? 'active'
        ]);
        
        $contact_id = $pdo->lastInsertId();
        
        // הוספת שדות מותאמים אישית
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $field_id => $value) {
                if (!empty($value)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO contact_custom_field_values (
                            contact_id, field_id, value, created_at, updated_at
                        ) VALUES (
                            ?, ?, ?, NOW(), NOW()
                        )
                    ");
                    $stmt->execute([$contact_id, $field_id, $value]);
                }
            }
        }
        
        $pdo->commit();
        
        $result['success'] = true;
        $result['message'] = 'איש הקשר נוסף בהצלחה';
        $result['contact_id'] = $contact_id;
        
        return $result;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("שגיאה בהוספת איש קשר: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה בהוספת איש הקשר';
        return $result;
    }
}

/**
 * עדכון פרטי איש קשר
 * 
 * @param array $contact_data נתוני איש הקשר
 * @param array $custom_fields שדות מותאמים אישית
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message]
 */
function update_contact($contact_data, $custom_fields, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // בדיקה שאיש הקשר שייך למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$contact_data['id'], $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'איש הקשר לא נמצא או אינו שייך למשתמש זה';
            return $result;
        }
        
        // בדיקה שאין כפילות אימייל
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM contacts 
            WHERE user_id = ? AND email = ? AND id != ?
        ");
        $stmt->execute([$user_id, $contact_data['email'], $contact_data['id']]);
        
        if ($stmt->fetchColumn() > 0) {
            $result['message'] = 'איש קשר אחר עם כתובת אימייל זו כבר קיים במערכת';
            return $result;
        }
        
        // עדכון איש הקשר
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            UPDATE contacts SET
                email = ?,
                phone = ?,
                first_name = ?,
                last_name = ?,
                whatsapp = ?,
                status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $contact_data['email'],
            $contact_data['phone'] ?? '',
            $contact_data['first_name'] ?? '',
            $contact_data['last_name'] ?? '',
            $contact_data['whatsapp'] ?? '',
            $contact_data['status'] ?? 'active',
            $contact_data['id']
        ]);
        
        // עדכון שדות מותאמים אישית
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $field_id => $value) {
                // בדיקה אם כבר קיים ערך לשדה זה
                $stmt = $pdo->prepare("
                    SELECT id FROM contact_custom_field_values 
                    WHERE contact_id = ? AND field_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$contact_data['id'], $field_id]);
                $field_value_id = $stmt->fetchColumn();
                
                if ($field_value_id) {
                    // עדכון ערך קיים
                    if (!empty($value)) {
                        $stmt = $pdo->prepare("
                            UPDATE contact_custom_field_values 
                            SET value = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$value, $field_value_id]);
                    } else {
                        // אם הערך ריק, מחק אותו
                        $stmt = $pdo->prepare("
                            DELETE FROM contact_custom_field_values 
                            WHERE id = ?
                        ");
                        $stmt->execute([$field_value_id]);
                    }
                } else if (!empty($value)) {
                    // הוספת ערך חדש
                    $stmt = $pdo->prepare("
                        INSERT INTO contact_custom_field_values (
                            contact_id, field_id, value, created_at, updated_at
                        ) VALUES (
                            ?, ?, ?, NOW(), NOW()
                        )
                    ");
                    $stmt->execute([$contact_data['id'], $field_id, $value]);
                }
            }
        }
        
        $pdo->commit();
        
        $result['success'] = true;
        $result['message'] = 'איש הקשר עודכן בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("שגיאה בעדכון איש קשר: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה בעדכון איש הקשר';
        return $result;
    }
}

/**
 * מחיקת איש קשר
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message]
 */
function delete_contact($contact_id, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // בדיקה שאיש הקשר שייך למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$contact_id, $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'איש הקשר לא נמצא או אינו שייך למשתמש זה';
            return $result;
        }
        
        $pdo->beginTransaction();
        
        // מחיקת שדות מותאמים אישית
        $stmt = $pdo->prepare("
            DELETE FROM contact_custom_field_values 
            WHERE contact_id = ?
        ");
        $stmt->execute([$contact_id]);
        
        // מחיקה מרשימות תפוצה
        $stmt = $pdo->prepare("
            DELETE FROM list_contacts 
            WHERE contact_id = ?
        ");
        $stmt->execute([$contact_id]);
        
        // מחיקת איש הקשר עצמו
        $stmt = $pdo->prepare("
            DELETE FROM contacts 
            WHERE id = ?
        ");
        $stmt->execute([$contact_id]);
        
        $pdo->commit();
        
        $result['success'] = true;
        $result['message'] = 'איש הקשר נמחק בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("שגיאה במחיקת איש קשר: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה במחיקת איש הקשר';
        return $result;
    }
}

/**
 * הוספת איש קשר לרשימת תפוצה
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $list_id מזהה רשימת התפוצה
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message]
 */
function add_contact_to_list($contact_id, $list_id, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // בדיקה שאיש הקשר שייך למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$contact_id, $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'איש הקשר לא נמצא או אינו שייך למשתמש זה';
            return $result;
        }
        
        // בדיקה שהרשימה שייכת למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contact_lists 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$list_id, $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'רשימת התפוצה לא נמצאה או אינה שייכת למשתמש זה';
            return $result;
        }
        
        // בדיקה שאיש הקשר לא כבר ברשימה
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM list_contacts 
            WHERE list_id = ? AND contact_id = ?
        ");
        $stmt->execute([$list_id, $contact_id]);
        if ($stmt->fetchColumn() > 0) {
            $result['message'] = 'איש הקשר כבר נמצא ברשימת התפוצה';
            $result['success'] = true; // לא נחשב כשגיאה
            return $result;
        }
        
        // הוספה לרשימה
        $stmt = $pdo->prepare("
            INSERT INTO list_contacts (list_id, contact_id, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$list_id, $contact_id]);
        
        $result['success'] = true;
        $result['message'] = 'איש הקשר נוסף לרשימת התפוצה בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בהוספת איש קשר לרשימה: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה בהוספת איש הקשר לרשימת התפוצה';
        return $result;
    }
}

/**
 * הסרת איש קשר מרשימת תפוצה
 * 
 * @param int $contact_id מזהה איש הקשר
 * @param int $list_id מזהה רשימת התפוצה
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message]
 */
function remove_contact_from_list($contact_id, $list_id, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    try {
        // בדיקה שאיש הקשר שייך למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$contact_id, $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'איש הקשר לא נמצא או אינו שייך למשתמש זה';
            return $result;
        }
        
        // בדיקה שהרשימה שייכת למשתמש
        $stmt = $pdo->prepare("
            SELECT id FROM contact_lists 
            WHERE id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$list_id, $user_id]);
        if (!$stmt->fetchColumn()) {
            $result['message'] = 'רשימת התפוצה לא נמצאה או אינה שייכת למשתמש זה';
            return $result;
        }
        
        // הסרה מהרשימה
        $stmt = $pdo->prepare("
            DELETE FROM list_contacts 
            WHERE list_id = ? AND contact_id = ?
        ");
        $stmt->execute([$list_id, $contact_id]);
        
        $result['success'] = true;
        $result['message'] = 'איש הקשר הוסר מרשימת התפוצה בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בהסרת איש קשר מרשימה: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה בהסרת איש הקשר מרשימת התפוצה';
        return $result;
    }
}

/**
 * ביצוע פעולה על מספר אנשי קשר בו-זמנית
 * 
 * @param string $action סוג הפעולה
 * @param array $contact_ids מזהי אנשי הקשר
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הפעולה [success, message, count]
 */
function process_bulk_action($action, $contact_ids, $user_id) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => '',
        'count' => 0
    ];
    
    if (empty($contact_ids)) {
        $result['message'] = 'לא נבחרו אנשי קשר';
        return $result;
    }
    
    try {
        // וידוא שכל אנשי הקשר שייכים למשתמש
        $placeholders = implode(',', array_fill(0, count($contact_ids), '?'));
        $params = $contact_ids;
        $params[] = $user_id;
        
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM contacts 
            WHERE id IN ($placeholders) AND user_id = ?
        ");
        $stmt->execute($params);
        
        $count = $stmt->fetchColumn();
        if ($count !== count($contact_ids)) {
            $result['message'] = 'חלק מאנשי הקשר שנבחרו אינם שייכים למשתמש זה';
            return $result;
        }
        
        // ביצוע הפעולה בהתאם לסוג
        switch ($action) {
            case 'delete':
                // מחיקת אנשי קשר
                $pdo->beginTransaction();
                
                // מחיקת שדות מותאמים אישית
                $stmt = $pdo->prepare("
                    DELETE FROM contact_custom_field_values 
                    WHERE contact_id IN ($placeholders)
                ");
                $stmt->execute($contact_ids);
                
                // מחיקה מרשימות תפוצה
                $stmt = $pdo->prepare("
                    DELETE FROM list_contacts 
                    WHERE contact_id IN ($placeholders)
                ");
                $stmt->execute($contact_ids);
                
                // מחיקת אנשי הקשר עצמם
                $stmt = $pdo->prepare("
                    DELETE FROM contacts 
                    WHERE id IN ($placeholders)
                ");
                $stmt->execute($contact_ids);
                
                $pdo->commit();
                
                $result['success'] = true;
                $result['message'] = $count . ' אנשי קשר נמחקו בהצלחה';
                $result['count'] = $count;
                break;
                
            case 'add_to_list':
                // הוספה לרשימת תפוצה
                $list_id = intval($_POST['bulk_list_id'] ?? 0);
                
                if ($list_id <= 0) {
                    $result['message'] = 'יש לבחור רשימת תפוצה תקפה';
                    return $result;
                }
                
                // בדיקה שהרשימה שייכת למשתמש
                $stmt = $pdo->prepare("
                    SELECT id FROM contact_lists 
                    WHERE id = ? AND user_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$list_id, $user_id]);
                if (!$stmt->fetchColumn()) {
                    $result['message'] = 'רשימת התפוצה לא נמצאה או אינה שייכת למשתמש זה';
                    return $result;
                }
                
                // הוספה לרשימה
                $pdo->beginTransaction();
                
                foreach ($contact_ids as $contact_id) {
                    // בדיקה אם כבר קיים ברשימה
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) FROM list_contacts 
                        WHERE list_id = ? AND contact_id = ?
                    ");
                    $stmt->execute([$list_id, $contact_id]);
                    
                    if ($stmt->fetchColumn() == 0) {
                        // הוספה רק אם לא קיים
                        $stmt = $pdo->prepare("
                            INSERT INTO list_contacts (list_id, contact_id, created_at)
                            VALUES (?, ?, NOW())
                        ");
                        $stmt->execute([$list_id, $contact_id]);
                    }
                }
                
                $pdo->commit();
                
                $result['success'] = true;
                $result['message'] = $count . ' אנשי קשר נוספו לרשימת התפוצה בהצלחה';
                $result['count'] = $count;
                break;
                
            case 'change_status':
                // שינוי סטטוס
                $new_status = $_POST['bulk_status'] ?? '';
                $allowed_statuses = ['active', 'unsubscribed', 'bounced'];
                
                if (!in_array($new_status, $allowed_statuses)) {
                    $result['message'] = 'יש לבחור סטטוס תקף';
                    return $result;
                }
                
                $stmt = $pdo->prepare("
                    UPDATE contacts 
                    SET status = ?, updated_at = NOW()
                    WHERE id IN ($placeholders)
                ");
                $params = [$new_status];
                $params = array_merge($params, $contact_ids);
                $stmt->execute($params);
                
                $result['success'] = true;
                $result['message'] = 'סטטוס ' . $count . ' אנשי קשר עודכן בהצלחה';
                $result['count'] = $count;
                break;
                
            default:
                $result['message'] = 'פעולה לא תקפה';
                return $result;
        }
        
        return $result;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        error_log("שגיאה בביצוע פעולת המוניות: " . $e->getMessage());
        $result['message'] = 'אירעה שגיאה בביצוע הפעולה';
        return $result;
    }
}
?>

<?php include_once '../../includes/footer.php'; ?>
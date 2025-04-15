<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * בדיקה אם המשתמש מחובר
 * 
 * @return bool האם המשתמש מחובר
 */
function isLoggedIn() {
    global $current_user;
    return !empty($current_user);
}

/**
 * הצפנת סיסמה בשיטת bcrypt
 * 
 * @param string $password הסיסמה לפני הצפנה
 * @return string הסיסמה המוצפנת
 */
function password_hash_custom($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

/**
 * אימות סיסמה מוצפנת
 * 
 * @param string $password הסיסמה מקורית (ללא הצפנה)
 * @param string $hash הסיסמה המוצפנת המאוחסנת במערכת
 * @return bool האם הסיסמה נכונה
 */
function password_verify_custom($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * ביצוע התחברות למערכת
 * 
 * @param string $email כתובת דוא"ל
 * @param string $password סיסמה
 * @param bool $remember האם לזכור את המשתמש
 * @return array תוצאות ההתחברות [success, message, user_id]
 */
function login($email, $password, $remember = false) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => '',
        'user_id' => 0
    ];
    
    if (empty($email) || empty($password)) {
        $result['message'] = 'נא למלא את כל השדות';
        return $result;
    }
    
    try {
        // בדיקה אם המשתמש קיים
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $result['message'] = 'שם משתמש או סיסמה שגויים';
            return $result;
        }
        
        // בדיקה אם המשתמש מאומת
        if (!$user['is_verified']) {
            $result['message'] = 'חשבון זה טרם אומת. אנא בדוק את המייל שלך להוראות אימות';
            return $result;
        }
        
        // בדיקה אם המשתמש מושעה
        if ($user['status'] !== 'active') {
            $result['message'] = 'חשבון זה אינו פעיל. אנא פנה לתמיכה';
            return $result;
        }
        
        // בדיקת סיסמה
        if (!password_verify_custom($password, $user['password'])) {
            $result['message'] = 'שם משתמש או סיסמה שגויים';
            return $result;
        }
        
        // האם צריך לחדש את ההצפנה
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST])) {
            $new_hash = password_hash_custom($password);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$new_hash, $user['id']]);
        }
        
        // עדכון זמן התחברות אחרון
        $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);
        
        // יצירת סשן
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_activity'] = time();
        
        // יצירת cookie לזכירת משתמש
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            // שמירת הטוקן במסד הנתונים
            $stmt = $pdo->prepare("
                INSERT INTO remember_tokens (user_id, token, expires) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $expires)]);
            
            // יצירת עוגיה
            setcookie('remember_token', $token, $expires, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), true);
        }
        
        $result['success'] = true;
        $result['user_id'] = $user['id'];
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה בהתחברות, אנא נסה שוב מאוחר יותר';
        error_log("שגיאת התחברות: " . $e->getMessage());
        return $result;
    }
}

/**
 * ביצוע התנתקות מהמערכת
 * 
 * @return void
 */
function logout() {
    // ניקוי עוגיית זכירת משתמש אם קיימת
    if (isset($_COOKIE['remember_token'])) {
        global $pdo;
        
        try {
            // מחיקת הטוקן ממסד הנתונים
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->execute([$_COOKIE['remember_token']]);
        } catch (PDOException $e) {
            error_log("שגיאה במחיקת טוקן זכירה: " . $e->getMessage());
        }
        
        // מחיקת העוגיה
        setcookie('remember_token', '', time() - 3600, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), true);
    }
    
    // ניקוי סשן
    session_unset();
    session_destroy();
}

/**
 * בדיקת התחברות מעוגיית זכירה
 * 
 * @return bool האם ההתחברות הצליחה
 */
function check_remember_login() {
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    global $pdo;
    $token = $_COOKIE['remember_token'];
    
    try {
        // בדיקה אם הטוקן קיים ותקף
        $stmt = $pdo->prepare("
            SELECT u.* 
            FROM users u 
            JOIN remember_tokens rt ON u.id = rt.user_id 
            WHERE rt.token = ? AND rt.expires > NOW() AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // מחיקת העוגיה אם הטוקן אינו תקף
            setcookie('remember_token', '', time() - 3600, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), true);
            return false;
        }
        
        // התחברות אוטומטית
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_activity'] = time();
        
        // עדכון זמן פקיעת הטוקן
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        $stmt = $pdo->prepare("UPDATE remember_tokens SET expires = ? WHERE token = ?");
        $stmt->execute([date('Y-m-d H:i:s', $expires), $token]);
        
        // עדכון העוגיה
        setcookie('remember_token', $token, $expires, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'), true);
        
        return true;
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת טוקן זכירה: " . $e->getMessage());
        return false;
    }
}

/**
 * רישום משתמש חדש
 * 
 * @param array $user_data נתוני המשתמש [email, password, first_name, last_name, ...]
 * @return array תוצאות הרישום [success, message, user_id]
 */
function register($user_data) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => '',
        'user_id' => 0
    ];
    
    // וידוא שדות חובה
    if (empty($user_data['email']) || empty($user_data['password']) || 
        empty($user_data['first_name']) || empty($user_data['last_name'])) {
        $result['message'] = 'נא למלא את כל שדות החובה';
        return $result;
    }
    
    // בדיקת אורך סיסמה
    if (strlen($user_data['password']) < PASSWORD_MIN_LENGTH) {
        $result['message'] = 'הסיסמה חייבת להיות לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        return $result;
    }
    
    // בדיקת תקינות כתובת אימייל
    if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
        $result['message'] = 'כתובת האימייל אינה תקינה';
        return $result;
    }
    
    try {
        // בדיקה אם האימייל כבר רשום
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user_data['email']]);
        
        if ($stmt->fetchColumn()) {
            $result['message'] = 'כתובת האימייל כבר רשומה במערכת';
            return $result;
        }
        
        // הצפנת הסיסמה
        $hashed_password = password_hash_custom($user_data['password']);
        
        // הוספת המשתמש למסד הנתונים
        $stmt = $pdo->prepare("
            INSERT INTO users (
                email, password, first_name, last_name, company_name, phone,
                is_verified, status, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 1, 'active', NOW()
            )
        ");
        
        $stmt->execute([
            $user_data['email'],
            $hashed_password,
            $user_data['first_name'],
            $user_data['last_name'],
            $user_data['company_name'] ?? '',
            $user_data['phone'] ?? ''
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // יצירת מנוי ניסיון ל-7 ימים
        create_trial_subscription($user_id, 7);
        
        $result['success'] = true;
        $result['message'] = 'ההרשמה בוצעה בהצלחה. החשבון מאומת ומנוי ניסיון ל-7 ימים הופעל אוטומטית';
        $result['user_id'] = $user_id;
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה ברישום, אנא נסה שוב מאוחר יותר';
        error_log("שגיאת רישום: " . $e->getMessage());
        return $result;
    }
}

/**
 * בדיקה אם יש למשתמש מנוי פעיל
 * 
 * @param int $user_id מזהה המשתמש
 * @return bool האם יש מנוי פעיל
 */
function has_active_subscription($user_id) {
    global $pdo;
    
    // וידוא שיש מזהה משתמש תקין
    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM subscriptions 
            WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()
        ");
        
        $stmt->execute([(int)$user_id]);
        
        return ($stmt->fetchColumn() > 0);
    } catch (PDOException $e) {
        error_log("שגיאה בבדיקת מנוי פעיל: " . $e->getMessage());
        return false;
    }
}

/**
 * קבלת פרטי המנוי הפעיל של המשתמש
 * 
 * @param int $user_id מזהה המשתמש
 * @return array|false פרטי המנוי או false אם אין מנוי פעיל
 */
function get_active_subscription($user_id) {
    global $pdo;
    
    // וידוא שיש מזהה משתמש תקין
    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }
    
    try {
        // שליפת המנוי הפעיל
        $stmt = $pdo->prepare("
            SELECT s.*, p.name as plan_name, p.price, p.landing_pages_limit, 
                   p.contacts_limit, p.messages_limit, p.views_limit
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()
            ORDER BY s.end_date DESC
            LIMIT 1
        ");
        
        $stmt->execute([$user_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת מנוי פעיל: " . $e->getMessage());
        return false;
    }
}

/**
 * הגבלת גישה לדפים שדורשים התחברות
 * 
 * @return void
 */
function require_login() {
    if (!isLoggedIn()) {
        set_flash_message('עליך להתחבר כדי לגשת לדף זה', 'warning');
        redirect(SITE_URL . '/auth/login.php');
    }
}

/**
 * הגבלת גישה לדפים שדורשים מנוי פעיל
 * 
 * @return void
 */
function require_subscription() {
    if (!isLoggedIn()) {
        set_flash_message('עליך להתחבר כדי לגשת לדף זה', 'warning');
        redirect(SITE_URL . '/auth/login.php');
    }
    
    global $current_user;
    
    if (!has_active_subscription($current_user['id'])) {
        set_flash_message('נדרש מנוי פעיל כדי לגשת לדף זה', 'warning');
        redirect(SITE_URL . '/admin/subscription.php');
    }
}

/**
 * יצירת תוכנית מנוי ברירת מחדל
 */
function create_default_plan() {
    global $pdo;
    
    try {
        // בדיקה אם יש כבר תוכניות
        $check = $pdo->query("SELECT COUNT(*) FROM plans");
        if ($check->fetchColumn() > 0) {
            return true; // יש כבר תוכניות, לא צריך ליצור
        }
        
        // יצירת תוכנית בסיסית
        $stmt = $pdo->prepare("
            INSERT INTO plans (
                name, description, price, landing_pages_limit, 
                contacts_limit, messages_limit, views_limit,
                custom_domain, api_access, status
            ) VALUES (
                'מסלול בסיסי', 'מסלול בסיסי לעסקים קטנים', 99.00, 5, 
                500, 1000, 5000, 0, 0, 'active'
            )
        ");
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת תוכנית ברירת מחדל: " . $e->getMessage());
        return false;
    }
}

/**
 * יצירת מנוי ניסיון למשתמש חדש
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $trial_days מספר ימי הניסיון
 * @return bool האם הפעולה הצליחה
 */
function create_trial_subscription($user_id, $trial_days = 7) {
    global $pdo;
    
    // וידוא שיש מזהה משתמש תקין
    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }
    
    try {
        // בדיקת המבנה של טבלת subscriptions
        $columns_check = $pdo->query("SHOW COLUMNS FROM subscriptions");
        $columns = [];
        while ($column = $columns_check->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        
        // בדיקה אם כבר יש מנוי פעיל למשתמש זה
        $subscription_check = $pdo->prepare("
            SELECT COUNT(*) FROM subscriptions 
            WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()
        ");
        $subscription_check->execute([$user_id]);
        
        if ($subscription_check->fetchColumn() > 0) {
            // כבר יש מנוי פעיל, לא צריך ליצור חדש
            return true;
        }
        
        // מציאת תוכנית בסיסית
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE status = 'active' ORDER BY price ASC LIMIT 1");
        $stmt->execute();
        $plan = $stmt->fetch();
        
        if (!$plan) {
            // אם אין תוכניות, ניצור תוכנית ברירת מחדל
            create_default_plan();
            
            // נסה שוב למצוא תוכנית
            $stmt->execute();
            $plan = $stmt->fetch();
            
            if (!$plan) {
                return false;
            }
        }
        
        $plan_id = $plan['id'];
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$trial_days} days"));
        
        // יצירת מנוי ניסיון בהתאם למבנה הטבלה
        if (in_array('payment_status', $columns)) {
            // אם יש עמודת payment_status
            $stmt = $pdo->prepare("
                INSERT INTO subscriptions (
                    user_id, plan_id, status, start_date, end_date,
                    payment_status, created_at, updated_at
                ) VALUES (
                    ?, ?, 'active', ?, ?, 'trial', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                $user_id,
                $plan_id,
                $start_date,
                $end_date
            ]);
        } else {
            // אם אין עמודת payment_status
            $stmt = $pdo->prepare("
                INSERT INTO subscriptions (
                    user_id, plan_id, status, start_date, end_date,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, 'active', ?, ?, NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                $user_id,
                $plan_id,
                $start_date,
                $end_date
            ]);
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת מנוי ניסיון: " . $e->getMessage());
        return false;
    }
}



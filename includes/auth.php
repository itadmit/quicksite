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
        
        // יצירת קוד אימות
        $verification_code = generate_token('verification', 32, 48); // תוקף של 48 שעות
        
        // הוספת המשתמש למסד הנתונים
        $stmt = $pdo->prepare("
            INSERT INTO users (
                email, password, first_name, last_name, company_name, phone,
                verification_code, is_verified, status, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        
        $stmt->execute([
            $user_data['email'],
            $hashed_password,
            $user_data['first_name'],
            $user_data['last_name'],
            $user_data['company_name'] ?? '',
            $user_data['phone'] ?? '',
            $verification_code,
            1, // שינוי כאן: מסומן כמאומת מיד
            'active'
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        // יצירת מנוי ניסיון ל-7 ימים
        create_trial_subscription($user_id, 7);
        
        // בגלל שאנחנו בסביבת לוקלהוסט, רק לוג את הקוד במקום לשלוח מייל
        error_log("קוד אימות למשתמש {$user_data['email']}: {$verification_code}");
        
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
 * שליחת אימייל אימות
 * 
 * @param string $email כתובת האימייל
 * @param string $code קוד האימות
 * @return bool האם שליחת האימייל הצליחה
 */
function send_verification_email($email, $code) {
    $verification_url = SITE_URL . "/auth/verify.php?email=" . urlencode($email) . "&code=" . urlencode($code);
    
    $subject = SITE_NAME . " - אימות חשבון";
    
    $message = "
    <html dir='rtl'>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4f46e5; color: white; padding: 10px 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; 
                      text-decoration: none; border-radius: 5px; }
            .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>" . SITE_NAME . "</h2>
            </div>
            <div class='content'>
                <h3>ברוכים הבאים!</h3>
                <p>תודה שנרשמת ל-" . SITE_NAME . ".</p>
                <p>כדי להשלים את תהליך ההרשמה ולאמת את כתובת האימייל שלך, אנא לחץ על הכפתור למטה:</p>
                <p style='text-align: center;'>
                    <a href='" . $verification_url . "' class='button'>אמת את החשבון שלי</a>
                </p>
                <p>או העתק את הקישור הבא לדפדפן שלך:</p>
                <p>" . $verification_url . "</p>
                <p>קישור זה יהיה בתוקף למשך 48 שעות.</p>
                <p>אם לא נרשמת ל-" . SITE_NAME . ", אנא התעלם מהודעה זו.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". כל הזכויות שמורות.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // כותרות אימייל
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>',
        'Reply-To: ' . ADMIN_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($email, $subject, $message, implode("\r\n", $headers));
}

/**
 * אימות חשבון משתמש
 * 
 * @param string $email כתובת האימייל
 * @param string $code קוד האימות
 * @return bool האם האימות הצליח
 */
function verify_account($email, $code) {
    global $pdo;
    
    try {
        // בדיקה אם קוד האימות תואם
        $stmt = $pdo->prepare("
            SELECT id, verification_code 
            FROM users 
            WHERE email = ? AND is_verified = 0
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // פיצול הקוד לטוקן ותאריך תפוגה
        $parts = explode('|', $user['verification_code']);
        
        if (count($parts) !== 2) {
            return false;
        }
        
        $token = $parts[0];
        $expiry_time = strtotime($parts[1]);
        
        // בדיקה אם הקוד פג תוקף
        if ($expiry_time < time()) {
            return false;
        }
        
        // בדיקה אם הקוד תואם
        if ($code !== $user['verification_code']) {
            return false;
        }
        
        // עדכון סטטוס המשתמש
        $update = $pdo->prepare("
            UPDATE users 
            SET is_verified = 1, verification_code = NULL 
            WHERE id = ?
        ");
        $update->execute([$user['id']]);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("שגיאה באימות חשבון: " . $e->getMessage());
        return false;
    }
}

/**
 * שליחת קוד איפוס סיסמה
 * 
 * @param string $email כתובת האימייל
 * @return array תוצאות השליחה [success, message]
 */
function send_password_reset($email) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    if (empty($email)) {
        $result['message'] = 'נא להזין כתובת אימייל';
        return $result;
    }
    
    try {
        // בדיקה אם האימייל קיים
        $stmt = $pdo->prepare("
            SELECT id, first_name, is_verified, status 
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // מטעמי אבטחה, לא להודיע שהאימייל לא קיים
            $result['success'] = true;
            $result['message'] = 'אם כתובת האימייל קיימת במערכת, נשלחו הוראות לאיפוס הסיסמה';
            return $result;
        }
        
        // בדיקה אם המשתמש מאומת ופעיל
        if (!$user['is_verified'] || $user['status'] !== 'active') {
            $result['success'] = true; // מטעמי אבטחה, להחזיר הצלחה בכל מקרה
            $result['message'] = 'אם כתובת האימייל קיימת במערכת, נשלחו הוראות לאיפוס הסיסמה';
            return $result;
        }
        
        // יצירת קוד איפוס
        $reset_code = generate_token('reset', 32, 24); // תוקף 24 שעות
        
        // שמירת קוד האיפוס
        $stmt = $pdo->prepare("
            UPDATE users 
            SET reset_token = ? 
            WHERE id = ?
        ");
        $stmt->execute([$reset_code, $user['id']]);
        
        // שליחת אימייל
        $reset_url = SITE_URL . "/auth/reset-password.php?email=" . urlencode($email) . "&code=" . urlencode($reset_code);
        
        $subject = SITE_NAME . " - איפוס סיסמה";
        
        $message = "
        <html dir='rtl'>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4f46e5; color: white; padding: 10px 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .button { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; 
                          text-decoration: none; border-radius: 5px; }
                .footer { font-size: 12px; color: #777; margin-top: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . SITE_NAME . "</h2>
                </div>
                <div class='content'>
                    <h3>שלום " . $user['first_name'] . ",</h3>
                    <p>קיבלנו בקשה לאיפוס הסיסמה שלך ב-" . SITE_NAME . ".</p>
                    <p>לחץ על הכפתור למטה כדי לאפס את הסיסמה:</p>
                    <p style='text-align: center;'>
                        <a href='" . $reset_url . "' class='button'>איפוס סיסמה</a>
                    </p>
                    <p>או העתק את הקישור הבא לדפדפן שלך:</p>
                    <p>" . $reset_url . "</p>
                    <p>קישור זה יהיה בתוקף למשך 24 שעות.</p>
                    <p>אם לא ביקשת לאפס את הסיסמה, אנא התעלם מהודעה זו.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . SITE_NAME . ". כל הזכויות שמורות.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // כותרות אימייל
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>',
            'Reply-To: ' . ADMIN_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $mail_sent = mail($email, $subject, $message, implode("\r\n", $headers));
        
        if (!$mail_sent) {
            $result['message'] = 'שגיאה בשליחת האימייל, אנא נסה שוב מאוחר יותר';
            return $result;
        }
        
        $result['success'] = true;
        $result['message'] = 'אם כתובת האימייל קיימת במערכת, נשלחו הוראות לאיפוס הסיסמה';
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה במערכת, אנא נסה שוב מאוחר יותר';
        error_log("שגיאה בבקשת איפוס סיסמה: " . $e->getMessage());
        return $result;
    }
}

/**
 * איפוס סיסמה
 * 
 * @param string $email כתובת האימייל
 * @param string $code קוד איפוס
 * @param string $new_password הסיסמה החדשה
 * @return array תוצאות האיפוס [success, message]
 */
function reset_password($email, $code, $new_password) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    if (empty($email) || empty($code) || empty($new_password)) {
        $result['message'] = 'נא למלא את כל השדות';
        return $result;
    }
    
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $result['message'] = 'הסיסמה חייבת להיות לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        return $result;
    }
    
    try {
        // בדיקה אם קוד האיפוס תקף
        $stmt = $pdo->prepare("
            SELECT id, reset_token 
            FROM users 
            WHERE email = ? AND is_verified = 1 AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user || empty($user['reset_token'])) {
            $result['message'] = 'קישור האיפוס אינו תקף או שפג תוקפו';
            return $result;
        }
        
        // בדיקה אם הקוד תואם ולא פג תוקף
        if (!validate_token($user['reset_token']) || $code !== $user['reset_token']) {
            $result['message'] = 'קישור האיפוס אינו תקף או שפג תוקפו';
            return $result;
        }
        
        // הצפנת הסיסמה החדשה
        $hashed_password = password_hash_custom($new_password);
        
        // עדכון הסיסמה
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$hashed_password, $user['id']]);
        
        $result['success'] = true;
        $result['message'] = 'הסיסמה עודכנה בהצלחה, כעת ניתן להתחבר עם הסיסמה החדשה';
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה במערכת, אנא נסה שוב מאוחר יותר';
        error_log("שגיאה באיפוס סיסמה: " . $e->getMessage());
        return $result;
    }
}

/**
 * עדכון סיסמה למשתמש מחובר
 * 
 * @param int $user_id מזהה המשתמש
 * @param string $old_password הסיסמה הישנה
 * @param string $new_password הסיסמה החדשה
 * @return array תוצאות העדכון [success, message]
 */
function update_password($user_id, $old_password, $new_password) {
    global $pdo;
    
    $result = [
        'success' => false,
        'message' => ''
    ];
    
    if (empty($old_password) || empty($new_password)) {
        $result['message'] = 'נא למלא את כל השדות';
        return $result;
    }
    
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $result['message'] = 'הסיסמה החדשה חייבת להיות לפחות ' . PASSWORD_MIN_LENGTH . ' תווים';
        return $result;
    }
    
    try {
        // קבלת פרטי המשתמש
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $result['message'] = 'משתמש לא קיים';
            return $result;
        }
        
        // אימות הסיסמה הישנה
        if (!password_verify_custom($old_password, $user['password'])) {
            $result['message'] = 'הסיסמה הנוכחית שגויה';
            return $result;
        }
        
        // הצפנת הסיסמה החדשה
        $hashed_password = password_hash_custom($new_password);
        
        // עדכון הסיסמה
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        $result['success'] = true;
        $result['message'] = 'הסיסמה עודכנה בהצלחה';
        
        return $result;
        
    } catch (PDOException $e) {
        $result['message'] = 'שגיאה במערכת, אנא נסה שוב מאוחר יותר';
        error_log("שגיאה בעדכון סיסמה: " . $e->getMessage());
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
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM subscriptions 
            WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()
        ");
        $stmt->execute([$user_id]);
        
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
    
    if (!$user_id) {
        return false;
    }
    
    try {
        // בדיקה אם טבלת subscriptions קיימת
        $table_check = $pdo->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$table_check || $table_check->rowCount() === 0) {
            return false;
        }
        
        // בדיקת המבנה של טבלת subscriptions
        $columns_check = $pdo->query("SHOW COLUMNS FROM subscriptions");
        $columns = [];
        while ($column = $columns_check->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        
        $has_new_structure = in_array('payment_status', $columns) && in_array('renewal_date', $columns);
        
        // השאילתה תשתנה בהתאם למבנה הטבלה
        if ($has_new_structure) {
            $stmt = $pdo->prepare("
                SELECT s.*, p.name as plan_name, p.price, p.landing_pages_limit, 
                       p.contacts_limit, p.messages_limit, p.views_limit
                FROM subscriptions s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()
                ORDER BY s.end_date DESC
                LIMIT 1
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT s.*, p.name as plan_name, p.price, p.landing_pages_limit, 
                       p.contacts_limit, p.messages_limit, p.views_limit, 
                       s.payment_method, s.auto_renew
                FROM subscriptions s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE()
                ORDER BY s.end_date DESC
                LIMIT 1
            ");
        }
        
        $stmt->execute([$user_id]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            // המרת ערכים משדות שונים למבנה אחיד עבור הקוד הקיים
            if ($has_new_structure) {
                // המרת payment_status לpayment_method (לתאימות עם הקוד הקיים)
                $subscription['payment_method'] = $subscription['payment_status'];
                // סימולציה של auto_renew על בסיס renewal_date
                $subscription['auto_renew'] = !empty($subscription['renewal_date']) && 
                                              strtotime($subscription['renewal_date']) > strtotime($subscription['end_date']);
            }
            
            return $subscription;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("שגיאה בקבלת מנוי פעיל: " . $e->getMessage());
        return false;
    }
}

/**
 * הגבלת גישה לדפים שדורשים התחברות
 * מפנה לדף ההתחברות אם המשתמש לא מחובר
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
 * מפנה לדף המנויים אם אין מנוי פעיל
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
 * יצירת רשומה בטבלת remember_tokens למקרה שהטבלה חסרה
 * 
 * @return void
 */
function create_remember_tokens_table() {
    global $pdo;
    
    try {
        $pdo->query("
            CREATE TABLE IF NOT EXISTS `remember_tokens` (
              `id` INT PRIMARY KEY AUTO_INCREMENT,
              `user_id` INT NOT NULL,
              `token` VARCHAR(255) NOT NULL,
              `expires` DATETIME NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
              UNIQUE (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת טבלת remember_tokens: " . $e->getMessage());
    }
}

// הוספת עמודת reset_token לטבלת users אם היא חסרה
function add_reset_token_column() {
    global $pdo;
    
    try {
        // בדיקה אם העמודה קיימת
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'reset_token'
        ");
        $stmt->execute([DB_NAME]);
        
        if ($stmt->fetchColumn() == 0) {
            // הוספת העמודה
            $pdo->query("ALTER TABLE `users` ADD COLUMN `reset_token` VARCHAR(255) NULL");
        }
    } catch (PDOException $e) {
        error_log("שגיאה בהוספת עמודת reset_token: " . $e->getMessage());
    }
}

// וידוא שהטבלאות הנדרשות לאימות קיימות
create_remember_tokens_table();
add_reset_token_column();

/**
 * יצירת מנוי ניסיון למשתמש חדש
 * 
 * @param int $user_id מזהה המשתמש
 * @param int $trial_days מספר ימי הניסיון (ברירת מחדל: 7)
 * @return bool האם הפעולה הצליחה
 */
function create_trial_subscription($user_id, $trial_days = 7) {
    global $pdo;
    
    if (!$user_id) {
        error_log("שגיאה ביצירת מנוי ניסיון: מזהה משתמש חסר");
        return false;
    }
    
    try {
        // בדיקה שטבלת plans קיימת
        try {
            // תיקון: ב-SHOW TABLES אין אפשרות להשתמש בפרמטרים, לכן נוסיף את הערך ישירות למחרוזת
            $table_check = $pdo->query("SHOW TABLES LIKE 'plans'");
            if (!$table_check || $table_check->rowCount() === 0) {
                // טבלת plans לא קיימת - ניצור אותה
                create_plans_table();
            }
            
            // בדיקה שטבלת subscriptions קיימת
            $table_check = $pdo->query("SHOW TABLES LIKE 'subscriptions'");
            if (!$table_check || $table_check->rowCount() === 0) {
                // טבלת subscriptions לא קיימת - ניצור אותה
                create_subscriptions_table();
            }
        } catch (PDOException $e) {
            error_log("שגיאה בבדיקת טבלאות מנויים: " . $e->getMessage());
            // ממשיך בכל זאת
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
        
        // מציאת תוכנית בסיסית/חינמית
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE status = 'active' ORDER BY price ASC LIMIT 1");
        $stmt->execute();
        $plan = $stmt->fetch();
        
        if (!$plan) {
            // אם אין תוכניות, ניצור תוכנית ברירת מחדל
            try {
                create_default_plan();
                // נסה שוב למצוא תוכנית
                $stmt = $pdo->prepare("SELECT id FROM plans WHERE status = 'active' ORDER BY price ASC LIMIT 1");
                $stmt->execute();
                $plan = $stmt->fetch();
                
                if (!$plan) {
                    error_log("שגיאה ביצירת מנוי ניסיון: לא נמצאה תוכנית מתאימה וגם לא ניתן ליצור ברירת מחדל");
                    return false;
                }
            } catch (PDOException $e) {
                error_log("שגיאה ביצירת תוכנית ברירת מחדל: " . $e->getMessage());
                return false;
            }
        }
        
        $plan_id = $plan['id'];
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$trial_days} days"));
        
        // בדיקת המבנה של טבלת subscriptions
        $columns_check = $pdo->query("SHOW COLUMNS FROM subscriptions");
        $columns = [];
        while ($column = $columns_check->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        
        // בדיקת הטיפוס של עמודת payment_status
        $payment_status_type = "";
        if (in_array('payment_status', $columns)) {
            $stmt = $pdo->query("SHOW COLUMNS FROM subscriptions WHERE Field = 'payment_status'");
            $column_info = $stmt->fetch(PDO::FETCH_ASSOC);
            $payment_status_type = $column_info['Type'];
        }
        
        // יצירת מנוי ניסיון בהתאם למבנה הטבלה הקיים
        if (in_array('payment_status', $columns) && in_array('renewal_date', $columns)) {
            // מבנה הטבלה החדש - בדיקה אם payment_status הוא enum ושימוש בערך תקף
            if (stripos($payment_status_type, "enum") !== false && stripos($payment_status_type, "'trial'") === false) {
                // בדיקה אילו ערכים מותרים ובחירת ערך ברירת מחדל
                preg_match_all("/'([^']+)'/", $payment_status_type, $matches);
                $allowed_values = $matches[1];
                $default_status = !empty($allowed_values) ? $allowed_values[0] : 'active';
                
                $stmt = $pdo->prepare("
                    INSERT INTO subscriptions (
                        user_id, plan_id, status, payment_status, start_date, end_date,
                        renewal_date, created_at, updated_at
                    ) VALUES (
                        ?, ?, 'active', ?, ?, ?, ?, NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    $user_id,
                    $plan_id,
                    $default_status,
                    $start_date,
                    $end_date,
                    $end_date // renewal_date זהה לתאריך סיום במקרה של ניסיון
                ]);
            } else {
                // אם payment_status אינו enum או שהוא מכיל 'trial'
                $stmt = $pdo->prepare("
                    INSERT INTO subscriptions (
                        user_id, plan_id, status, payment_status, start_date, end_date,
                        renewal_date, created_at, updated_at
                    ) VALUES (
                        ?, ?, 'active', 'trial', ?, ?, ?, NOW(), NOW()
                    )
                ");
                
                $stmt->execute([
                    $user_id,
                    $plan_id,
                    $start_date,
                    $end_date,
                    $end_date // renewal_date זהה לתאריך סיום במקרה של ניסיון
                ]);
            }
        } else {
            // מבנה הטבלה המקורי
            $stmt = $pdo->prepare("
                INSERT INTO subscriptions (
                    user_id, plan_id, status, start_date, end_date,
                    auto_renew, payment_method, created_at
                ) VALUES (
                    ?, ?, 'active', ?, ?, 0, 'trial', NOW()
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

/**
 * יצירת טבלת plans אם לא קיימת
 */
function create_plans_table() {
    global $pdo;
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `plans` (
              `id` INT PRIMARY KEY AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `description` TEXT,
              `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
              `landing_pages_limit` INT NOT NULL DEFAULT 5,
              `contacts_limit` INT NOT NULL DEFAULT 500,
              `messages_limit` INT NOT NULL DEFAULT 1000,
              `views_limit` INT NOT NULL DEFAULT 5000,
              `custom_domain` TINYINT(1) NOT NULL DEFAULT 0,
              `api_access` TINYINT(1) NOT NULL DEFAULT 0,
              `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        return true;
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת טבלת plans: " . $e->getMessage());
        return false;
    }
}

/**
 * יצירת טבלת subscriptions אם לא קיימת
 */
function create_subscriptions_table() {
    global $pdo;
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `subscriptions` (
              `id` INT PRIMARY KEY AUTO_INCREMENT,
              `user_id` INT NOT NULL,
              `plan_id` INT NOT NULL,
              `status` ENUM('active', 'inactive', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
              `start_date` DATE NOT NULL,
              `end_date` DATE NOT NULL,
              `payment_status` VARCHAR(50) DEFAULT 'trial',
              `renewal_date` DATE NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        return true;
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת טבלת subscriptions: " . $e->getMessage());
        return false;
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
        
        // יצירת תוכנית מתקדמת
        $stmt = $pdo->prepare("
            INSERT INTO plans (
                name, description, price, landing_pages_limit, 
                contacts_limit, messages_limit, views_limit,
                custom_domain, api_access, status
            ) VALUES (
                'מסלול עסקי', 'מסלול מתקדם לעסקים בינוניים', 199.00, 15, 
                2000, 5000, 20000, 1, 0, 'active'
            )
        ");
        $stmt->execute();
        
        // יצירת תוכנית פרמיום
        $stmt = $pdo->prepare("
            INSERT INTO plans (
                name, description, price, landing_pages_limit, 
                contacts_limit, messages_limit, views_limit,
                custom_domain, api_access, status
            ) VALUES (
                'מסלול פרימיום', 'מסלול פרימיום ללא מגבלות', 499.00, 0, 
                0, 0, 0, 1, 1, 'active'
            )
        ");
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        error_log("שגיאה ביצירת תוכנית ברירת מחדל: " . $e->getMessage());
        return false;
    }
}
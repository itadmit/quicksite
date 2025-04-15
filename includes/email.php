<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * שליחת אימייל
 * 
 * @param string $to כתובת הנמען
 * @param string $subject נושא האימייל
 * @param string $message תוכן האימייל (HTML)
 * @param string $from_email כתובת השולח (אופציונלי)
 * @param string $from_name שם השולח (אופציונלי)
 * @param array $attachments קבצים מצורפים (אופציונלי)
 * @return bool האם השליחה הצליחה
 */
function send_email($to, $subject, $message, $from_email = '', $from_name = '', $attachments = []) {
    // אם לא צוינה כתובת שולח, השתמש בכתובת ברירת המחדל
    if (empty($from_email)) {
        $from_email = ADMIN_EMAIL;
    }
    
    // אם לא צוין שם שולח, השתמש בשם האתר
    if (empty($from_name)) {
        $from_name = SITE_NAME;
    }
    
    // כותרות בסיסיות
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $from_email,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // הרכבת כותרות
    $email_headers = implode("\r\n", $headers);
    
    // אם מדובר בסביבת פיתוח, רק תדפיס את האימייל ואל תשלח
    if (ENVIRONMENT === 'development') {
        error_log("Would send email to: $to");
        error_log("Subject: $subject");
        error_log("Headers: " . print_r($headers, true));
        error_log("Message: $message");
        return true;
    }
    
    // שליחת האימייל בפועל
    return mail($to, $subject, $message, $email_headers);
}

/**
 * שליחת אימייל מקמפיין
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @param int $contact_id מזהה איש הקשר
 * @param string $email_content תוכן האימייל
 * @param string $subject נושא האימייל
 * @param int $user_id מזהה המשתמש השולח
 * @return bool|array האם השליחה הצליחה או שגיאה
 */
function send_campaign_email($campaign_id, $contact_id, $email_content, $subject, $user_id) {
    global $pdo;
    
    try {
        // שליפת פרטי איש הקשר
        $contact_stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
        $contact_stmt->execute([$contact_id, $user_id]);
        $contact = $contact_stmt->fetch();
        
        if (!$contact) {
            return ['error' => 'איש הקשר לא נמצא'];
        }
        
        // שליפת פרטי המשתמש השולח
        $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user = $user_stmt->fetch();
        
        if (!$user) {
            return ['error' => 'המשתמש לא נמצא'];
        }
        
        // פרסונליזציה של תוכן האימייל עם פרטי איש הקשר
        $personalized_content = personalize_email_content($email_content, $contact);
        
        // שליחת האימייל
        $from_email = $user['email'];
        $from_name = $user['first_name'] . ' ' . $user['last_name'];
        
        if (!empty($user['company_name'])) {
            $from_name = $user['company_name'];
        }
        
        $sent = send_email($contact['email'], $subject, $personalized_content, $from_email, $from_name);
        
        if ($sent) {
            // תיעוד השליחה במסד הנתונים
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, sent_at) 
                VALUES (?, ?, 'email', 'sent', NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id]);
            
            // עדכון סטטיסטיקות שימוש חודשי
            update_usage_stats($user_id, 'email_sent', 1);
            
            return true;
        } else {
            // תיעוד שגיאה
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, error_message, sent_at) 
                VALUES (?, ?, 'email', 'failed', 'שגיאה בשליחת האימייל', NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id]);
            
            return ['error' => 'שגיאה בשליחת האימייל'];
        }
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליחת אימייל קמפיין: " . $e->getMessage());
        return ['error' => 'שגיאה בשליחת אימייל. אנא נסה שוב.'];
    }
}

/**
 * עדכון סטטיסטיקות שימוש
 * 
 * @param int $user_id מזהה המשתמש
 * @param string $field שדה לעדכון (email_sent/sms_sent/whatsapp_sent/contacts_count)
 * @param int $increment כמות לתוספת
 * @return bool האם העדכון הצליח
 */
function update_usage_stats($user_id, $field, $increment = 1) {
    global $pdo;
    
    $current_month = date('n');
    $current_year = date('Y');
    
    try {
        // בדיקה אם יש כבר רשומה לחודש הנוכחי
        $stmt = $pdo->prepare("
            SELECT id FROM usage_stats 
            WHERE user_id = ? AND month = ? AND year = ?
        ");
        $stmt->execute([$user_id, $current_month, $current_year]);
        $stats_id = $stmt->fetchColumn();
        
        if ($stats_id) {
            // עדכון רשומה קיימת
            $update = $pdo->prepare("
                UPDATE usage_stats 
                SET {$field} = {$field} + ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $update->execute([$increment, $stats_id]);
        } else {
            // יצירת רשומה חדשה
            $fields = ['user_id', 'month', 'year', $field];
            $values = [$user_id, $current_month, $current_year, $increment];
            
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            
            $insert = $pdo->prepare("
                INSERT INTO usage_stats 
                (" . implode(', ', $fields) . ") 
                VALUES ({$placeholders})
            ");
            $insert->execute($values);
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("שגיאה בעדכון סטטיסטיקות שימוש: " . $e->getMessage());
        return false;
    }
}

/**
 * ביצוע פרסונליזציה לתוכן אימייל
 * 
 * @param string $content תוכן האימייל המקורי
 * @param array $contact פרטי איש הקשר
 * @return string תוכן אימייל לאחר החלפת תגים
 */
function personalize_email_content($content, $contact) {
    // רשימת התגים לפרסונליזציה
    $tags = [
        '{{first_name}}' => $contact['first_name'] ?? '',
        '{{last_name}}' => $contact['last_name'] ?? '',
        '{{email}}' => $contact['email'] ?? '',
        '{{phone}}' => $contact['phone'] ?? '',
        '{{full_name}}' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: 'לקוח יקר'
    ];
    
    // החלפת התגים הבסיסיים
    $personalized_content = str_replace(array_keys($tags), array_values($tags), $content);
    
    // החלפת תגים של שדות מותאמים אישית אם יש
    if (isset($contact['custom_fields']) && is_array($contact['custom_fields'])) {
        foreach ($contact['custom_fields'] as $field_name => $value) {
            $personalized_content = str_replace("{{" . $field_name . "}}", $value, $personalized_content);
        }
    }
    
    // החלפת תגים שלא הוחלפו בערכים ריקים
    $personalized_content = preg_replace('/\{\{[^}]+\}\}/', '', $personalized_content);
    
    return $personalized_content;
}

/**
 * שליחת קמפיין אימייל
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @return array תוצאות השליחה
 */
function send_email_campaign($campaign_id) {
    global $pdo;
    
    try {
        // שליפת פרטי הקמפיין
        $campaign_stmt = $pdo->prepare("
            SELECT c.*, 
                   mt.subject, mt.content,
                   u.id as user_id, u.email as user_email,
                   u.first_name as user_first_name, u.last_name as user_last_name,
                   u.company_name as user_company_name
            FROM campaigns c
            JOIN message_templates mt ON c.template_id = mt.id
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ? AND c.type = 'email'
            LIMIT 1
        ");
        $campaign_stmt->execute([$campaign_id]);
        $campaign = $campaign_stmt->fetch();
        
        if (!$campaign) {
            return ['error' => 'הקמפיין לא נמצא או אינו קמפיין אימייל'];
        }
        
        // בדיקה שהקמפיין מורשה לשליחה
        if ($campaign['status'] !== 'scheduled' && $campaign['status'] !== 'sending') {
            return ['error' => 'סטטוס הקמפיין אינו מאפשר שליחה'];
        }
        
        // עדכון סטטוס הקמפיין ל"נשלח"
        $update_stmt = $pdo->prepare("
            UPDATE campaigns 
            SET status = 'sending', updated_at = NOW() 
            WHERE id = ?
        ");
        $update_stmt->execute([$campaign_id]);
        
        // שליפת אנשי הקשר ברשימה
        $contacts_stmt = $pdo->prepare("
            SELECT c.* 
            FROM contacts c
            JOIN list_contacts lc ON c.id = lc.contact_id
            WHERE lc.list_id = ? AND c.status = 'active'
        ");
        $contacts_stmt->execute([$campaign['list_id']]);
        $contacts = $contacts_stmt->fetchAll();
        
        $results = [
            'total' => count($contacts),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // שליחת האימייל לכל אנשי הקשר
        foreach ($contacts as $contact) {
            $result = send_campaign_email(
                $campaign_id,
                $contact['id'],
                $campaign['content'],
                $campaign['subject'],
                $campaign['user_id']
            );
            
            if ($result === true) {
                $results['sent']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'email' => $contact['email'],
                    'status' => 'sent'
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'email' => $contact['email'],
                    'status' => 'failed',
                    'error' => $result['error'] ?? 'שגיאה לא ידועה'
                ];
            }
        }
        
        // עדכון סטטוס הקמפיין ל"נשלח"
        $update_stmt = $pdo->prepare("
            UPDATE campaigns 
            SET status = 'sent', sent_at = NOW(), updated_at = NOW() 
            WHERE id = ?
        ");
        $update_stmt->execute([$campaign_id]);
        
        return $results;
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליחת קמפיין אימייל: " . $e->getMessage());
        
        // עדכון סטטוס הקמפיין ל"נכשל"
        try {
            $update_stmt = $pdo->prepare("
                UPDATE campaigns 
                SET status = 'cancelled', updated_at = NOW() 
                WHERE id = ?
            ");
            $update_stmt->execute([$campaign_id]);
        } catch (PDOException $e2) {
            error_log("שגיאה בעדכון סטטוס קמפיין: " . $e2->getMessage());
        }
        
        return ['error' => 'שגיאה בשליחת הקמפיין. אנא נסה שוב מאוחר יותר.'];
    }
}

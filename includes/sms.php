<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * שליחת הודעת SMS באמצעות Twilio
 * 
 * @param string $to מספר הטלפון של הנמען (בפורמט בינלאומי)
 * @param string $message תוכן ההודעה
 * @param int $user_id מזהה המשתמש השולח
 * @return array תוצאת השליחה
 */
function send_sms($to, $message, $user_id = null) {
    // אם מדובר בסביבת פיתוח, רק תדפיס את ההודעה ואל תשלח
    if (ENVIRONMENT === 'development') {
        error_log("Would send SMS to: $to");
        error_log("Message: $message");
        return [
            'success' => true,
            'sid' => 'DEV_' . md5($to . $message . time()),
            'status' => 'sent'
        ];
    }
    
    // בדיקה אם יש הגדרות API מותאמות אישית למשתמש
    $twilio_sid = TWILIO_SID;
    $twilio_token = TWILIO_TOKEN;
    $from_number = null;
    
    if ($user_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT api_key, api_secret, additional_settings 
                FROM api_connections 
                WHERE user_id = ? AND service = 'twilio' AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $api_connection = $stmt->fetch();
            
            if ($api_connection) {
                $twilio_sid = $api_connection['api_key'];
                $twilio_token = $api_connection['api_secret'];
                
                $additional_settings = json_decode($api_connection['additional_settings'], true);
                if (isset($additional_settings['from_number'])) {
                    $from_number = $additional_settings['from_number'];
                }
            }
        } catch (PDOException $e) {
            error_log("שגיאה בשליפת הגדרות API: " . $e->getMessage());
        }
    }
    
    // התחלת טעינת SDK של Twilio אם לא נטען כבר
    if (!class_exists('Twilio\Rest\Client')) {
        // בדיקה אם קובץ Composer Autoload קיים
        $autoload_path = __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
        } else {
            return [
                'success' => false,
                'error' => 'Twilio SDK לא נמצא. אנא התקן את חבילת PHP של Twilio.'
            ];
        }
    }
    
    try {
        // יצירת לקוח Twilio
        $client = new \Twilio\Rest\Client($twilio_sid, $twilio_token);
        
        // שליחת ההודעה
        $message_params = [
            'body' => $message
        ];
        
        // אם יש מספר שליחה מוגדר, הוסף אותו
        if ($from_number) {
            $message_params['from'] = $from_number;
        }
        
        $sms = $client->messages->create(
            $to,
            $message_params
        );
        
        return [
            'success' => true,
            'sid' => $sms->sid,
            'status' => $sms->status
        ];
        
    } catch (\Exception $e) {
        error_log("שגיאת Twilio: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * פרסונליזציה של תוכן הודעת SMS
 * 
 * @param string $content תוכן ההודעה המקורי
 * @param array $contact פרטי איש הקשר
 * @return string תוכן ההודעה לאחר החלפת תגים
 */
function personalize_sms_content($content, $contact) {
    // רשימת התגים לפרסונליזציה
    $tags = [
        '{{first_name}}' => $contact['first_name'] ?? '',
        '{{last_name}}' => $contact['last_name'] ?? '',
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
 * שליחת הודעת SMS מקמפיין
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @param int $contact_id מזהה איש הקשר
 * @param string $message תוכן ההודעה
 * @param int $user_id מזהה המשתמש השולח
 * @return array תוצאת השליחה
 */
function send_campaign_sms($campaign_id, $contact_id, $message, $user_id) {
    global $pdo;
    
    try {
        // שליפת פרטי איש הקשר
        $contact_stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
        $contact_stmt->execute([$contact_id, $user_id]);
        $contact = $contact_stmt->fetch();
        
        if (!$contact) {
            return ['success' => false, 'error' => 'איש הקשר לא נמצא'];
        }
        
        // בדיקה שיש מספר טלפון
        if (empty($contact['phone'])) {
            return ['success' => false, 'error' => 'אין מספר טלפון רשום לאיש הקשר'];
        }
        
        // פרסונליזציה של תוכן ההודעה
        $personalized_message = personalize_sms_content($message, $contact);
        
        // שליחת ה-SMS
        $result = send_sms($contact['phone'], $personalized_message, $user_id);
        
        // תיעוד השליחה במסד הנתונים
        if ($result['success']) {
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, sent_at) 
                VALUES (?, ?, 'sms', 'sent', NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id]);
            
            // עדכון סטטיסטיקות שימוש חודשי
            update_usage_stats($user_id, 'sms_sent', 1);
        } else {
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, error_message, sent_at) 
                VALUES (?, ?, 'sms', 'failed', ?, NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id, $result['error'] ?? 'שגיאה לא ידועה']);
        }
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליחת SMS קמפיין: " . $e->getMessage());
        return ['success' => false, 'error' => 'שגיאה בשליחת SMS. אנא נסה שוב.'];
    }
}

/**
 * שליחת קמפיין SMS
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @return array תוצאות השליחה
 */
function send_sms_campaign($campaign_id) {
    global $pdo;
    
    try {
        // שליפת פרטי הקמפיין
        $campaign_stmt = $pdo->prepare("
            SELECT c.*, 
                   mt.content,
                   u.id as user_id
            FROM campaigns c
            JOIN message_templates mt ON c.template_id = mt.id
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ? AND c.type = 'sms'
            LIMIT 1
        ");
        $campaign_stmt->execute([$campaign_id]);
        $campaign = $campaign_stmt->fetch();
        
        if (!$campaign) {
            return ['error' => 'הקמפיין לא נמצא או אינו קמפיין SMS'];
        }
        
        // בדיקה שהקמפיין מורשה לשליחה
        if ($campaign['status'] !== 'scheduled' && $campaign['status'] !== 'sending') {
            return ['error' => 'סטטוס הקמפיין אינו מאפשר שליחה'];
        }
        
        // עדכון סטטוס הקמפיין ל"בשליחה"
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
            WHERE lc.list_id = ? AND c.status = 'active' AND c.phone != ''
        ");
        $contacts_stmt->execute([$campaign['list_id']]);
        $contacts = $contacts_stmt->fetchAll();
        
        $results = [
            'total' => count($contacts),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // שליחת ה-SMS לכל אנשי הקשר
        foreach ($contacts as $contact) {
            $result = send_campaign_sms(
                $campaign_id,
                $contact['id'],
                $campaign['content'],
                $campaign['user_id']
            );
            
            if ($result['success']) {
                $results['sent']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'phone' => $contact['phone'],
                    'status' => 'sent',
                    'sid' => $result['sid'] ?? ''
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'phone' => $contact['phone'],
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
        error_log("שגיאה בשליחת קמפיין SMS: " . $e->getMessage());
        
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

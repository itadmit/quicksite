<?php
// ודא שהקובץ לא נגיש ישירות
if (!defined('QUICKSITE')) {
    die('גישה ישירה לקובץ זה אסורה');
}

/**
 * שליחת הודעת WhatsApp באמצעות RappelSend
 * 
 * @param string $to מספר הטלפון של הנמען (בפורמט בינלאומי)
 * @param string $message תוכן ההודעה
 * @param array $media קובץ מדיה לשליחה (אופציונלי)
 * @param int $user_id מזהה המשתמש השולח
 * @return array תוצאת השליחה
 */
function send_whatsapp($to, $message, $media = null, $user_id = null) {
    // אם מדובר בסביבת פיתוח, רק תדפיס את ההודעה ואל תשלח
    if (ENVIRONMENT === 'development') {
        error_log("Would send WhatsApp to: $to");
        error_log("Message: $message");
        if ($media) {
            error_log("Media: " . print_r($media, true));
        }
        return [
            'success' => true,
            'message_id' => 'DEV_' . md5($to . $message . time()),
            'status' => 'sent'
        ];
    }
    
    // בדיקה אם יש הגדרות API מותאמות אישית למשתמש
    $api_key = RAPPELSEND_API_KEY;
    $api_endpoint = 'https://api.rappelsend.com/v1/messages';
    
    if ($user_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT api_key, additional_settings 
                FROM api_connections 
                WHERE user_id = ? AND service = 'rappelsend' AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $api_connection = $stmt->fetch();
            
            if ($api_connection) {
                $api_key = $api_connection['api_key'];
                
                $additional_settings = json_decode($api_connection['additional_settings'], true);
                if (isset($additional_settings['api_endpoint'])) {
                    $api_endpoint = $additional_settings['api_endpoint'];
                }
            }
        } catch (PDOException $e) {
            error_log("שגיאה בשליפת הגדרות API: " . $e->getMessage());
        }
    }
    
    // בניית הבקשה
    $data = [
        'to' => $to,
        'type' => $media ? 'media' : 'text',
    ];
    
    if ($media) {
        $data['media'] = [
            'url' => $media['url'],
            'caption' => $message
        ];
    } else {
        $data['text'] = [
            'body' => $message
        ];
    }
    
    // יצירת HTTP client
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    // שליחת הבקשה
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // פיענוח התגובה
    $response_data = json_decode($response, true);
    
    if ($status_code == 200 && isset($response_data['success']) && $response_data['success']) {
        return [
            'success' => true,
            'message_id' => $response_data['message_id'] ?? '',
            'status' => $response_data['status'] ?? 'sent'
        ];
    } else {
        error_log("שגיאת RappelSend: " . $response);
        
        return [
            'success' => false,
            'error' => $response_data['error'] ?? 'שגיאה לא ידועה בשליחת הודעת WhatsApp'
        ];
    }
}

/**
 * פרסונליזציה של תוכן הודעת WhatsApp
 * 
 * @param string $content תוכן ההודעה המקורי
 * @param array $contact פרטי איש הקשר
 * @return string תוכן ההודעה לאחר החלפת תגים
 */
function personalize_whatsapp_content($content, $contact) {
    // רשימת התגים לפרסונליזציה
    $tags = [
        '{{first_name}}' => $contact['first_name'] ?? '',
        '{{last_name}}' => $contact['last_name'] ?? '',
        '{{phone}}' => $contact['phone'] ?? '',
        '{{whatsapp}}' => $contact['whatsapp'] ?? $contact['phone'] ?? '',
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
 * שליחת הודעת WhatsApp מקמפיין
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @param int $contact_id מזהה איש הקשר
 * @param string $message תוכן ההודעה
 * @param array $media מדיה לשליחה (אופציונלי)
 * @param int $user_id מזהה המשתמש השולח
 * @return array תוצאת השליחה
 */
function send_campaign_whatsapp($campaign_id, $contact_id, $message, $media = null, $user_id) {
    global $pdo;
    
    try {
        // שליפת פרטי איש הקשר
        $contact_stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
        $contact_stmt->execute([$contact_id, $user_id]);
        $contact = $contact_stmt->fetch();
        
        if (!$contact) {
            return ['success' => false, 'error' => 'איש הקשר לא נמצא'];
        }
        
        // בדיקה שיש מספר וואטסאפ או מספר טלפון
        $whatsapp_number = !empty($contact['whatsapp']) ? $contact['whatsapp'] : $contact['phone'];
        
        if (empty($whatsapp_number)) {
            return ['success' => false, 'error' => 'אין מספר WhatsApp רשום לאיש הקשר'];
        }
        
        // פרסונליזציה של תוכן ההודעה
        $personalized_message = personalize_whatsapp_content($message, $contact);
        
        // שליחת ההודעה
        $result = send_whatsapp($whatsapp_number, $personalized_message, $media, $user_id);
        
        // תיעוד השליחה במסד הנתונים
        if ($result['success']) {
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, sent_at) 
                VALUES (?, ?, 'whatsapp', 'sent', NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id]);
            
            // עדכון סטטיסטיקות שימוש חודשי
            update_usage_stats($user_id, 'whatsapp_sent', 1);
        } else {
            $log_stmt = $pdo->prepare("
                INSERT INTO sent_messages 
                (campaign_id, contact_id, type, status, error_message, sent_at) 
                VALUES (?, ?, 'whatsapp', 'failed', ?, NOW())
            ");
            $log_stmt->execute([$campaign_id, $contact_id, $result['error'] ?? 'שגיאה לא ידועה']);
        }
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("שגיאה בשליחת WhatsApp קמפיין: " . $e->getMessage());
        return ['success' => false, 'error' => 'שגיאה בשליחת WhatsApp. אנא נסה שוב.'];
    }
}

/**
 * שליחת קמפיין WhatsApp
 * 
 * @param int $campaign_id מזהה הקמפיין
 * @return array תוצאות השליחה
 */
function send_whatsapp_campaign($campaign_id) {
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
            WHERE c.id = ? AND c.type = 'whatsapp'
            LIMIT 1
        ");
        $campaign_stmt->execute([$campaign_id]);
        $campaign = $campaign_stmt->fetch();
        
        if (!$campaign) {
            return ['error' => 'הקמפיין לא נמצא או אינו קמפיין WhatsApp'];
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
            WHERE lc.list_id = ? AND c.status = 'active' AND (c.whatsapp != '' OR c.phone != '')
        ");
        $contacts_stmt->execute([$campaign['list_id']]);
        $contacts = $contacts_stmt->fetchAll();
        
        $results = [
            'total' => count($contacts),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        // שליחת ההודעות לכל אנשי הקשר
        foreach ($contacts as $contact) {
            $result = send_campaign_whatsapp(
                $campaign_id,
                $contact['id'],
                $campaign['content'],
                null, // אין מדיה בקמפיין רגיל
                $campaign['user_id']
            );
            
            if ($result['success']) {
                $results['sent']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'phone' => !empty($contact['whatsapp']) ? $contact['whatsapp'] : $contact['phone'],
                    'status' => 'sent',
                    'message_id' => $result['message_id'] ?? ''
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'contact_id' => $contact['id'],
                    'phone' => !empty($contact['whatsapp']) ? $contact['whatsapp'] : $contact['phone'],
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
        error_log("שגיאה בשליחת קמפיין WhatsApp: " . $e->getMessage());
        
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

/**
 * בדיקת מספר וואטסאפ
 * 
 * @param string $number מספר הטלפון לבדיקה
 * @param int $user_id מזהה המשתמש
 * @return array תוצאת הבדיקה
 */
function check_whatsapp_number($number, $user_id = null) {
    // בסביבת פיתוח תמיד החזר חיובי
    if (ENVIRONMENT === 'development') {
        return [
            'success' => true,
            'exists' => true,
            'number' => $number
        ];
    }
    
    // בדיקה אם יש הגדרות API מותאמות אישית למשתמש
    $api_key = RAPPELSEND_API_KEY;
    $api_endpoint = 'https://api.rappelsend.com/v1/numbers/check';
    
    if ($user_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT api_key, additional_settings 
                FROM api_connections 
                WHERE user_id = ? AND service = 'rappelsend' AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $api_connection = $stmt->fetch();
            
            if ($api_connection) {
                $api_key = $api_connection['api_key'];
                
                $additional_settings = json_decode($api_connection['additional_settings'], true);
                if (isset($additional_settings['api_endpoint'])) {
                    $api_endpoint = rtrim($additional_settings['api_endpoint'], '/') . '/numbers/check';
                }
            }
        } catch (PDOException $e) {
            error_log("שגיאה בשליפת הגדרות API: " . $e->getMessage());
        }
    }
    
    // בניית הבקשה
    $data = [
        'number' => $number
    ];
    
    // יצירת HTTP client
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    // שליחת הבקשה
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // פיענוח התגובה
    $response_data = json_decode($response, true);
    
    if ($status_code == 200 && isset($response_data['success']) && $response_data['success']) {
        return [
            'success' => true,
            'exists' => $response_data['exists'] ?? false,
            'number' => $response_data['number'] ?? $number
        ];
    } else {
        error_log("שגיאת RappelSend בבדיקת מספר: " . $response);
        
        return [
            'success' => false,
            'error' => $response_data['error'] ?? 'שגיאה לא ידועה בבדיקת מספר WhatsApp'
        ];
    }
}

<?php
/**
 * Save Template API
 * 
 * Handles saving template data to the database
 */

// הגדרת סוג התוכן ל-JSON
header('Content-Type: application/json');

// אפשור CORS עבור פיתוח
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// הפעלת שגיאות PHP לדיבאג
ini_set('display_errors', 1);
error_reporting(E_ALL);

// כתיבת לוג דיבאג
$debug_log = fopen(__DIR__ . '/../data/save_debug.log', 'a');
fwrite($debug_log, "--- " . date('Y-m-d H:i:s') . " ---\n");
fwrite($debug_log, "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n");

// טיפול בבקשת OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    fwrite($debug_log, "OPTIONS request received, returning 200\n");
    fclose($debug_log);
    exit;
}

// ודא שזו בקשת POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fwrite($debug_log, "Invalid request method: " . $_SERVER['REQUEST_METHOD'] . "\n");
    fclose($debug_log);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are accepted'
    ]);
    exit;
}

// קבלת JSON מגוף הבקשה
$json = file_get_contents('php://input');
fwrite($debug_log, "Received JSON length: " . strlen($json) . "\n");

// פענוח ה-JSON
$data = json_decode($json, true);

// בדיקה שה-JSON תקין
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite($debug_log, "JSON decode error: " . json_last_error_msg() . "\n");
    fclose($debug_log);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// ודא שקיימים page_id ו-sections
if (!isset($data['page_id']) || !isset($data['sections'])) {
    fwrite($debug_log, "Missing required fields\n");
    fwrite($debug_log, "page_id exists: " . (isset($data['page_id']) ? 'yes' : 'no') . "\n");
    fwrite($debug_log, "sections exists: " . (isset($data['sections']) ? 'yes' : 'no') . "\n");
    fclose($debug_log);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: page_id and/or sections'
    ]);
    exit;
}

// התחברות למסד הנתונים
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );

    // המרת הנתונים ל-JSON
    $json_output = json_encode($data['sections'], JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Failed to encode data to JSON: ' . json_last_error_msg());
    }

    // בדיקה אם כבר קיים רשומה עבור דף זה
    $stmt = $pdo->prepare("SELECT id FROM landing_page_contents WHERE landing_page_id = ?");
    $stmt->execute([$data['page_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // עדכון הרשומה הקיימת
        $stmt = $pdo->prepare("
            UPDATE landing_page_contents 
            SET content = ?, 
                updated_at = NOW(),
                version = version + 1,
                is_current = 1
            WHERE landing_page_id = ?
        ");
        $success = $stmt->execute([$json_output, $data['page_id']]);
    } else {
        // יצירת רשומה חדשה
        $stmt = $pdo->prepare("
            INSERT INTO landing_page_contents 
            (landing_page_id, content, created_at, updated_at, version, is_current) 
            VALUES (?, ?, NOW(), NOW(), 1, 1)
        ");
        $success = $stmt->execute([$data['page_id'], $json_output]);
    }

    if (!$success) {
        throw new Exception('Failed to save data to database');
    }

    // החזרת הצלחה
    fwrite($debug_log, "Save completed successfully\n");
    fclose($debug_log);

    echo json_encode([
        'success' => true,
        'message' => 'Template saved successfully to database'
    ]);

} catch (Exception $e) {
    fwrite($debug_log, "Error: " . $e->getMessage() . "\n");
    fclose($debug_log);
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save template: ' . $e->getMessage()
    ]);
    exit;
} 
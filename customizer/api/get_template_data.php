<?php
/**
 * Get Template Data API
 * 
 * Handles retrieving template data from the database
 */

// Set content type to JSON
header('Content-Type: application/json');

// Allow CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Make sure this is a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Only GET requests are accepted'
    ]);
    exit;
}

// Get page_id from query string
$page_id = isset($_GET['page_id']) ? (int) $_GET['page_id'] : 0;

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

    // קריאת הנתונים מהמסד נתונים
    $stmt = $pdo->prepare("
        SELECT content 
        FROM landing_page_contents 
        WHERE landing_page_id = ? 
        AND is_current = 1 
        ORDER BY version DESC 
        LIMIT 1
    ");
    $stmt->execute([$page_id]);
    $result = $stmt->fetch();

    if (!$result) {
        // Return default empty template if no data found
        echo json_encode([
            'success' => true,
            'sections' => []
        ]);
        exit;
    }

    // פענוח ה-JSON
    $sections = json_decode($result['content'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON in database: ' . json_last_error_msg());
    }

    // Return data
    echo json_encode([
        'success' => true,
        'sections' => $sections
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get template data: ' . $e->getMessage()
    ]);
    exit;
} 
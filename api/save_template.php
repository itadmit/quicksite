<?php
/**
 * API Endpoint to save template data for a specific page
 * 
 * Accepts JSON data with sections configuration and saves it
 */

// Include necessary files - commented out temporarily for debugging
// require_once(__DIR__ . '/../config/config.php');

// Set headers
header('Content-Type: application/json');

// Security check
// TODO: Add proper authentication check here

// Get POST data
$input_data = file_get_contents('php://input');
$request_data = json_decode($input_data, true);

// Validate request
if (!$request_data || !isset($request_data['page_id']) || !isset($request_data['sections'])) {
    send_error('Invalid request data');
    exit;
}

$page_id = intval($request_data['page_id']);
$sections = $request_data['sections'];

// Save template data to database
function save_template_data($page_id, $sections) {
    // In a real implementation, this would save data to a database
    // For now, we'll just simulate success
    
    // Validate sections data
    foreach ($sections as $section_id => $section_data) {
        // Basic validation - could be expanded based on requirements
        if (!is_array($section_data)) {
            return [
                'success' => false,
                'message' => "Invalid data for section: $section_id"
            ];
        }
    }
    
    // In a real implementation, save to database here
    // For now, just return success
    return [
        'success' => true,
        'message' => 'Template saved successfully'
    ];
}

// Helper function to send error response
function send_error($message) {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}

// Save template data
$result = save_template_data($page_id, $sections);

// Return response
echo json_encode($result); 
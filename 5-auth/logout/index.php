<?php
// DL-14: Initialize session with consent support
require_once dirname(__DIR__) . '/../8-about-me/cookie-helper.php';
initializeSessionWithConsentSupport();

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear session data
    $_SESSION['loggedin'] = false;
    $_SESSION['user'] = [];
    session_destroy();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => '../login/'
    ]);
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'METHOD_NOT_ALLOWED',
    'message' => 'Method not allowed'
]);
?>

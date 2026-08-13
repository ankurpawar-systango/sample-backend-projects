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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // DL-14: Include consent state in session response
        $consentState = getConsentState();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'loggedin' => true,
            'user' => $_SESSION['user'],
            'cookie_consent' => $consentState,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'loggedin' => false,
            'message' => 'User not logged in',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'METHOD_NOT_ALLOWED',
    'message' => 'Method not allowed'
]);
?>

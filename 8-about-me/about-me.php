<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

/**
 * About Me Endpoint
 *
 * Returns detailed information about the platform and its features
 * This endpoint requires user authentication
 */

try {
    require_once 'config.php';

    $aboutMe = new AboutMe();
    $serverTime = $aboutMe->getTimestamp();

    // Build detailed response
    $response = [
        'status' => 'success',
        'message' => $aboutMe->getMessage(),
        'timestamp' => $serverTime,
        'platform' => [
            'name' => 'Sample Platform',
            'version' => ENDPOINT_VERSION,
            'service' => ENDPOINT_NAME,
            'operational' => $aboutMe->isOperational(),
            'responseTime' => $aboutMe->getResponseTime()
        ],
        'features' => [
            'authentication' => true,
            'cookie_consent' => true,
            'responsive_design' => true,
            'session_management' => true,
            'user_profiles' => true
        ],
        'cookie_policy' => [
            'essential' => true,
            'performance' => true,
            'preferences' => true,
            'description' => 'We use cookies to improve your experience and remember your preferences'
        ]
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred while retrieving about information',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

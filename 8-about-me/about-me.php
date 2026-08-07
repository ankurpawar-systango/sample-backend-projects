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
 * Returns information about the backend service
 */

try {
    $serverTime = date('Y-m-d H:i:s');

    $response = [
        'status' => 'success',
        'message' => 'This is a sample site',
        'timestamp' => $serverTime
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

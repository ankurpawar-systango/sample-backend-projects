<?php
/**
 * Health Status API Endpoint
 *
 * This endpoint returns the health status of the backend service.
 * Returns HTTP 200 with status: "ok" when the service is healthy.
 */

// Set response headers for JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed'
    ]);
    exit;
}

/**
 * Get the health status of the backend
 *
 * @return array Health status information
 */
function getHealthStatus() {
    return [
        'status' => 'ok',
        'timestamp' => date('c'),
        'service' => 'Backend Health Check',
        'version' => '1.0.0'
    ];
}

// Return health status as JSON
http_response_code(200);
echo json_encode(getHealthStatus());
exit;

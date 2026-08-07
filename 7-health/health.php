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
 * Health Check Endpoint
 *
 * Returns the health status of the backend service
 * Includes response time, timestamp, and service status
 */

$startTime = microtime(true);

try {
    // Check basic PHP environment
    $phpVersion = phpversion();
    $serverTime = date('Y-m-d H:i:s');

    // You can add more health checks here (database connectivity, etc.)
    // For now, we'll just return that the service is running

    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds

    $response = [
        'status' => 'healthy',
        'message' => 'Backend service is running',
        'timestamp' => $serverTime,
        'responseTime' => $responseTime . ' ms',
        'php_version' => $phpVersion,
        'uptime' => 'Service is operational'
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    $response = [
        'status' => 'unhealthy',
        'message' => 'An error occurred',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

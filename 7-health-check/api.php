<?php
/**
 * Health Check API Endpoint
 *
 * Returns the health status of the application in JSON format.
 * Endpoint: /api/health or /health
 */

// Set response header to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(json_encode(['status' => 'ok']));
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

// Get current timestamp in ISO 8601 format
$timestamp = date('c');

// Check various system health indicators
$health = [
    'status' => 'ok',
    'timestamp' => $timestamp,
    'uptime' => function_exists('php_uname') ? 'healthy' : 'unknown',
    'version' => [
        'php' => PHP_VERSION,
        'api' => '1.0.0'
    ]
];

// Add additional checks if needed
$health['checks'] = [
    'php_enabled' => true,
    'json_enabled' => extension_loaded('json'),
    'memory_usage' => [
        'current' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'
    ]
];

// Return health status as JSON
http_response_code(200);
echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
?>

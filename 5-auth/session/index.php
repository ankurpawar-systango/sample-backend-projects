<?php
session_start();

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
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'loggedin' => true,
            'user' => $_SESSION['user']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'loggedin' => false,
            'message' => 'User not logged in'
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

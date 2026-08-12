<?php
/**
 * Cookie Consent Endpoint
 *
 * Handles cookie consent preferences and policy information
 * Provides endpoints to get and update cookie preferences
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get cookie policy information
        handleGetRequest();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Save cookie preferences
        handlePostRequest();
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'An error occurred while processing cookie preferences',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(500);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * Handle GET requests - return cookie policy information
 */
function handleGetRequest() {
    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'cookiePolicy' => [
            'essential' => [
                'name' => 'Essential Cookies',
                'required' => true,
                'description' => 'Required for basic functionality and security',
                'purpose' => [
                    'Session management',
                    'Security',
                    'Basic site functionality'
                ]
            ],
            'performance' => [
                'name' => 'Performance Cookies',
                'required' => false,
                'description' => 'Help us understand how you use our site',
                'purpose' => [
                    'Usage analytics',
                    'Performance monitoring',
                    'Error tracking'
                ]
            ],
            'preferences' => [
                'name' => 'Preference Cookies',
                'required' => false,
                'description' => 'Remember your choices and settings',
                'purpose' => [
                    'User preferences',
                    'Language settings',
                    'Theme preferences'
                ]
            ]
        ],
        'privacyPolicyUrl' => '/privacy',
        'termsUrl' => '/terms',
        'contactEmail' => 'privacy@example.com',
        'lastUpdated' => '2025-01-01'
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Handle POST requests - save cookie preferences
 */
function handlePostRequest() {
    // Get request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate request
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON in request body',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    // Extract preferences
    $preferences = [
        'essential' => isset($data['essential']) ? (bool)$data['essential'] : true,
        'performance' => isset($data['performance']) ? (bool)$data['performance'] : false,
        'preferences' => isset($data['preferences']) ? (bool)$data['preferences'] : false,
        'timestamp' => date('Y-m-d H:i:s'),
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    // In a real application, you would save these to a database or file
    // For now, we'll just return confirmation

    $response = [
        'status' => 'success',
        'message' => 'Cookie preferences saved successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'saved_preferences' => $preferences,
        'nextReviewDate' => date('Y-m-d H:i:s', strtotime('+1 year'))
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

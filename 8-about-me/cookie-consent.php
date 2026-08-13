<?php
/**
 * Cookie Consent Endpoint
 *
 * Handles cookie consent preferences and policy information
 * Provides endpoints to get and update cookie preferences
 *
 * DL-1: Added consent-level validation and cookie segregation support
 *
 * Features:
 * - Cookie policy information (GET)
 * - Save cookie preferences (POST with action: save)
 * - Validate consent level before cookie operations (POST with action: validate)
 * - Returns 403 if user attempts to access cookies above their consent level
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Valid cookie categories
define('COOKIE_CATEGORIES', ['essential', 'performance', 'preferences']);

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
        // Get cookie policy information or current consent state
        handleGetRequest();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle save or validate actions
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
 * Handle GET requests - return cookie policy information or consent state
 */
function handleGetRequest() {
    // Check if requesting current consent state
    $requestState = isset($_GET['state']) && $_GET['state'] === 'true';

    if ($requestState) {
        // Return current consent state for server-side validation
        handleGetConsentState();
        return;
    }

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
                ],
                'examples' => ['PHPSESSID', 'csrf_token', 'session_id']
            ],
            'performance' => [
                'name' => 'Performance Cookies',
                'required' => false,
                'description' => 'Help us understand how you use our site',
                'purpose' => [
                    'Usage analytics',
                    'Performance monitoring',
                    'Error tracking'
                ],
                'examples' => ['_ga', '_gid', '_analytics']
            ],
            'preferences' => [
                'name' => 'Preference Cookies',
                'required' => false,
                'description' => 'Remember your choices and settings',
                'purpose' => [
                    'User preferences',
                    'Language settings',
                    'Theme preferences'
                ],
                'examples' => ['_theme', '_language', '_preferences']
            ]
        ],
        'privacyPolicyUrl' => '/privacy',
        'termsUrl' => '/terms',
        'contactEmail' => 'privacy@example.com',
        'lastUpdated' => '2025-01-01',
        'segregationEnabled' => true
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Handle GET request for current consent state
 * This endpoint can be used for server-side validation
 */
function handleGetConsentState() {
    // In a real application, this would read from a database or session
    // For now, we return the default state
    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'consentState' => [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ],
        'message' => 'Current consent state retrieved successfully'
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * Handle POST requests - save cookie preferences or validate consent
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

    // Route to appropriate handler based on action
    $action = isset($data['action']) ? $data['action'] : 'save';

    switch ($action) {
        case 'validate':
            handleValidateConsent($data);
            break;
        case 'save':
        default:
            handleSavePreferences($data);
            break;
    }
}

/**
 * Validate if a cookie operation is allowed based on user's consent level
 * Returns 403 if consent not given for the requested category
 */
function handleValidateConsent($data) {
    $consentLevel = isset($data['consent_level']) ? $data['consent_level'] : null;
    $currentConsent = isset($data['current_consent']) ? $data['current_consent'] : null;

    // Validate required fields
    if (!$consentLevel) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'consent_level is required for validation',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    // Validate consent level is a known category
    if (!in_array($consentLevel, COOKIE_CATEGORIES)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid consent_level. Must be one of: ' . implode(', ', COOKIE_CATEGORIES),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    // Essential cookies are always allowed
    if ($consentLevel === 'essential') {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'allowed' => true,
            'message' => 'Essential cookies are always allowed',
            'category' => $consentLevel,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit();
    }

    // Check if user has given consent for the requested category
    $hasConsent = false;
    if ($currentConsent && isset($currentConsent[$consentLevel])) {
        $hasConsent = (bool)$currentConsent[$consentLevel];
    }

    if (!$hasConsent) {
        // User has not given consent for this category
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'allowed' => false,
            'message' => "Consent not given for {$consentLevel} cookies. Please update your cookie preferences.",
            'category' => $consentLevel,
            'requiredAction' => 'update_preferences',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit();
    }

    // Consent is given
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'allowed' => true,
        'message' => "Consent verified for {$consentLevel} cookies",
        'category' => $consentLevel,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

/**
 * Save cookie preferences
 */
function handleSavePreferences($data) {
    // Extract preferences
    $preferences = [
        'essential' => true, // Always true, cannot be disabled
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
        'nextReviewDate' => date('Y-m-d H:i:s', strtotime('+1 year')),
        'segregationEnforced' => true
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

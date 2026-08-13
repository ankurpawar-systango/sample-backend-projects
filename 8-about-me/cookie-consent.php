<?php
/**
 * Cookie Consent Endpoint
 *
 * Handles cookie consent preferences and policy information
 * Provides endpoints to get and update cookie preferences
 *
 * DL-1: Added consent-level validation and cookie segregation support
 * DL-22: Added permission-level-based cookie segregation
 *
 * Features:
 * - Cookie policy information (GET)
 * - Save cookie preferences (POST with action: save)
 * - Validate consent level before cookie operations (POST with action: validate)
 * - Returns 403 if user attempts to access cookies above their consent level
 * - Permission-level-based cookie segregation (DL-22)
 * - Retrieve permission-level information for each cookie category (DL-22)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// DL-5: Start session for persistent consent storage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valid cookie categories
define('COOKIE_CATEGORIES', ['essential', 'performance', 'preferences']);

// DL-22: Permission levels for cookie categories
// Maps cookie categories to required permission levels
define('COOKIE_PERMISSION_LEVELS', [
    'essential' => [
        'level' => 0,
        'name' => 'Public',
        'description' => 'No permission required - always available'
    ],
    'performance' => [
        'level' => 1,
        'name' => 'Basic User',
        'description' => 'Requires basic user authentication'
    ],
    'preferences' => [
        'level' => 2,
        'name' => 'Premium User',
        'description' => 'Requires premium user status'
    ]
]);

// DL-22: Default user permission level (if not specified, treat as public)
define('DEFAULT_USER_PERMISSION_LEVEL', 0);

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
 *
 * DL-22: Includes permission-level information for each cookie category
 */
function handleGetRequest() {
    // Check if requesting current consent state
    $requestState = isset($_GET['state']) && $_GET['state'] === 'true';

    if ($requestState) {
        // Return current consent state for server-side validation
        handleGetConsentState();
        return;
    }

    // DL-22: Check if requesting permission levels info
    $requestPermissions = isset($_GET['permissions']) && $_GET['permissions'] === 'true';
    if ($requestPermissions) {
        handleGetPermissionLevels();
        return;
    }

    // DL-22: Build cookie policy with permission level info
    $permissionLevels = getPermissionLevelDefinitions();

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
                'examples' => ['PHPSESSID', 'csrf_token', 'session_id'],
                // DL-22: Permission level info
                'permissionLevel' => $permissionLevels['essential']
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
                'examples' => ['_ga', '_gid', '_analytics'],
                // DL-22: Permission level info
                'permissionLevel' => $permissionLevels['performance']
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
                'examples' => ['_theme', '_language', '_preferences'],
                // DL-22: Permission level info
                'permissionLevel' => $permissionLevels['preferences']
            ]
        ],
        'privacyPolicyUrl' => '/privacy',
        'termsUrl' => '/terms',
        'contactEmail' => 'privacy@example.com',
        'lastUpdated' => '2025-01-01',
        'segregationEnabled' => true,
        // DL-22: Include permission level segregation flag
        'permissionLevelSegregationEnabled' => true,
        'availablePermissionLevels' => array_values(array_unique(array_map(function($perm) {
            return $perm['level'];
        }, $permissionLevels)))
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Handle GET request for current consent state
 * This endpoint can be used for server-side validation
 *
 * DL-5: Reads from persistent session storage
 */
function handleGetConsentState() {
    // Retrieve consent state from PHP session (persistent storage)
    $consentState = getConsentStateFromSession();

    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'consentState' => $consentState,
        'message' => 'Current consent state retrieved successfully'
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * Handle POST requests - save cookie preferences or validate consent
 *
 * DL-22: Added support for permission-level validation
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
        // DL-22: New action for permission-level validation
        case 'validate-permission':
            handleValidatePermissionLevel($data);
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
 *
 * DL-5: Now checks against persistent server-side consent state
 */
function handleValidateConsent($data) {
    $consentLevel = isset($data['consent_level']) ? $data['consent_level'] : null;

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

    // Get the server-side persisted consent state
    $persistedConsent = getConsentStateFromSession();

    // Check if user has given consent for the requested category
    $hasConsent = isset($persistedConsent[$consentLevel]) && (bool)$persistedConsent[$consentLevel];

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
 *
 * DL-5: Now persists consent state to server-side session storage
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

    // DL-5: Persist consent state to PHP session for server-side validation
    saveConsentStateToSession($preferences);

    $response = [
        'status' => 'success',
        'message' => 'Cookie preferences saved successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'saved_preferences' => $preferences,
        'nextReviewDate' => date('Y-m-d H:i:s', strtotime('+1 year')),
        'segregationEnforced' => true,
        'persistedToServer' => true
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * DL-5: Get consent state from persistent session storage
 * @return array Consent state with essential, performance, preferences flags
 */
function getConsentStateFromSession() {
    // Check if consent state exists in session
    if (isset($_SESSION['cookie_consent'])) {
        return [
            'essential' => $_SESSION['cookie_consent']['essential'] ?? true,
            'performance' => $_SESSION['cookie_consent']['performance'] ?? false,
            'preferences' => $_SESSION['cookie_consent']['preferences'] ?? false
        ];
    }

    // Default: only essential cookies allowed
    return [
        'essential' => true,
        'performance' => false,
        'preferences' => false
    ];
}

/**
 * DL-5: Save consent state to persistent session storage
 * @param array $consentData Array with essential, performance, preferences keys
 */
function saveConsentStateToSession($consentData) {
    // Validate input
    if (!is_array($consentData)) {
        return false;
    }

    // Store in session with timestamp
    $_SESSION['cookie_consent'] = [
        'essential' => $consentData['essential'] ?? true,
        'performance' => $consentData['performance'] ?? false,
        'preferences' => $consentData['preferences'] ?? false,
        'saved_at' => date('Y-m-d H:i:s'),
        'user_agent' => $consentData['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    return true;
}

/**
 * DL-22: Get permission level definitions
 * @return array Permission level definitions for each cookie category
 */
function getPermissionLevelDefinitions() {
    $definitions = [];
    foreach (COOKIE_PERMISSION_LEVELS as $category => $info) {
        $definitions[$category] = $info;
    }
    return $definitions;
}

/**
 * DL-22: Handle GET request for permission levels information
 */
function handleGetPermissionLevels() {
    $permissionLevels = getPermissionLevelDefinitions();

    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'permissionLevels' => $permissionLevels,
        'message' => 'Permission level definitions retrieved successfully'
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

/**
 * DL-22: Get user's current permission level from session
 * @return int User's current permission level
 */
function getUserPermissionLevel() {
    // Check if user permission level is stored in session
    if (isset($_SESSION['user_permission_level'])) {
        return (int)$_SESSION['user_permission_level'];
    }

    // Default to public level
    return DEFAULT_USER_PERMISSION_LEVEL;
}

/**
 * DL-22: Set user's permission level in session
 * @param int $level User's permission level
 */
function setUserPermissionLevel($level) {
    $_SESSION['user_permission_level'] = (int)$level;
}

/**
 * DL-22: Validate if user has permission to access a cookie category
 * Checks both consent and permission level
 *
 * @param string $category Cookie category to validate
 * @param int $userPermissionLevel User's permission level
 * @return array Validation result with status, allowed, and message
 */
function validateCookieAccessByPermission($category, $userPermissionLevel) {
    // Validate category exists
    if (!in_array($category, COOKIE_CATEGORIES)) {
        return [
            'status' => 'error',
            'allowed' => false,
            'message' => 'Invalid cookie category: ' . $category,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // Get permission level for this category
    $permissionLevels = COOKIE_PERMISSION_LEVELS;
    $requiredLevel = $permissionLevels[$category]['level'] ?? 0;

    // Check if user's permission level meets requirement
    if ($userPermissionLevel < $requiredLevel) {
        return [
            'status' => 'error',
            'allowed' => false,
            'category' => $category,
            'requiredPermissionLevel' => $requiredLevel,
            'userPermissionLevel' => $userPermissionLevel,
            'message' => "Insufficient permission level for {$category} cookies. Required level: {$requiredLevel}, Your level: {$userPermissionLevel}",
            'requiredAction' => 'upgrade_permission',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // User has permission level, now check consent
    $consentState = getConsentStateFromSession();

    // Essential cookies are always allowed if permission level is met
    if ($category === 'essential') {
        return [
            'status' => 'success',
            'allowed' => true,
            'category' => $category,
            'message' => 'Essential cookies are always allowed with your permission level',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // For other categories, check both permission level and consent
    $hasConsent = isset($consentState[$category]) && (bool)$consentState[$category];

    if (!$hasConsent) {
        return [
            'status' => 'error',
            'allowed' => false,
            'category' => $category,
            'message' => "Permission level sufficient but consent not given for {$category} cookies",
            'requiredAction' => 'update_consent',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // Both permission level and consent are satisfied
    return [
        'status' => 'success',
        'allowed' => true,
        'category' => $category,
        'message' => "Full access allowed for {$category} cookies",
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * DL-22: Handle permission-level-based validation request
 * POST action: validate-permission
 * Required fields: cookie_category, user_permission_level (optional)
 *
 * @param array $data Request data
 */
function handleValidatePermissionLevel($data) {
    $category = isset($data['cookie_category']) ? $data['cookie_category'] : null;
    $userPermissionLevel = isset($data['user_permission_level'])
        ? (int)$data['user_permission_level']
        : getUserPermissionLevel();

    if (!$category) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'cookie_category is required for permission validation',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    // Validate the cookie access by permission level
    $validationResult = validateCookieAccessByPermission($category, $userPermissionLevel);

    if ($validationResult['status'] === 'error') {
        http_response_code(403);
    } else {
        http_response_code(200);
    }

    echo json_encode($validationResult, JSON_PRETTY_PRINT);
}

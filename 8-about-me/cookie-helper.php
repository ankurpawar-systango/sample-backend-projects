<?php
/**
 * Cookie Helper Utility
 *
 * Provides centralized cookie validation and management across the backend.
 * Enforces cookie segregation by consent level to ensure only allowed cookies
 * are set/accessed based on the user's consent state.
 *
 * DL-14: Core utility for cookie segregation validation on the backend
 */

// Valid cookie categories for segregation
const COOKIE_CATEGORIES = ['essential', 'performance', 'preferences'];

// Cookie category mappings - which cookies belong to which categories
const COOKIE_CATEGORY_MAP = [
    'essential' => [
        'PHPSESSID',
        'csrf_token',
        'session_id',
        'session_token',
        'auth_token',
        'user_id',
        'user_session'
    ],
    'performance' => [
        '_ga',
        '_gid',
        '_analytics',
        'analytics_session',
        'performance_tracking'
    ],
    'preferences' => [
        '_theme',
        '_language',
        '_preferences',
        'user_preferences',
        'theme_preference',
        'language_preference'
    ]
];

/**
 * Initialize and ensure session is started with cookie consent awareness
 *
 * DL-14: Must be called at the start of any backend script that uses $_SESSION
 * Ensures session state is available for consent validation
 */
function initializeSessionWithConsentSupport() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Initialize consent state if not already present
    if (!isset($_SESSION['cookie_consent'])) {
        $_SESSION['cookie_consent'] = [
            'essential' => true,    // Always enabled
            'performance' => false,
            'preferences' => false,
            'initialized_at' => date('Y-m-d H:i:s')
        ];
    }
}

/**
 * Get the current consent state from the session
 *
 * @return array Consent state with essential, performance, preferences flags
 */
function getConsentState() {
    if (!isset($_SESSION['cookie_consent'])) {
        return [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
    }

    return [
        'essential' => $_SESSION['cookie_consent']['essential'] ?? true,
        'performance' => $_SESSION['cookie_consent']['performance'] ?? false,
        'preferences' => $_SESSION['cookie_consent']['preferences'] ?? false
    ];
}

/**
 * Update the consent state in the session
 *
 * @param array $consentData Array with essential, performance, preferences keys
 * @return bool True if successfully updated
 */
function updateConsentState($consentData) {
    if (!is_array($consentData)) {
        return false;
    }

    $_SESSION['cookie_consent'] = [
        'essential' => $consentData['essential'] ?? true,
        'performance' => $consentData['performance'] ?? false,
        'preferences' => $consentData['preferences'] ?? false,
        'updated_at' => date('Y-m-d H:i:s'),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    return true;
}

/**
 * Determine the category of a cookie based on its name
 *
 * @param string $cookieName The name of the cookie
 * @return string The category (essential, performance, preferences) or null if not found
 */
function getCookieCategory($cookieName) {
    foreach (COOKIE_CATEGORY_MAP as $category => $cookies) {
        if (in_array($cookieName, $cookies)) {
            return $category;
        }
    }

    // If not explicitly mapped, check if it looks like a session cookie
    if (strpos($cookieName, 'session') !== false || $cookieName === 'PHPSESSID') {
        return 'essential';
    }

    // If not explicitly mapped, categorize as preferences (safest for non-critical cookies)
    return 'preferences';
}

/**
 * Check if a cookie operation is allowed based on consent level
 *
 * @param string $cookieName The name of the cookie
 * @param string|null $explicitCategory Override the auto-detected category
 * @return bool True if the cookie operation is allowed, false otherwise
 */
function canSetCookie($cookieName, $explicitCategory = null) {
    // Determine the category
    $category = $explicitCategory ?? getCookieCategory($cookieName);

    // Essential cookies are always allowed
    if ($category === 'essential') {
        return true;
    }

    // Get current consent state
    $consent = getConsentState();

    // Check if user has given consent for this category
    return isset($consent[$category]) && (bool)$consent[$category];
}

/**
 * Check if a cookie can be read based on consent level
 *
 * @param string $cookieName The name of the cookie
 * @param string|null $explicitCategory Override the auto-detected category
 * @return bool True if the cookie can be read, false otherwise
 */
function canReadCookie($cookieName, $explicitCategory = null) {
    // Same logic as canSetCookie for reading
    return canSetCookie($cookieName, $explicitCategory);
}

/**
 * Safely get a cookie value, respecting consent
 *
 * @param string $cookieName The name of the cookie
 * @param string|null $explicitCategory Override the auto-detected category
 * @param mixed $defaultValue Value to return if cookie not found or consent not given
 * @return mixed The cookie value or the default value
 */
function getSafeCookie($cookieName, $explicitCategory = null, $defaultValue = null) {
    if (!canReadCookie($cookieName, $explicitCategory)) {
        error_log("Cookie '{$cookieName}' access denied: consent not given for category");
        return $defaultValue;
    }

    return $_COOKIE[$cookieName] ?? $defaultValue;
}

/**
 * Get the category of a cookie for validation purposes
 *
 * @param string $cookieName The name of the cookie
 * @return string The category (essential, performance, or preferences)
 */
function getCookieCategoryDescription($cookieName) {
    $category = getCookieCategory($cookieName);

    $descriptions = [
        'essential' => 'Essential (always allowed)',
        'performance' => 'Performance (requires explicit consent)',
        'preferences' => 'Preferences (requires explicit consent)'
    ];

    return $descriptions[$category] ?? 'Unknown category';
}

/**
 * Log a cookie consent violation for auditing
 *
 * @param string $cookieName The cookie name that was blocked
 * @param string $action The action attempted (read, write, delete)
 * @param string $reason The reason for blocking
 */
function logCookieConsentViolation($cookieName, $action, $reason = '') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'cookie_name' => $cookieName,
        'action' => $action,
        'reason' => $reason,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];

    error_log('Cookie Consent Violation: ' . json_encode($logEntry));
}

/**
 * Validate consent before setting a cookie in response headers
 *
 * Note: PHP setcookie() must be called before any output is sent.
 * This function provides consent validation but doesn't prevent the call.
 * It's up to the calling code to check the return value.
 *
 * @param string $cookieName The name of the cookie
 * @param string|null $explicitCategory Override the auto-detected category
 * @return array Array with 'allowed' (bool) and 'reason' (string) keys
 */
function validateCookieOperation($cookieName, $explicitCategory = null) {
    $category = $explicitCategory ?? getCookieCategory($cookieName);

    if (!canSetCookie($cookieName, $category)) {
        $reason = "Consent not given for {$category} cookies. User must provide explicit consent to use {$cookieName}.";
        logCookieConsentViolation($cookieName, 'set', $reason);
        return [
            'allowed' => false,
            'reason' => $reason,
            'category' => $category
        ];
    }

    return [
        'allowed' => true,
        'reason' => "Cookie operation allowed for category: {$category}",
        'category' => $category
    ];
}

/**
 * Get a detailed consent report for the current session
 *
 * @return array Comprehensive consent state and cookie information
 */
function getConsentReport() {
    $consent = getConsentState();

    return [
        'timestamp' => date('Y-m-d H:i:s'),
        'consent_state' => $consent,
        'categories' => [
            'essential' => [
                'name' => 'Essential Cookies',
                'allowed' => true,
                'description' => 'Required for basic functionality and security',
                'examples' => COOKIE_CATEGORY_MAP['essential']
            ],
            'performance' => [
                'name' => 'Performance Cookies',
                'allowed' => $consent['performance'],
                'description' => 'Help us understand how you use our site',
                'examples' => COOKIE_CATEGORY_MAP['performance']
            ],
            'preferences' => [
                'name' => 'Preference Cookies',
                'allowed' => $consent['preferences'],
                'description' => 'Remember your choices and settings',
                'examples' => COOKIE_CATEGORY_MAP['preferences']
            ]
        ]
    ];
}
?>

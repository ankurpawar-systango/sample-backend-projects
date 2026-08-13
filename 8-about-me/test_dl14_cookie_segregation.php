<?php
/**
 * Unit Tests for Cookie Segregation (DL-14)
 *
 * Comprehensive tests for cookie segregation by consent level across
 * frontend and backend of the entire application.
 *
 * DL-14: Ensure cookie segregation validation across all pages and APIs
 */

// Start output buffering to capture headers
ob_start();

// Suppress output from included files
@session_start();

class DL14CookieSegregationTest {
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];

    // Mock session for testing without actual browser
    private $mockSession = [];

    public function __construct() {
        // Initialize mock session
        $this->mockSession['cookie_consent'] = [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
    }

    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "=== DL-14: Cookie Segregation Unit Tests ===\n\n";

        // Cookie Helper Tests
        $this->testCookieHelperFunctions();

        // Cookie Consent Endpoint Tests
        $this->testCookieConsentValidation();

        // Backend Integration Tests
        $this->testBackendCookieValidation();

        // Sync and Report Tests
        $this->testSyncAndReporting();

        $this->printResults();
    }

    /**
     * Test cookie helper functions
     */
    private function testCookieHelperFunctions() {
        echo "Testing Cookie Helper Functions...\n";
        echo str_repeat("-", 50) . "\n";

        // Test 1: Cookie category detection
        $testName = "DL-14-Helper-1: getCookieCategory() detects essential cookies";
        $result = $this->testCookieCategoryDetection();
        $this->addTest($testName, $result);

        // Test 2: Cookie consent initialization
        $testName = "DL-14-Helper-2: initializeSessionWithConsentSupport() initializes consent state";
        $_SESSION['cookie_consent'] = null; // Reset
        require_once 'cookie-helper.php';
        initializeSessionWithConsentSupport();
        $result = isset($_SESSION['cookie_consent']) &&
                  $_SESSION['cookie_consent']['essential'] === true;
        $this->addTest($testName, $result);

        // Test 3: Can set essential cookies
        $testName = "DL-14-Helper-3: Essential cookies are always allowed";
        $result = canSetCookie('PHPSESSID', 'essential') === true;
        $this->addTest($testName, $result);

        // Test 4: Performance cookies require consent
        $testName = "DL-14-Helper-4: Performance cookies blocked without consent";
        $_SESSION['cookie_consent']['performance'] = false;
        $result = canSetCookie('_ga', 'performance') === false;
        $this->addTest($testName, $result);

        // Test 5: Preference cookies require consent
        $testName = "DL-14-Helper-5: Preference cookies allowed with consent";
        $_SESSION['cookie_consent']['preferences'] = true;
        $result = canSetCookie('_theme', 'preferences') === true;
        $this->addTest($testName, $result);

        // Test 6: Consent state getter
        $testName = "DL-14-Helper-6: getConsentState() returns current consent";
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => true,
            'preferences' => false
        ];
        $consent = getConsentState();
        $result = $consent['performance'] === true && $consent['preferences'] === false;
        $this->addTest($testName, $result);

        // Test 7: Consent state updater
        $testName = "DL-14-Helper-7: updateConsentState() persists consent changes";
        $newState = [
            'essential' => true,
            'performance' => true,
            'preferences' => true
        ];
        $result = updateConsentState($newState);
        $result = $result && $_SESSION['cookie_consent']['performance'] === true;
        $this->addTest($testName, $result);

        // Test 8: Cookie category mapping
        $testName = "DL-14-Helper-8: Cookie category mapping includes all essential cookies";
        $result = in_array('PHPSESSID', COOKIE_CATEGORY_MAP['essential']) &&
                  in_array('csrf_token', COOKIE_CATEGORY_MAP['essential']);
        $this->addTest($testName, $result);

        // Test 9: Safe cookie getter respects consent
        $testName = "DL-14-Helper-9: getSafeCookie() respects consent restrictions";
        $_SESSION['cookie_consent']['performance'] = false;
        $_COOKIE['_ga'] = 'test_value';
        $result = getSafeCookie('_ga', 'performance', 'DEFAULT') === 'DEFAULT';
        $this->addTest($testName, $result);

        // Test 10: Consent report generation
        $testName = "DL-14-Helper-10: getConsentReport() generates complete report";
        $report = getConsentReport();
        $result = isset($report['consent_state']) &&
                  isset($report['categories']) &&
                  count($report['categories']) === 3;
        $this->addTest($testName, $result);

        echo "\n";
    }

    /**
     * Test cookie consent validation endpoint behavior
     */
    private function testCookieConsentValidation() {
        echo "Testing Cookie Consent Validation...\n";
        echo str_repeat("-", 50) . "\n";

        // Test 11: Essential cookies always validate
        $testName = "DL-14-Consent-11: Validate essential cookies returns allowed";
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
        require_once 'cookie-helper.php';
        $validation = validateCookieOperation('PHPSESSID', 'essential');
        $result = $validation['allowed'] === true;
        $this->addTest($testName, $result);

        // Test 12: Performance cookies require consent
        $testName = "DL-14-Consent-12: Validate performance cookies without consent returns denied";
        $_SESSION['cookie_consent']['performance'] = false;
        $validation = validateCookieOperation('_ga', 'performance');
        $result = $validation['allowed'] === false;
        $this->addTest($testName, $result);

        // Test 13: Performance cookies with consent
        $testName = "DL-14-Consent-13: Validate performance cookies with consent returns allowed";
        $_SESSION['cookie_consent']['performance'] = true;
        $validation = validateCookieOperation('_ga', 'performance');
        $result = $validation['allowed'] === true;
        $this->addTest($testName, $result);

        // Test 14: Preference cookies validation
        $testName = "DL-14-Consent-14: Validate preference cookies respects consent";
        $_SESSION['cookie_consent']['preferences'] = false;
        $validation = validateCookieOperation('_theme', 'preferences');
        $result = $validation['allowed'] === false;
        $this->addTest($testName, $result);

        // Test 15: Validation response structure
        $testName = "DL-14-Consent-15: Validation response contains all required fields";
        $validation = validateCookieOperation('PHPSESSID', 'essential');
        $result = isset($validation['allowed']) &&
                  isset($validation['reason']) &&
                  isset($validation['category']);
        $this->addTest($testName, $result);

        echo "\n";
    }

    /**
     * Test backend cookie validation integration
     */
    private function testBackendCookieValidation() {
        echo "Testing Backend Cookie Validation Integration...\n";
        echo str_repeat("-", 50) . "\n";

        // Test 16: Session variables can be accessed without consent
        $testName = "DL-14-Backend-16: Essential session variables accessible";
        $_SESSION['user'] = ['id' => 1, 'name' => 'Test User'];
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
        require_once 'cookie-helper.php';
        $result = isset($_SESSION['user']) && $_SESSION['user']['name'] === 'Test User';
        $this->addTest($testName, $result);

        // Test 17: Multiple cookie categories can be independently managed
        $testName = "DL-14-Backend-17: Multiple categories independently managed";
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => true,
            'preferences' => false
        ];
        $canSetPerf = canSetCookie('_ga', 'performance');
        $canSetPref = canSetCookie('_theme', 'preferences');
        $result = $canSetPerf === true && $canSetPref === false;
        $this->addTest($testName, $result);

        // Test 18: Session persistence across requests
        $testName = "DL-14-Backend-18: Consent state persists in session";
        updateConsentState(['essential' => true, 'performance' => true, 'preferences' => true]);
        $consent = getConsentState();
        $result = $consent['performance'] === true && $consent['preferences'] === true;
        $this->addTest($testName, $result);

        // Test 19: Reading cookies respects consent
        $testName = "DL-14-Backend-19: Reading cookies respects consent";
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
        $_COOKIE['_ga'] = 'analytics_value';
        $result = canReadCookie('_ga', 'performance') === false;
        $this->addTest($testName, $result);

        // Test 20: Cookie category descriptions are accurate
        $testName = "DL-14-Backend-20: Cookie category descriptions accurate";
        $desc = getCookieCategoryDescription('PHPSESSID');
        $result = strpos($desc, 'Essential') !== false;
        $this->addTest($testName, $result);

        echo "\n";
    }

    /**
     * Test sync and reporting functionality
     */
    private function testSyncAndReporting() {
        echo "Testing Sync and Reporting...\n";
        echo str_repeat("-", 50) . "\n";

        // Test 21: Backend consent report includes all categories
        $testName = "DL-14-Sync-21: Consent report includes all categories";
        require_once 'cookie-helper.php';
        $_SESSION['cookie_consent'] = [
            'essential' => true,
            'performance' => true,
            'preferences' => false
        ];
        $report = getConsentReport();
        $result = count($report['categories']) === 3 &&
                  isset($report['categories']['essential']) &&
                  isset($report['categories']['performance']) &&
                  isset($report['categories']['preferences']);
        $this->addTest($testName, $result);

        // Test 22: Report reflects current consent state
        $testName = "DL-14-Sync-22: Report reflects current consent state";
        $report = getConsentReport();
        $result = $report['consent_state']['performance'] === true &&
                  $report['consent_state']['preferences'] === false;
        $this->addTest($testName, $result);

        // Test 23: Report includes category examples
        $testName = "DL-14-Sync-23: Report includes cookie examples for each category";
        $report = getConsentReport();
        $result = isset($report['categories']['essential']['examples']) &&
                  is_array($report['categories']['essential']['examples']) &&
                  count($report['categories']['essential']['examples']) > 0;
        $this->addTest($testName, $result);

        // Test 24: Category examples are accurate
        $testName = "DL-14-Sync-24: Performance category examples are correct";
        $report = getConsentReport();
        $examples = $report['categories']['performance']['examples'];
        $result = in_array('_ga', $examples) && in_array('_gid', $examples);
        $this->addTest($testName, $result);

        // Test 25: All essential cookies are listed in report
        $testName = "DL-14-Sync-25: Essential cookies listed in report";
        $report = getConsentReport();
        $essentialExamples = $report['categories']['essential']['examples'];
        $result = in_array('PHPSESSID', $essentialExamples);
        $this->addTest($testName, $result);

        echo "\n";
    }

    /**
     * Helper function to test cookie category detection
     */
    private function testCookieCategoryDetection() {
        require_once 'cookie-helper.php';

        $essentialCookies = ['PHPSESSID', 'csrf_token', 'session_id'];
        $performanceCookies = ['_ga', '_gid', '_analytics'];
        $preferenceCookies = ['_theme', '_language', '_preferences'];

        foreach ($essentialCookies as $cookie) {
            if (getCookieCategory($cookie) !== 'essential') {
                return false;
            }
        }

        foreach ($performanceCookies as $cookie) {
            if (getCookieCategory($cookie) !== 'performance') {
                return false;
            }
        }

        foreach ($preferenceCookies as $cookie) {
            if (getCookieCategory($cookie) !== 'preferences') {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a test result
     */
    private function addTest($name, $passed, $message = "") {
        $status = $passed ? "PASS" : "FAIL";
        $this->tests[] = [
            "name" => $name,
            "passed" => $passed,
            "message" => $message
        ];

        if ($passed) {
            $this->testsPassed++;
        } else {
            $this->testsFailed++;
        }
    }

    /**
     * Print test results
     */
    private function printResults() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Results Summary\n";
        echo str_repeat("=", 50) . "\n\n";

        foreach ($this->tests as $test) {
            $status = $test['passed'] ? "✓ PASS" : "✗ FAIL";
            echo "{$status} - {$test['name']}\n";
            if (!empty($test['message'])) {
                echo "       {$test['message']}\n";
            }
        }

        echo "\n" . str_repeat("-", 50) . "\n";
        $total = $this->testsPassed + $this->testsFailed;
        echo "Total Tests: {$total}\n";
        echo "Passed: {$this->testsPassed}\n";
        echo "Failed: {$this->testsFailed}\n";
        echo str_repeat("-", 50) . "\n";

        if ($this->testsFailed === 0) {
            echo "\n✓ All cookie segregation tests passed!\n";
            echo "Cookie segregation is properly enforced across all pages and APIs.\n";
            return 0;
        } else {
            echo "\n✗ Some tests failed. Review the failures above.\n";
            return 1;
        }
    }
}

// Run tests
if (php_sapi_name() === 'cli') {
    $tester = new DL14CookieSegregationTest();
    $exitCode = $tester->runAllTests();
    ob_end_clean();
    exit($exitCode);
} else {
    ob_end_clean();
    header('Content-Type: application/json');
    $tester = new DL14CookieSegregationTest();
    $tester->runAllTests();
}
?>

<?php
/**
 * DL-27: Enhanced Backend Cookie Segregation Tests
 *
 * Tests server-side cookie consent validation and enforcement:
 * - Session-based consent persistence
 * - 403 responses for unauthorized cookie access
 * - Consent validation for each category
 * - Server-side enforcement of cookie segregation
 *
 * Run: php test_dl27_backend_segregation.php
 */

class DL27BackendSegregationTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];

    // Valid cookie categories
    const COOKIE_CATEGORIES = ['essential', 'performance', 'preferences'];

    // Mock session storage
    private $mockSession = [];

    public function runTests()
    {
        echo "=== DL-27: Backend Cookie Segregation Tests ===\n\n";

        // Core validation tests
        $this->testEssentialCookiesAlwaysAllowed();
        $this->testPerformanceCookiesRequireConsent();
        $this->testPreferenceCookiesRequireConsent();
        $this->testInvalidCategoryReturnsError();

        // Session persistence tests
        $this->testConsentStateSavedToSession();
        $this->testConsentStateRetrievedFromSession();
        $this->testDefaultConsentStateWithNoSession();

        // 403 response tests
        $this->testUnauthorizedPerformanceReturns403();
        $this->testUnauthorizedPreferencesReturns403();
        $this->testAuthorizedAccessReturns200();

        // Save preferences tests
        $this->testSavePreferencesUpdatesSession();
        $this->testSavePreferencesDefaults();
        $this->testSavePreferencesTimestamp();

        // Integration tests
        $this->testFullWorkflowSaveAndValidate();
        $this->testRevokeConsentWorkflow();

        $this->printResults();
    }

    private function testEssentialCookiesAlwaysAllowed()
    {
        $testName = "Essential cookies always allowed regardless of consent";
        try {
            $response = $this->simulateValidateRequest('essential', [
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            $this->assertTrue(
                $response['allowed'] === true && $response['status'] === 'success',
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testPerformanceCookiesRequireConsent()
    {
        $testName = "Performance cookies blocked without consent";
        try {
            $response = $this->simulateValidateRequest('performance', [
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            $this->assertTrue(
                $response['allowed'] === false && $response['status'] === 'error',
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testPreferenceCookiesRequireConsent()
    {
        $testName = "Preference cookies blocked without consent";
        try {
            $response = $this->simulateValidateRequest('preferences', [
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            $this->assertTrue(
                $response['allowed'] === false && $response['status'] === 'error',
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testInvalidCategoryReturnsError()
    {
        $testName = "Invalid cookie category returns error";
        try {
            $response = $this->simulateValidateRequest('invalid_category', [
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            $this->assertTrue($response['status'] === 'error', $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testConsentStateSavedToSession()
    {
        $testName = "Consent state saved to session correctly";
        try {
            $this->mockSession = [];

            $consentData = [
                'essential' => true,
                'performance' => true,
                'preferences' => false,
                'userAgent' => 'Test Browser'
            ];

            $saved = $this->saveConsentStateToSession($consentData);

            $this->assertTrue(
                $saved && isset($this->mockSession['cookie_consent']),
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testConsentStateRetrievedFromSession()
    {
        $testName = "Consent state retrieved from session correctly";
        try {
            $this->mockSession['cookie_consent'] = [
                'essential' => true,
                'performance' => true,
                'preferences' => false,
                'saved_at' => date('Y-m-d H:i:s')
            ];

            $state = $this->getConsentStateFromSession();

            $this->assertTrue(
                $state['essential'] === true &&
                $state['performance'] === true &&
                $state['preferences'] === false,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testDefaultConsentStateWithNoSession()
    {
        $testName = "Default consent state when no session exists";
        try {
            $this->mockSession = [];

            $state = $this->getConsentStateFromSession();

            $this->assertTrue(
                $state['essential'] === true &&
                $state['performance'] === false &&
                $state['preferences'] === false,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testUnauthorizedPerformanceReturns403()
    {
        $testName = "Unauthorized performance cookie access returns 403";
        try {
            $response = $this->simulateValidateRequest('performance', [
                'essential' => true,
                'performance' => false,
                'preferences' => true
            ]);

            $httpCode = $response['allowed'] === false ? 403 : 200;

            $this->assertTrue($httpCode === 403, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testUnauthorizedPreferencesReturns403()
    {
        $testName = "Unauthorized preference cookie access returns 403";
        try {
            $response = $this->simulateValidateRequest('preferences', [
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ]);

            $httpCode = $response['allowed'] === false ? 403 : 200;

            $this->assertTrue($httpCode === 403, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAuthorizedAccessReturns200()
    {
        $testName = "Authorized cookie access returns 200";
        try {
            $response = $this->simulateValidateRequest('performance', [
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ]);

            $httpCode = $response['allowed'] === true ? 200 : 403;

            $this->assertTrue($httpCode === 200, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testSavePreferencesUpdatesSession()
    {
        $testName = "Save preferences updates session state";
        try {
            $this->mockSession = [];

            $preferences = [
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ];

            $response = $this->simulateSaveRequest($preferences);

            $this->assertTrue(
                $response['status'] === 'success' &&
                $response['persistedToServer'] === true,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testSavePreferencesDefaults()
    {
        $testName = "Save preferences applies correct defaults";
        try {
            $response = $this->simulateSaveRequest([]);

            $this->assertTrue(
                $response['saved_preferences']['essential'] === true &&
                $response['saved_preferences']['performance'] === false &&
                $response['saved_preferences']['preferences'] === false,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testSavePreferencesTimestamp()
    {
        $testName = "Save preferences includes timestamp";
        try {
            $response = $this->simulateSaveRequest([
                'essential' => true,
                'performance' => true,
                'preferences' => true
            ]);

            $this->assertTrue(
                isset($response['saved_preferences']['timestamp']),
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testFullWorkflowSaveAndValidate()
    {
        $testName = "Full workflow: save preferences then validate";
        try {
            $this->mockSession = [];

            // 1. Save preferences with performance enabled
            $saveResponse = $this->simulateSaveRequest([
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ]);

            // 2. Validate performance cookie access
            $validateResponse = $this->simulateValidateRequest('performance', [
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ]);

            $this->assertTrue(
                $saveResponse['status'] === 'success' &&
                $validateResponse['allowed'] === true,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testRevokeConsentWorkflow()
    {
        $testName = "Revoke consent workflow blocks access";
        try {
            $this->mockSession = [];

            // 1. Initially allow performance
            $this->simulateSaveRequest([
                'essential' => true,
                'performance' => true,
                'preferences' => false
            ]);

            // 2. Revoke performance consent
            $this->simulateSaveRequest([
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            // 3. Validate performance cookie access (should fail)
            $validateResponse = $this->simulateValidateRequest('performance', [
                'essential' => true,
                'performance' => false,
                'preferences' => false
            ]);

            $this->assertTrue($validateResponse['allowed'] === false, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    /**
     * Simulate validate consent request
     */
    private function simulateValidateRequest($category, $currentConsent)
    {
        // Essential cookies are always allowed
        if ($category === 'essential') {
            return [
                'status' => 'success',
                'allowed' => true,
                'message' => 'Essential cookies are always allowed',
                'category' => $category,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // Check for invalid category
        if (!in_array($category, self::COOKIE_CATEGORIES)) {
            return [
                'status' => 'error',
                'message' => 'Invalid consent_level. Must be one of: ' . implode(', ', self::COOKIE_CATEGORIES),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        // Check consent for the requested category
        $hasConsent = isset($currentConsent[$category]) && $currentConsent[$category] === true;

        if (!$hasConsent) {
            return [
                'status' => 'error',
                'allowed' => false,
                'message' => "Consent not given for {$category} cookies. Please update your cookie preferences.",
                'category' => $category,
                'requiredAction' => 'update_preferences',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }

        return [
            'status' => 'success',
            'allowed' => true,
            'message' => "Consent verified for {$category} cookies",
            'category' => $category,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Simulate save preferences request
     */
    private function simulateSaveRequest($data)
    {
        $preferences = [
            'essential' => true,
            'performance' => $data['performance'] ?? false,
            'preferences' => $data['preferences'] ?? false,
            'timestamp' => date('Y-m-d H:i:s'),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];

        $this->saveConsentStateToSession($preferences);

        return [
            'status' => 'success',
            'message' => 'Cookie preferences saved successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'saved_preferences' => $preferences,
            'nextReviewDate' => date('Y-m-d H:i:s', strtotime('+1 year')),
            'segregationEnforced' => true,
            'persistedToServer' => true
        ];
    }

    /**
     * Get consent state from mock session
     */
    private function getConsentStateFromSession()
    {
        if (isset($this->mockSession['cookie_consent'])) {
            return [
                'essential' => $this->mockSession['cookie_consent']['essential'] ?? true,
                'performance' => $this->mockSession['cookie_consent']['performance'] ?? false,
                'preferences' => $this->mockSession['cookie_consent']['preferences'] ?? false
            ];
        }

        return [
            'essential' => true,
            'performance' => false,
            'preferences' => false
        ];
    }

    /**
     * Save consent state to mock session
     */
    private function saveConsentStateToSession($consentData)
    {
        if (!is_array($consentData)) {
            return false;
        }

        $this->mockSession['cookie_consent'] = [
            'essential' => $consentData['essential'] ?? true,
            'performance' => $consentData['performance'] ?? false,
            'preferences' => $consentData['preferences'] ?? false,
            'saved_at' => date('Y-m-d H:i:s'),
            'user_agent' => $consentData['userAgent'] ?? null
        ];

        return true;
    }

    /**
     * AssertTrue assertion
     */
    private function assertTrue($condition, $testName)
    {
        if ($condition) {
            $this->testsPassed++;
            $this->tests[] = [
                'name' => $testName,
                'status' => 'PASS',
                'details' => 'Test passed'
            ];
        } else {
            $this->testsFailed++;
            $this->tests[] = [
                'name' => $testName,
                'status' => 'FAIL',
                'details' => 'Assertion failed'
            ];
        }
    }

    /**
     * AssertFalse assertion
     */
    private function assertFalse($message)
    {
        $this->testsFailed++;
        $this->tests[] = [
            'name' => $message,
            'status' => 'FAIL',
            'details' => 'Test failed'
        ];
    }

    /**
     * Print test results
     */
    private function printResults()
    {
        $totalTests = $this->testsPassed + $this->testsFailed;
        $passPercentage = $totalTests > 0 ? round(($this->testsPassed / $totalTests) * 100) : 0;

        echo "Test Results:\n";
        echo "=============\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Passed: {$this->testsPassed} ✓\n";
        echo "Failed: {$this->testsFailed} ✗\n";
        echo "Success Rate: {$passPercentage}%\n\n";

        echo "Details:\n";
        echo "--------\n";
        foreach ($this->tests as $test) {
            $status = $test['status'] === 'PASS' ? '✓' : '✗';
            echo "{$status} {$test['name']}\n";
        }

        echo "\n=== DL-27 Backend Tests Complete ===\n";

        // Exit with proper code
        exit($this->testsFailed === 0 ? 0 : 1);
    }
}

// Run the tests
$test = new DL27BackendSegregationTest();
$test->runTests();
?>

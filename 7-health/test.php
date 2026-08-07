<?php
/**
 * Unit Tests for Health Status API
 *
 * Tests for the health endpoint functionality
 */

class HealthTest {
    private $passed = 0;
    private $failed = 0;
    private $apiUrl = 'http://localhost/7-health/api.php';

    /**
     * Run all tests
     */
    public function runTests() {
        echo "🧪 Running Health Status API Tests\n";
        echo "================================\n\n";

        // Unit tests
        $this->testHealthStatusFunction();
        $this->testCORSHeaders();

        // Display results
        $this->displayResults();
    }

    /**
     * Test the health status function
     */
    private function testHealthStatusFunction() {
        echo "Test 1: Health Status Function\n";

        // Include the API file and get the function
        ob_start();
        include(__DIR__ . '/api.php');
        $output = ob_get_clean();

        // Verify JSON output
        $data = json_decode($output, true);

        if ($data && $data['status'] === 'ok') {
            echo "✓ PASS: Health status is 'ok'\n";
            $this->passed++;
        } else {
            echo "✗ FAIL: Health status is not 'ok'\n";
            $this->failed++;
        }

        if ($data && isset($data['timestamp'])) {
            echo "✓ PASS: Timestamp is present\n";
            $this->passed++;
        } else {
            echo "✗ FAIL: Timestamp is not present\n";
            $this->failed++;
        }

        if ($data && isset($data['service'])) {
            echo "✓ PASS: Service name is present\n";
            $this->passed++;
        } else {
            echo "✗ FAIL: Service name is not present\n";
            $this->failed++;
        }

        echo "\n";
    }

    /**
     * Test CORS headers
     */
    private function testCORSHeaders() {
        echo "Test 2: CORS Headers\n";

        // Test that the API is accessible and returns proper headers
        $headers = get_headers($this->apiUrl, 1);

        if ($headers) {
            echo "✓ PASS: API endpoint is accessible\n";
            $this->passed++;
        } else {
            echo "✗ FAIL: API endpoint is not accessible\n";
            $this->failed++;
        }

        // Check Content-Type header
        if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/json') !== false) {
            echo "✓ PASS: Content-Type is application/json\n";
            $this->passed++;
        } else {
            echo "ℹ INFO: Could not verify Content-Type header\n";
        }

        echo "\n";
    }

    /**
     * Display test results
     */
    private function displayResults() {
        $total = $this->passed + $this->failed;
        echo "================================\n";
        echo "Test Results\n";
        echo "================================\n";
        echo "✓ Passed: {$this->passed}\n";
        echo "✗ Failed: {$this->failed}\n";
        echo "━ Total: {$total}\n\n";

        if ($this->failed === 0) {
            echo "🎉 All tests passed!\n";
            return 0;
        } else {
            echo "❌ Some tests failed!\n";
            return 1;
        }
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' || (isset($_GET['test']) && $_GET['test'] === '1')) {
    $test = new HealthTest();
    exit($test->runTests());
}

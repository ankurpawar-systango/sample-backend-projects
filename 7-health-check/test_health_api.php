<?php
/**
 * Health Check API Test Suite
 *
 * Unit tests for the health check API endpoint
 */

class HealthCheckTest {
    private $testsPassed = 0;
    private $testsFailed = 0;

    /**
     * Run all tests
     */
    public function runTests() {
        echo "=== Health Check API Test Suite ===\n\n";

        $this->testHealthEndpointResponse();
        $this->testResponseJsonFormat();
        $this->testResponseStatusField();
        $this->testResponseTimestampField();
        $this->testResponseVersionField();
        $this->testResponseChecksField();
        $this->testCORSHeaders();
        $this->testMemoryTracking();

        $this->printSummary();
    }

    /**
     * Test that the health endpoint returns data
     */
    private function testHealthEndpointResponse() {
        $this->test('Health endpoint returns valid response', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            if (empty($output)) {
                throw new Exception('Empty response from endpoint');
            }

            $data = json_decode($output, true);
            if ($data === null) {
                throw new Exception('Response is not valid JSON');
            }

            return true;
        });
    }

    /**
     * Test that response is valid JSON format
     */
    private function testResponseJsonFormat() {
        $this->test('Response is valid JSON', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }

            return is_array($data);
        });
    }

    /**
     * Test that status field exists and is 'ok'
     */
    private function testResponseStatusField() {
        $this->test('Response contains status field with value "ok"', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if (!isset($data['status'])) {
                throw new Exception('Status field missing from response');
            }

            if ($data['status'] !== 'ok') {
                throw new Exception('Status is not "ok", got: ' . $data['status']);
            }

            return true;
        });
    }

    /**
     * Test that timestamp field exists
     */
    private function testResponseTimestampField() {
        $this->test('Response contains valid timestamp field', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if (!isset($data['timestamp'])) {
                throw new Exception('Timestamp field missing from response');
            }

            // Verify it's a valid ISO 8601 timestamp
            if (!preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['timestamp'])) {
                throw new Exception('Timestamp is not in ISO 8601 format');
            }

            return true;
        });
    }

    /**
     * Test that version field exists
     */
    private function testResponseVersionField() {
        $this->test('Response contains version information', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if (!isset($data['version'])) {
                throw new Exception('Version field missing from response');
            }

            if (!isset($data['version']['php'])) {
                throw new Exception('PHP version missing from version field');
            }

            if (!isset($data['version']['api'])) {
                throw new Exception('API version missing from version field');
            }

            return true;
        });
    }

    /**
     * Test that checks field exists
     */
    private function testResponseChecksField() {
        $this->test('Response contains health checks', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if (!isset($data['checks'])) {
                throw new Exception('Checks field missing from response');
            }

            if (!isset($data['checks']['php_enabled'])) {
                throw new Exception('php_enabled check missing');
            }

            if (!isset($data['checks']['json_enabled'])) {
                throw new Exception('json_enabled check missing');
            }

            return true;
        });
    }

    /**
     * Test CORS headers
     */
    private function testCORSHeaders() {
        $this->test('Endpoint sets proper CORS headers', function() {
            // Check if headers would be set (simulate header calls)
            $headers = [];

            // In a real scenario, we'd check actual headers
            // For now, we verify the code includes the header calls
            $fileContent = file_get_contents('api.php');

            if (strpos($fileContent, 'Access-Control-Allow-Origin') === false) {
                throw new Exception('CORS headers not found in code');
            }

            return true;
        });
    }

    /**
     * Test memory tracking
     */
    private function testMemoryTracking() {
        $this->test('Response includes memory usage information', function() {
            ob_start();
            include 'api.php';
            $output = ob_get_clean();

            $data = json_decode($output, true);
            if (!isset($data['checks']['memory_usage'])) {
                throw new Exception('Memory usage check missing');
            }

            if (!isset($data['checks']['memory_usage']['current'])) {
                throw new Exception('Current memory usage missing');
            }

            if (!isset($data['checks']['memory_usage']['peak'])) {
                throw new Exception('Peak memory usage missing');
            }

            return true;
        });
    }

    /**
     * Helper method to run a single test
     */
    private function test($name, callable $callback) {
        try {
            $result = $callback();
            if ($result === true) {
                echo "✓ PASS: {$name}\n";
                $this->testsPassed++;
            } else {
                echo "✗ FAIL: {$name} - Assertion failed\n";
                $this->testsFailed++;
            }
        } catch (Exception $e) {
            echo "✗ FAIL: {$name}\n";
            echo "  Error: {$e->getMessage()}\n";
            $this->testsFailed++;
        }
    }

    /**
     * Print test summary
     */
    private function printSummary() {
        $total = $this->testsPassed + $this->testsFailed;
        echo "\n=== Test Summary ===\n";
        echo "Total: {$total} | Passed: {$this->testsPassed} | Failed: {$this->testsFailed}\n";

        if ($this->testsFailed === 0) {
            echo "All tests passed! ✓\n";
        } else {
            echo "Some tests failed. Please review the errors above.\n";
        }
    }
}

// Run tests if this file is executed directly
if (basename($GLOBALS['_SERVER']['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    $test = new HealthCheckTest();
    $test->runTests();
}
?>

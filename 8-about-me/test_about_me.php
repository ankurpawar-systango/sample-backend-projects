<?php
/**
 * Unit Tests for About Me Endpoint
 *
 * Tests for the about-me endpoint functionality
 */

class AboutMeTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];

    public function runTests()
    {
        echo "=== About Me Endpoint Unit Tests ===\n\n";

        $this->testAboutMeClassInstantiation();
        $this->testAboutMeMessage();
        $this->testResponseTime();
        $this->testEndpointOperational();
        $this->testTimestampFormat();
        $this->testJsonResponseStructure();
        $this->testCorsHeaders();
        $this->testOptionsRequest();
        $this->testNonGetMethods();

        $this->printResults();
    }

    private function testAboutMeClassInstantiation()
    {
        $testName = "AboutMe class instantiation";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $this->assertTrue($aboutMe !== null, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAboutMeMessage()
    {
        $testName = "About Me message matches requirement";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $message = $aboutMe->getMessage();
            $this->assertTrue(
                $message === "This is a sample site",
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testResponseTime()
    {
        $testName = "Response time calculation";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $responseTime = $aboutMe->getResponseTime();
            $this->assertTrue(
                is_numeric($responseTime) && $responseTime >= 0,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testEndpointOperational()
    {
        $testName = "Endpoint is operational";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $isOperational = $aboutMe->isOperational();
            $this->assertTrue($isOperational === true, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testTimestampFormat()
    {
        $testName = "Timestamp is properly formatted";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $timestamp = $aboutMe->getTimestamp();
            // Verify the timestamp matches Y-m-d H:i:s format
            $this->assertTrue(
                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp) === 1,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testJsonResponseStructure()
    {
        $testName = "JSON response has required fields";
        try {
            require_once 'config.php';
            $aboutMe = new AboutMe();
            $message = $aboutMe->getMessage();
            $timestamp = $aboutMe->getTimestamp();

            $responseStructure = [
                'status' => 'success',
                'message' => $message,
                'timestamp' => $timestamp
            ];

            $json = json_encode($responseStructure);
            $decoded = json_decode($json, true);

            $hasStatus = isset($decoded['status']) && $decoded['status'] === 'success';
            $hasMessage = isset($decoded['message']) && $decoded['message'] === "This is a sample site";
            $hasTimestamp = isset($decoded['timestamp']) && !empty($decoded['timestamp']);

            $this->assertTrue(
                $hasStatus && $hasMessage && $hasTimestamp,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testCorsHeaders()
    {
        $testName = "CORS headers are properly configured";
        // This test verifies the header definitions in about-me.php
        try {
            $expectedHeaders = [
                'Content-Type: application/json',
                'Access-Control-Allow-Origin: *',
                'Access-Control-Allow-Methods: GET, OPTIONS',
                'Access-Control-Allow-Headers: Content-Type'
            ];

            // Read the about-me.php file to verify headers
            $fileContent = file_get_contents('about-me.php');

            $allHeadersPresent = true;
            foreach ($expectedHeaders as $header) {
                if (strpos($fileContent, "header('" . $header . "')") === false &&
                    strpos($fileContent, 'header("' . $header . '")') === false) {
                    $allHeadersPresent = false;
                    break;
                }
            }

            $this->assertTrue($allHeadersPresent, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testOptionsRequest()
    {
        $testName = "OPTIONS preflight requests are handled";
        try {
            $fileContent = file_get_contents('about-me.php');
            // Verify OPTIONS handling exists
            $hasOptionsHandling = strpos($fileContent, "REQUEST_METHOD'] === 'OPTIONS'") !== false;
            $this->assertTrue($hasOptionsHandling, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testNonGetMethods()
    {
        $testName = "Non-GET requests return 405 Method Not Allowed";
        try {
            $fileContent = file_get_contents('about-me.php');
            // Verify 405 error handling exists
            $has405Handling = strpos($fileContent, "http_response_code(405)") !== false;
            $hasMethodNotAllowed = strpos($fileContent, "'message' => 'Method Not Allowed'") !== false;
            $this->assertTrue($has405Handling && $hasMethodNotAllowed, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function assertTrue($condition, $testName)
    {
        if ($condition) {
            $this->tests[] = [
                'name' => $testName,
                'passed' => true
            ];
            $this->testsPassed++;
        } else {
            $this->tests[] = [
                'name' => $testName,
                'passed' => false
            ];
            $this->testsFailed++;
        }
    }

    private function assertFalse($testName)
    {
        $this->tests[] = [
            'name' => $testName,
            'passed' => false
        ];
        $this->testsFailed++;
    }

    private function printResults()
    {
        echo "Test Results:\n";
        echo str_repeat("-", 50) . "\n";

        foreach ($this->tests as $test) {
            $status = $test['passed'] ? "✓ PASS" : "✗ FAIL";
            echo $status . " - " . $test['name'] . "\n";
        }

        echo str_repeat("-", 50) . "\n";
        echo "Total Tests: " . ($this->testsPassed + $this->testsFailed) . "\n";
        echo "Passed: " . $this->testsPassed . "\n";
        echo "Failed: " . $this->testsFailed . "\n";

        if ($this->testsFailed === 0) {
            echo "\n✓ All tests passed!\n";
            exit(0);
        } else {
            echo "\n✗ Some tests failed!\n";
            exit(1);
        }
    }
}

// Run tests
$tester = new AboutMeTest();
$tester->runTests();

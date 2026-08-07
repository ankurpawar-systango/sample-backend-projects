<?php
/**
 * Unit Tests for Health Endpoint
 *
 * Tests for the health check endpoint functionality
 */

class HealthCheckTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];

    public function runTests()
    {
        echo "=== Health Endpoint Unit Tests ===\n\n";

        $this->testHealthCheckClass();
        $this->testResponseTime();
        $this->testHealthStatus();
        $this->testMultipleChecks();

        $this->printResults();
    }

    private function testHealthCheckClass()
    {
        $testName = "HealthCheck class instantiation";
        try {
            require_once 'config.php';
            $health = new HealthCheck();
            $this->assertTrue($health !== null, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testResponseTime()
    {
        $testName = "Response time calculation";
        try {
            require_once 'config.php';
            $health = new HealthCheck();
            $responseTime = $health->getResponseTime();
            $this->assertTrue(
                is_numeric($responseTime) && $responseTime >= 0,
                $testName
            );
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testHealthStatus()
    {
        $testName = "Overall health status determination";
        try {
            require_once 'config.php';
            $health = new HealthCheck();
            $health->addCheck('test_check', true, 'Test passed');
            $status = $health->getOverallStatus();
            $this->assertTrue($status === STATUS_HEALTHY, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testMultipleChecks()
    {
        $testName = "Multiple health checks with failure";
        try {
            require_once 'config.php';
            $health = new HealthCheck();
            $health->addCheck('check_1', true, 'Check 1 passed');
            $health->addCheck('check_2', false, 'Check 2 failed');
            $status = $health->getOverallStatus();
            $this->assertTrue($status === STATUS_UNHEALTHY, $testName);
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
$tester = new HealthCheckTest();
$tester->runTests();

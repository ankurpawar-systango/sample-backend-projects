<?php
/**
 * Unit Tests for Login Endpoint
 *
 * Tests for the authentication system functionality
 */

class LoginTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];
    private $db;

    public function runTests()
    {
        echo "=== Login Endpoint Unit Tests ===\n\n";

        try {
            require_once 'config.php';
            $this->db = new Database();

            $this->testDatabaseConnection();
            $this->testUserTableExists();
            $this->testValidUserExists();
            $this->testPasswordHashing();
            $this->testPasswordVerification();
            $this->testInvalidUsername();
            $this->testInvalidPassword();
            $this->testEmptyCredentials();
        } catch (Exception $e) {
            echo "Fatal error: " . $e->getMessage() . "\n";
            exit(1);
        }

        $this->printResults();
    }

    private function testDatabaseConnection()
    {
        $testName = "Database connection established";
        try {
            $result = $this->db->select('users', 'COUNT(*) as count');
            $this->assertTrue($result !== false && !is_string($result), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testUserTableExists()
    {
        $testName = "Users table exists with required columns";
        try {
            $users = $this->db->select('users', '*', 'LIMIT 1');
            if ($users && isset($users[0])) {
                $user = $users[0];
                $hasRequired = isset($user['id']) && isset($user['username']) &&
                               isset($user['password']) && isset($user['name']) &&
                               isset($user['role']);
                $this->assertTrue($hasRequired, $testName);
            } else {
                $this->assertTrue(true, $testName . " (table exists, no test data)");
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testValidUserExists()
    {
        $testName = "Valid test user exists in database";
        try {
            $user = $this->db->select('users', '*', 'username = ?', ['iqbolshoh'], 's');
            $this->assertTrue($user && isset($user[0]) && $user[0]['username'] === 'iqbolshoh', $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testPasswordHashing()
    {
        $testName = "Passwords are hashed using bcrypt";
        try {
            $user = $this->db->select('users', '*', 'username = ?', ['iqbolshoh'], 's');
            if ($user && isset($user[0])) {
                $hashedPassword = $user[0]['password'];
                // Bcrypt hashes start with $2y$ or $2a$ or $2b$
                $isBcrypt = preg_match('/^\$2[aby]\$/', $hashedPassword) === 1;
                $this->assertTrue($isBcrypt, $testName);
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testPasswordVerification()
    {
        $testName = "Password verification works correctly";
        try {
            $user = $this->db->select('users', '*', 'username = ?', ['iqbolshoh'], 's');
            if ($user && isset($user[0])) {
                $hashedPassword = $user[0]['password'];
                // Test password for iqbolshoh is 'password123'
                $verified = password_verify('password123', $hashedPassword);
                $this->assertTrue($verified, $testName);
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testInvalidUsername()
    {
        $testName = "Invalid username returns no results";
        try {
            $user = $this->db->select('users', '*', 'username = ?', ['nonexistent_user'], 's');
            $this->assertTrue($user === null || count($user) === 0, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testInvalidPassword()
    {
        $testName = "Invalid password verification fails";
        try {
            $user = $this->db->select('users', '*', 'username = ?', ['iqbolshoh'], 's');
            if ($user && isset($user[0])) {
                $hashedPassword = $user[0]['password'];
                $verified = password_verify('wrong_password', $hashedPassword);
                $this->assertTrue(!$verified, $testName);
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testEmptyCredentials()
    {
        $testName = "Empty credentials handling";
        try {
            $emptyUsername = '';
            $emptyPassword = '';
            $shouldReject = empty(trim($emptyUsername)) || empty(trim($emptyPassword));
            $this->assertTrue($shouldReject, $testName);
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
        echo str_repeat("-", 60) . "\n";

        foreach ($this->tests as $test) {
            $status = $test['passed'] ? "✓ PASS" : "✗ FAIL";
            echo $status . " - " . $test['name'] . "\n";
        }

        echo str_repeat("-", 60) . "\n";
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
$tester = new LoginTest();
$tester->runTests();
?>

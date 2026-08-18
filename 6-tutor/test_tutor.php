<?php
/**
 * Unit Tests for Tutor Endpoint
 *
 * Tests for the tutor profile and information retrieval
 */

class TutorTest
{
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $tests = [];
    private $db;

    public function runTests()
    {
        echo "=== Tutor Endpoint Unit Tests ===\n\n";

        try {
            require_once 'config.php';
            $this->db = new Database();

            $this->testDatabaseConnection();
            $this->testTutorTableExists();
            $this->testFirstTutorExists();
            $this->testFirstTutorIsMarked();
            $this->testTutorDataStructure();
            $this->testAnkurPawarExists();
            $this->testTutorEmail();
            $this->testAvinashExists();
            $this->testAvinashEmail();
            $this->testAvinashIsNotFirstTutor();
            $this->testMultipleTutorsExist();
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
            $result = $this->db->select('tutors', 'COUNT(*) as count');
            $this->assertTrue($result !== false && !is_string($result), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testTutorTableExists()
    {
        $testName = "Tutors table exists with required columns";
        try {
            $tutors = $this->db->select('tutors', '*', 'LIMIT 1');
            if ($tutors && isset($tutors[0])) {
                $tutor = $tutors[0];
                $hasRequired = isset($tutor['id']) && isset($tutor['name']) &&
                               isset($tutor['email']) && isset($tutor['is_first_tutor']);
                $this->assertTrue($hasRequired, $testName);
            } else {
                $this->assertTrue(true, $testName . " (table exists, no test data)");
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testFirstTutorExists()
    {
        $testName = "First tutor record exists in database";
        try {
            $tutors = $this->db->select('tutors', '*');
            $this->assertTrue($tutors && count($tutors) > 0, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testFirstTutorIsMarked()
    {
        $testName = "First tutor is marked with is_first_tutor flag";
        try {
            $tutors = $this->db->select('tutors', '*', 'is_first_tutor = ?', [1], 'i');
            $this->assertTrue($tutors && isset($tutors[0]), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testTutorDataStructure()
    {
        $testName = "Tutor data contains name, email, and bio";
        try {
            $tutors = $this->db->select('tutors', '*');
            if ($tutors && isset($tutors[0])) {
                $tutor = $tutors[0];
                $hasBio = isset($tutor['bio']) && !empty($tutor['bio']);
                $this->assertTrue($hasBio, $testName);
            } else {
                $this->assertTrue(false, $testName . " - No tutor found");
            }
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAnkurPawarExists()
    {
        $testName = "Ankur Pawar exists as tutor";
        try {
            $tutors = $this->db->select('tutors', '*', 'name LIKE ?', ['%Ankur%'], 's');
            $this->assertTrue($tutors && isset($tutors[0]), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testTutorEmail()
    {
        $testName = "Tutor email is valid (ankur.pawar@systango.com)";
        try {
            $tutors = $this->db->select('tutors', '*', 'email = ?', ['ankur.pawar@systango.com'], 's');
            $this->assertTrue($tutors && isset($tutors[0]) && $tutors[0]['email'] === 'ankur.pawar@systango.com', $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAvinashExists()
    {
        $testName = "Avinash tutor exists in database";
        try {
            $tutors = $this->db->select('tutors', '*', 'name LIKE ?', ['%Avinash%'], 's');
            $this->assertTrue($tutors && isset($tutors[0]), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAvinashEmail()
    {
        $testName = "Avinash email is correct (avinash@systango.com)";
        try {
            $tutors = $this->db->select('tutors', '*', 'email = ?', ['avinash@systango.com'], 's');
            $this->assertTrue($tutors && isset($tutors[0]) && $tutors[0]['email'] === 'avinash@systango.com', $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testAvinashIsNotFirstTutor()
    {
        $testName = "Avinash is not marked as first tutor";
        try {
            $tutors = $this->db->select('tutors', '*', 'name = ? AND is_first_tutor = ?', ['Avinash', 0], 'si');
            $this->assertTrue($tutors && isset($tutors[0]), $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function testMultipleTutorsExist()
    {
        $testName = "At least 2 tutors exist in database";
        try {
            $tutors = $this->db->select('tutors', '*');
            $this->assertTrue($tutors && count($tutors) >= 2, $testName);
        } catch (Exception $e) {
            $this->assertFalse($testName . " - Exception: " . $e->getMessage());
        }
    }

    private function assertTrue($condition, $testName)
    {
        if ($condition) {
            $this->testsPassed++;
            $this->tests[] = "✓ " . $testName;
        } else {
            $this->testsFailed++;
            $this->tests[] = "✗ " . $testName;
        }
    }

    private function assertFalse($message)
    {
        $this->testsFailed++;
        $this->tests[] = "✗ " . $message;
    }

    private function printResults()
    {
        echo "\n--- Test Results ---\n";
        foreach ($this->tests as $test) {
            echo $test . "\n";
        }

        echo "\n--- Summary ---\n";
        echo "Tests Passed: " . $this->testsPassed . "\n";
        echo "Tests Failed: " . $this->testsFailed . "\n";
        echo "Total Tests: " . ($this->testsPassed + $this->testsFailed) . "\n";

        if ($this->testsFailed === 0) {
            echo "\n✅ All tests passed!\n";
            exit(0);
        } else {
            echo "\n❌ Some tests failed!\n";
            exit(1);
        }
    }
}

// Run the tests
$test = new TutorTest();
$test->runTests();

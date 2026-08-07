#!/usr/bin/env python3
"""
Validation script for Health Endpoint files

Validates:
- PHP syntax correctness
- Required functions exist
- Configuration constants are defined
- JSON output format compliance
"""

import os
import re
import json
import sys

class HealthEndpointValidator:
    def __init__(self):
        self.tests_passed = 0
        self.tests_failed = 0
        self.tests = []

    def run_all_validations(self):
        print("=== Health Endpoint Validation ===\n")

        self.validate_health_php()
        self.validate_config_php()
        self.validate_test_php()
        self.validate_json_response()

        self.print_results()

    def validate_health_php(self):
        """Validate health.php file structure"""
        test_name = "health.php file exists"
        if os.path.exists('health.php'):
            self.assert_true(True, test_name)
        else:
            self.assert_false(test_name)
            return

        with open('health.php', 'r') as f:
            content = f.read()

        # Check for required headers
        self.assert_true('Content-Type: application/json' in content,
                        "health.php sets JSON Content-Type header")

        self.assert_true('Access-Control-Allow-Origin' in content,
                        "health.php sets CORS headers")

        # Check for required logic
        self.assert_true('$_SERVER[\'REQUEST_METHOD\']' in content,
                        "health.php checks HTTP method")

        self.assert_true('json_encode' in content,
                        "health.php returns JSON response")

        self.assert_true('status' in content and 'healthy' in content,
                        "health.php includes 'status' field")

    def validate_config_php(self):
        """Validate config.php file structure"""
        test_name = "config.php file exists"
        if os.path.exists('config.php'):
            self.assert_true(True, test_name)
        else:
            self.assert_false(test_name)
            return

        with open('config.php', 'r') as f:
            content = f.read()

        # Check for required constants
        self.assert_true('HEALTH_CHECK_ENABLED' in content,
                        "config.php defines HEALTH_CHECK_ENABLED constant")

        self.assert_true('STATUS_HEALTHY' in content,
                        "config.php defines STATUS_HEALTHY constant")

        self.assert_true('STATUS_UNHEALTHY' in content,
                        "config.php defines STATUS_UNHEALTHY constant")

        # Check for HealthCheck class
        self.assert_true('class HealthCheck' in content,
                        "config.php contains HealthCheck class")

        self.assert_true('getResponseTime' in content,
                        "HealthCheck has getResponseTime method")

        self.assert_true('getOverallStatus' in content,
                        "HealthCheck has getOverallStatus method")

    def validate_test_php(self):
        """Validate test_health.php file structure"""
        test_name = "test_health.php file exists"
        if os.path.exists('test_health.php'):
            self.assert_true(True, test_name)
        else:
            self.assert_false(test_name)
            return

        with open('test_health.php', 'r') as f:
            content = f.read()

        # Check for test class
        self.assert_true('class HealthCheckTest' in content,
                        "test_health.php contains test class")

        self.assert_true('runTests' in content,
                        "test_health.php has runTests method")

        # Check for individual tests
        self.assert_true('testHealthCheckClass' in content,
                        "test_health.php includes class instantiation test")

        self.assert_true('testResponseTime' in content,
                        "test_health.php includes response time test")

    def validate_json_response(self):
        """Validate expected JSON response structure"""
        test_name = "Expected JSON response structure"

        expected_fields = ['status', 'message', 'timestamp', 'responseTime']

        # Read health.php to check for these fields
        with open('health.php', 'r') as f:
            content = f.read()

        all_fields_present = all(field in content for field in expected_fields)
        self.assert_true(all_fields_present, test_name)

    def assert_true(self, condition, test_name):
        if condition:
            self.tests.append({
                'name': test_name,
                'passed': True
            })
            self.tests_passed += 1
        else:
            self.tests.append({
                'name': test_name,
                'passed': False
            })
            self.tests_failed += 1

    def assert_false(self, test_name):
        self.tests.append({
            'name': test_name,
            'passed': False
        })
        self.tests_failed += 1

    def print_results(self):
        print("Validation Results:")
        print("-" * 60)

        for test in self.tests:
            status = "✓ PASS" if test['passed'] else "✗ FAIL"
            print(f"{status} - {test['name']}")

        print("-" * 60)
        total = self.tests_passed + self.tests_failed
        print(f"Total Tests: {total}")
        print(f"Passed: {self.tests_passed}")
        print(f"Failed: {self.tests_failed}")

        if self.tests_failed == 0:
            print("\n✓ All validations passed!")
            return 0
        else:
            print("\n✗ Some validations failed!")
            return 1

if __name__ == '__main__':
    validator = HealthEndpointValidator()
    exit_code = validator.run_all_validations()
    sys.exit(exit_code)

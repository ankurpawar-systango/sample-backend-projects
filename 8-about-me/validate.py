#!/usr/bin/env python3
"""
Validation script for the About Me endpoint.
This script validates the PHP endpoint code for correctness and compliance with specifications.
"""

import os
import re
import json
import sys


class AboutMeValidator:
    def __init__(self):
        self.tests_passed = 0
        self.tests_failed = 0
        self.tests = []

    def add_test(self, name, passed, message=""):
        status = "PASS" if passed else "FAIL"
        self.tests.append({
            "name": name,
            "passed": passed,
            "message": message
        })
        if passed:
            self.tests_passed += 1
        else:
            self.tests_failed += 1

    def test_file_exists(self, filename):
        """Test if required file exists"""
        passed = os.path.isfile(filename)
        self.add_test(f"File {filename} exists", passed)
        return passed

    def test_headers_present(self, filename):
        """Test if required headers are present in about-me.php"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        required_headers = [
            "Content-Type: application/json",
            "Access-Control-Allow-Origin: *",
            "Access-Control-Allow-Methods: GET, OPTIONS",
            "Access-Control-Allow-Headers: Content-Type"
        ]

        all_present = True
        for header in required_headers:
            if header not in content:
                all_present = False
                break

        self.add_test(
            "All required headers present in about-me.php",
            all_present,
            f"Expected headers: {required_headers}"
        )
        return all_present

    def test_options_handling(self, filename):
        """Test if OPTIONS request handling is present"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        has_options = "REQUEST_METHOD'] === 'OPTIONS'" in content
        self.add_test("OPTIONS preflight request handling present", has_options)
        return has_options

    def test_method_validation(self, filename):
        """Test if GET method validation is present"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        has_get_check = "REQUEST_METHOD'] !== 'GET'" in content
        has_405 = "http_response_code(405)" in content
        has_method_not_allowed = "Method Not Allowed" in content

        passed = has_get_check and has_405 and has_method_not_allowed
        self.add_test("GET method validation and 405 error handling present", passed)
        return passed

    def test_message_content(self, filename):
        """Test if the required message is in the code"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        required_message = "This is a sample site"
        has_message = required_message in content

        self.add_test(f"Required message '{required_message}' present", has_message)
        return has_message

    def test_json_response_structure(self, filename):
        """Test if JSON response structure is correct"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        required_fields = ["'status'", "'message'", "'timestamp'"]
        all_present = all(field in content for field in required_fields)

        self.add_test("JSON response has status, message, and timestamp fields", all_present)
        return all_present

    def test_error_handling(self, filename):
        """Test if try-catch error handling is present"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        has_try = "try {" in content
        has_catch = "catch (Exception $e)" in content

        passed = has_try and has_catch
        self.add_test("Try-catch error handling present", passed)
        return passed

    def test_http_status_codes(self, filename):
        """Test if HTTP status codes are set correctly"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        has_200 = "http_response_code(200)" in content
        has_500 = "http_response_code(500)" in content

        passed = has_200 and has_500
        self.add_test("HTTP status codes (200 and 500) are set", passed)
        return passed

    def test_config_file_structure(self, filename):
        """Test if config.php has required constants and class"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        required_defines = [
            "ENDPOINT_ENABLED",
            "ENDPOINT_MESSAGE",
            "STATUS_SUCCESS",
            "STATUS_ERROR"
        ]

        all_defines_present = all(f"define(\"{define}\"" in content for define in required_defines)
        has_class = "class AboutMe" in content

        passed = all_defines_present and has_class
        self.add_test("config.php has required constants and AboutMe class", passed)
        return passed

    def test_about_me_class_methods(self, filename):
        """Test if AboutMe class has required methods"""
        if not self.test_file_exists(filename):
            return False

        with open(filename, 'r') as f:
            content = f.read()

        required_methods = [
            "public function getMessage()",
            "public function getResponseTime()",
            "public function getTimestamp()",
            "public function isOperational()"
        ]

        all_methods_present = all(method in content for method in required_methods)
        self.add_test("AboutMe class has all required methods", all_methods_present)
        return all_methods_present

    def run_all_tests(self):
        """Run all validation tests"""
        print("=== About Me Endpoint Validation ===\n")

        # Test about-me.php
        print("Testing about-me.php...")
        self.test_file_exists("about-me.php")
        self.test_headers_present("about-me.php")
        self.test_options_handling("about-me.php")
        self.test_method_validation("about-me.php")
        self.test_message_content("about-me.php")
        self.test_json_response_structure("about-me.php")
        self.test_error_handling("about-me.php")
        self.test_http_status_codes("about-me.php")

        # Test config.php
        print("\nTesting config.php...")
        self.test_file_exists("config.php")
        self.test_config_file_structure("config.php")
        self.test_about_me_class_methods("config.php")

        # Print results
        self.print_results()

    def print_results(self):
        """Print test results summary"""
        print("\n" + "=" * 50)
        print("Test Results:\n")
        print("-" * 50)

        for test in self.tests:
            status = "✓ PASS" if test["passed"] else "✗ FAIL"
            print(f"{status} - {test['name']}")
            if test["message"]:
                print(f"       {test['message']}")

        print("-" * 50)
        total = self.tests_passed + self.tests_failed
        print(f"\nTotal Tests: {total}")
        print(f"Passed: {self.tests_passed}")
        print(f"Failed: {self.tests_failed}")

        if self.tests_failed == 0:
            print("\n✓ All validation tests passed!")
            return 0
        else:
            print("\n✗ Some validation tests failed!")
            return 1


if __name__ == "__main__":
    validator = AboutMeValidator()
    exit_code = validator.run_all_tests()
    sys.exit(exit_code)

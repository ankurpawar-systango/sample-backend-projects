#!/usr/bin/env python3
"""
Validation script for DL-5: Cookie Segregation Implementation
Tests the cookie consent endpoint for proper consent validation and persistence
"""

import os
import re
import json
import sys


class DL5Validator:
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

    def test_backend_cookie_consent_exists(self):
        """Test if cookie-consent.php file exists"""
        passed = os.path.isfile("cookie-consent.php")
        self.add_test("Backend cookie-consent.php file exists", passed)
        return passed

    def test_session_handling(self):
        """Test if session handling is implemented for persistence"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        has_session_start = "session_start()" in content
        has_session_storage = "_SESSION['cookie_consent']" in content
        has_get_consent_function = "function getConsentStateFromSession()" in content
        has_save_consent_function = "function saveConsentStateToSession()" in content

        passed = has_session_start and has_session_storage and has_get_consent_function and has_save_consent_function
        self.add_test(
            "Backend implements session-based persistent storage (DL-5)",
            passed,
            "session_start(), getConsentStateFromSession(), saveConsentStateToSession()"
        )
        return passed

    def test_validate_consent_uses_session(self):
        """Test if handleValidateConsent uses persisted session state"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        # Check if handleValidateConsent calls getConsentStateFromSession
        has_validate_function = "function handleValidateConsent(" in content
        uses_session_state = re.search(
            r'function handleValidateConsent.*?\$persistedConsent = getConsentStateFromSession\(\)',
            content,
            re.DOTALL
        )
        returns_403 = "http_response_code(403)" in content

        passed = has_validate_function and uses_session_state is not None and returns_403
        self.add_test(
            "handleValidateConsent reads from persistent session state (DL-5)",
            passed,
            "Should call getConsentStateFromSession() and return 403 for denied consent"
        )
        return passed

    def test_save_preferences_persists(self):
        """Test if handleSavePreferences actually persists to session"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        has_save_function = "function handleSavePreferences(" in content
        calls_save_session = re.search(
            r'function handleSavePreferences.*?saveConsentStateToSession\(',
            content,
            re.DOTALL
        )
        returns_persisted_flag = "'persistedToServer' => true" in content

        passed = has_save_function and calls_save_session is not None and returns_persisted_flag
        self.add_test(
            "handleSavePreferences persists consent to session (DL-5)",
            passed,
            "Should call saveConsentStateToSession() and set persistedToServer flag"
        )
        return passed

    def test_backend_categories(self):
        """Test if all three consent categories are defined"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        has_categories = "define('COOKIE_CATEGORIES'" in content
        has_essential = "'essential'" in content
        has_performance = "'performance'" in content
        has_preferences = "'preferences'" in content

        passed = has_categories and has_essential and has_performance and has_preferences
        self.add_test(
            "Backend defines all three cookie categories (essential, performance, preferences)",
            passed
        )
        return passed

    def test_frontend_cleanup_enhanced(self):
        """Test if frontend has enhanced cleanup function"""
        if not os.path.isfile("../About-page/script.js"):
            self.add_test("Frontend script.js file exists", False)
            return False

        with open("../About-page/script.js", 'r') as f:
            content = f.read()

        has_cleanup = "function cleanupNonConsentedCookies(" in content
        has_performance_cookies = "performanceCookies = [" in content
        has_preference_cookies = "preferenceCookies = [" in content
        has_scan_loop = "allCookies.forEach(" in content
        has_is_perf_func = "function isPerformanceCookie(" in content
        has_is_pref_func = "function isPreferenceCookie(" in content

        passed = (has_cleanup and has_performance_cookies and has_preference_cookies and
                  has_scan_loop and has_is_perf_func and has_is_pref_func)
        self.add_test(
            "Frontend has enhanced cleanupNonConsentedCookies with category detection (DL-5)",
            passed,
            "Should have isPerformanceCookie() and isPreferenceCookie() helper functions"
        )
        return passed

    def test_frontend_sync_on_load(self):
        """Test if frontend syncs preferences on page load"""
        if not os.path.isfile("../About-page/script.js"):
            return False

        with open("../About-page/script.js", 'r') as f:
            content = f.read()

        has_sync_function = "function syncCurrentPreferencesToBackend()" in content
        calls_in_init = "syncCurrentPreferencesToBackend()" in content

        passed = has_sync_function and calls_in_init
        self.add_test(
            "Frontend syncs preferences to backend on page load (DL-5)",
            passed,
            "Should have syncCurrentPreferencesToBackend() called in DOMContentLoaded"
        )
        return passed

    def test_frontend_validate_uses_backend(self):
        """Test if frontend validateConsentWithBackend properly uses backend"""
        if not os.path.isfile("../About-page/script.js"):
            return False

        with open("../About-page/script.js", 'r') as f:
            content = f.read()

        has_validate = "function validateConsentWithBackend(" in content
        uses_validate_action = "'action': 'validate'" in content
        sends_consent_level = "'consent_level': category" in content
        checks_403 = "response.status === 403" in content

        passed = has_validate and uses_validate_action and sends_consent_level and checks_403
        self.add_test(
            "Frontend validateConsentWithBackend properly calls backend (DL-5)",
            passed,
            "Should send consent_level and handle 403 responses"
        )
        return passed

    def test_backend_categories_validation(self):
        """Test if backend validates all category types"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        # Check that all categories are validated
        essential_check = re.search(r"\$consentLevel === 'essential'", content)
        perf_check = re.search(r"\$consentLevel === 'performance'", content)
        pref_check = re.search(r"\$consentLevel === 'preferences'", content)
        in_array_check = re.search(r"in_array\(\$consentLevel, COOKIE_CATEGORIES\)", content)

        passed = essential_check is not None and in_array_check is not None
        self.add_test(
            "Backend validates all cookie category types",
            passed,
            "Should check essential, performance, preferences against COOKIE_CATEGORIES"
        )
        return passed

    def test_cors_headers_present(self):
        """Test if CORS headers are set for cross-origin cookie consent"""
        if not self.test_backend_cookie_consent_exists():
            return False

        with open("cookie-consent.php", 'r') as f:
            content = f.read()

        has_origin = "Access-Control-Allow-Origin" in content
        has_methods = "Access-Control-Allow-Methods" in content
        has_headers = "Access-Control-Allow-Headers" in content

        passed = has_origin and has_methods and has_headers
        self.add_test(
            "Backend sets proper CORS headers for cookie consent",
            passed
        )
        return passed

    def run_all_tests(self):
        """Run all DL-5 validation tests"""
        print("=== DL-5: Cookie Segregation Implementation Validation ===\n")

        print("Backend Tests:")
        print("-" * 50)
        self.test_backend_cookie_consent_exists()
        self.test_session_handling()
        self.test_validate_consent_uses_session()
        self.test_save_preferences_persists()
        self.test_backend_categories()
        self.test_backend_categories_validation()
        self.test_cors_headers_present()

        print("\nFrontend Tests:")
        print("-" * 50)
        self.test_frontend_cleanup_enhanced()
        self.test_frontend_sync_on_load()
        self.test_frontend_validate_uses_backend()

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
            print("\n✓ All DL-5 validation tests passed!")
            return 0
        else:
            print("\n✗ Some DL-5 validation tests failed!")
            return 1


if __name__ == "__main__":
    validator = DL5Validator()
    exit_code = validator.run_all_tests()
    sys.exit(exit_code)

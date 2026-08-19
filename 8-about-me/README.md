# About Me Endpoint

## Overview

The About Me endpoint provides a simple HTTP endpoint that returns information about the backend service. It follows the same coding patterns and conventions as other endpoints in this project.

## Features

- **GET Request Handler**: Accepts HTTP GET requests
- **CORS Support**: Includes proper CORS headers for cross-origin requests
- **Preflight Handling**: Supports OPTIONS requests for CORS preflight
- **Method Validation**: Returns 405 Method Not Allowed for non-GET requests
- **JSON Response**: Returns a properly formatted JSON response with status, message, and timestamp
- **Error Handling**: Includes exception handling for error scenarios

## Endpoint Details

### URL
```
GET /about-me
```

### Response Format
```json
{
  "status": "success",
  "message": "This is a sample site",
  "timestamp": "2024-08-07 12:14:00"
}
```

### HTTP Methods

- **GET**: Returns the about-me information (200 OK)
- **OPTIONS**: Handles CORS preflight requests (200 OK)
- **Other Methods**: Returns 405 Method Not Allowed

### Response Headers

- `Content-Type: application/json`
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type`

## Files

- **about-me.php**: Main endpoint implementation
- **config.php**: Configuration and utility classes
- **test_about_me.php**: Unit tests for the endpoint
- **cookie-consent.php**: Cookie consent policy and preference management endpoint
- **test_cookie_consent.php**: Unit tests for the cookie-consent endpoint (added for DUAL-55)
- **README.md**: This file

## Usage

### Basic Request
```bash
curl http://localhost/about-me
```

### With Verbose Output
```bash
curl -v http://localhost/about-me
```

### Testing OPTIONS (CORS Preflight)
```bash
curl -X OPTIONS http://localhost/about-me -v
```

### Testing Invalid Method
```bash
curl -X POST http://localhost/about-me
```

## Testing

To run the unit tests:

```bash
php test_about_me.php
```

The test suite covers:
- Class instantiation
- Message content verification
- Response time calculation
- Endpoint operational status
- Timestamp format validation
- JSON response structure
- CORS header configuration
- OPTIONS request handling
- Non-GET method error handling

## Configuration

Configuration constants are defined in `config.php`:

- `ENDPOINT_ENABLED`: Whether the endpoint is enabled
- `ENDPOINT_VERSION`: Version of the endpoint
- `ENDPOINT_NAME`: Name of the service
- `ENDPOINT_MESSAGE`: The message returned by the endpoint
- `HTTP_OK`, `HTTP_METHOD_NOT_ALLOWED`, `HTTP_SERVER_ERROR`: HTTP status codes
- `STATUS_SUCCESS`, `STATUS_ERROR`: Response status values

## Error Handling

The endpoint includes try-catch error handling that returns a 500 error with details if an exception occurs:

```json
{
  "status": "error",
  "message": "An error occurred",
  "error": "Error details here",
  "timestamp": "2024-08-07 12:14:00"
}
```

## Cookie Consent Endpoint (DUAL-55, Enhanced by DL-40)

The `cookie-consent.php` endpoint manages cookie preferences and policy information with segregated cookie categories. This endpoint supports the cookie notification feature on the about page with clear distinction between required and optional cookies.

### Features
- **DL-1**: Cookie segregation by category (essential, performance, preferences)
- **DL-5**: Server-side persistent session storage for consent state
- **DL-40**: Enhanced UI support for segregated cookie categories with examples

### URL
```
GET /cookie-consent             # Get cookie policy information with segregation
GET /cookie-consent?state=true  # Get current consent state from server
POST /cookie-consent            # Save or validate user cookie preferences
```

### GET Response Format (Cookie Policy)
```json
{
  "status": "success",
  "timestamp": "2024-08-07 12:14:00",
  "cookiePolicy": {
    "essential": {
      "name": "Essential Cookies",
      "required": true,
      "description": "Required for basic functionality and security",
      "purpose": ["Session management", "Security", "Basic site functionality"],
      "examples": ["PHPSESSID", "csrf_token", "session_id"]
    },
    "performance": {
      "name": "Performance Cookies",
      "required": false,
      "description": "Help us understand how you use our site",
      "purpose": ["Usage analytics", "Performance monitoring", "Error tracking"],
      "examples": ["_ga", "_gid", "_analytics"]
    },
    "preferences": {
      "name": "Preference Cookies",
      "required": false,
      "description": "Remember your choices and settings",
      "purpose": ["User preferences", "Language settings", "Theme preferences"],
      "examples": ["_theme", "_language", "_preferences"]
    }
  },
  "privacyPolicyUrl": "/privacy",
  "termsUrl": "/terms",
  "contactEmail": "privacy@example.com",
  "lastUpdated": "2025-01-01",
  "segregationEnabled": true
}
```

### GET Response Format (Consent State)
```json
{
  "status": "success",
  "timestamp": "2024-08-07 12:14:00",
  "consentState": {
    "essential": true,
    "performance": false,
    "preferences": true
  },
  "message": "Current consent state retrieved successfully"
}
```

### POST Request Formats

#### Save Cookie Preferences
```json
{
  "action": "save",
  "essential": true,
  "performance": true,
  "preferences": false
}
```

#### Validate Consent Level (DL-5)
```json
{
  "action": "validate",
  "consent_level": "performance"
}
```

### POST Response Formats

#### Save Success Response
```json
{
  "status": "success",
  "message": "Cookie preferences saved successfully",
  "timestamp": "2024-08-07 12:14:00",
  "saved_preferences": {
    "essential": true,
    "performance": true,
    "preferences": false,
    "timestamp": "2024-08-07 12:14:00",
    "userAgent": "Mozilla/5.0..."
  },
  "nextReviewDate": "2025-08-07 12:14:00",
  "segregationEnforced": true,
  "persistedToServer": true
}
```

#### Validate Success Response
```json
{
  "status": "success",
  "allowed": true,
  "message": "Consent verified for performance cookies",
  "category": "performance",
  "timestamp": "2024-08-07 12:14:00"
}
```

#### Validate Failure Response (403)
```json
{
  "status": "error",
  "allowed": false,
  "message": "Consent not given for performance cookies. Please update your cookie preferences.",
  "category": "performance",
  "requiredAction": "update_preferences",
  "timestamp": "2024-08-07 12:14:00"
}
```

### Cookie Segregation (DL-40)

The cookie consent system now supports clear segregation of cookies into three categories:

1. **Essential/Required Cookies** (DL-1)
   - Cannot be disabled by users
   - Always required for basic functionality
   - Examples: PHPSESSID, csrf_token, session_id
   - Marked with `required: true` in policy

2. **Performance Cookies** (Optional)
   - Used for analytics and tracking
   - User can consent or decline
   - Examples: _ga, _gid, analytics tracking cookies
   - Default: disabled (requires user consent)

3. **Preference Cookies** (Optional)
   - Remember user choices and settings
   - User can consent or decline
   - Examples: _theme, _language, user_preferences
   - Default: disabled (requires user consent)

#### Server-Side Validation (DL-5)

The `/cookie-consent.php` endpoint with `action: validate` allows server-side enforcement of cookie consent:

```php
// Example: Check if user has consented to performance cookies
POST /cookie-consent
{
  "action": "validate",
  "consent_level": "performance"
}

// Response: 200 OK if allowed, 403 Forbidden if not
```

This ensures that only users who have explicitly consented can trigger performance tracking operations.

### Testing Cookie Consent
To run the cookie consent endpoint tests:

```bash
php test_cookie_consent.php
```

Or test via curl:
```bash
# Get cookie policy
curl http://localhost/cookie-consent

# Get current consent state (server-side)
curl http://localhost/cookie-consent?state=true

# Save preferences
curl -X POST http://localhost/cookie-consent \
  -H "Content-Type: application/json" \
  -d '{"action": "save", "essential": true, "performance": true, "preferences": false}'

# Validate consent level
curl -X POST http://localhost/cookie-consent \
  -H "Content-Type: application/json" \
  -d '{"action": "validate", "consent_level": "performance"}'
```

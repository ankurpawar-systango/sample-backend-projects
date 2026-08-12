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

## Cookie Consent Endpoint (DUAL-55)

The `cookie-consent.php` endpoint manages cookie preferences and policy information. This endpoint supports the cookie notification feature on the about page.

### URL
```
GET /cookie-consent    # Get cookie policy information
POST /cookie-consent   # Save user cookie preferences
```

### GET Response Format
```json
{
  "status": "success",
  "timestamp": "2024-08-07 12:14:00",
  "cookiePolicy": {
    "essential": {
      "name": "Essential Cookies",
      "required": true,
      "description": "Required for basic functionality and security",
      "purpose": ["Session management", "Security", "Basic site functionality"]
    },
    "performance": {
      "name": "Performance Cookies",
      "required": false,
      "description": "Help us understand how you use our site",
      "purpose": ["Usage analytics", "Performance monitoring", "Error tracking"]
    },
    "preferences": {
      "name": "Preference Cookies",
      "required": false,
      "description": "Remember your choices and settings",
      "purpose": ["User preferences", "Language settings", "Theme preferences"]
    }
  },
  "privacyPolicyUrl": "/privacy",
  "termsUrl": "/terms",
  "contactEmail": "privacy@example.com",
  "lastUpdated": "2025-01-01"
}
```

### POST Request Format
```json
{
  "essential": true,
  "performance": true,
  "preferences": false
}
```

### POST Response Format
```json
{
  "status": "success",
  "message": "Cookie preferences saved successfully",
  "timestamp": "2024-08-07 12:14:00",
  "saved_preferences": {
    "essential": true,
    "performance": true,
    "preferences": false,
    "timestamp": "2024-08-07 12:14:00"
  },
  "nextReviewDate": "2025-08-07 12:14:00"
}
```

### Testing Cookie Consent
To run the cookie consent endpoint tests:

```bash
php test_cookie_consent.php
```

Or access via URL:
```bash
curl http://localhost/cookie-consent
```

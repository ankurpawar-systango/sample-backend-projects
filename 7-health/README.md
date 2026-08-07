# Health Status API Module

## Overview

This module provides a simple health check endpoint for monitoring the backend service status. It follows REST API conventions and returns JSON responses.

## Features

- **GET /7-health/api.php** - Returns the current health status of the backend
- **CORS Support** - Cross-Origin Resource Sharing enabled for frontend consumption
- **JSON Response** - Returns structured JSON data with status, timestamp, and service information
- **HTTP 200 Success** - Returns HTTP 200 status code when healthy

## Usage

### Health Status Endpoint

**URL:** `GET /7-health/api.php`

**Response (HTTP 200):**
```json
{
  "status": "ok",
  "timestamp": "2026-08-07T10:30:45+00:00",
  "service": "Backend Health Check",
  "version": "1.0.0"
}
```

### Alternative Health Endpoint

**URL:** `GET /7-health/health.php`

Redirects to `/7-health/api.php` for convenience.

## CORS Headers

The API endpoint includes CORS headers to allow cross-origin requests:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, OPTIONS
Access-Control-Allow-Headers: Content-Type
Content-Type: application/json
```

## Testing

### Unit Tests

Run the test file to verify the API functionality:

```bash
php 7-health/test.php
```

Or access via HTTP with test parameter:

```bash
curl "http://localhost/7-health/test.php?test=1"
```

### Manual Testing

Test the API endpoint with curl:

```bash
curl -X GET http://localhost/7-health/api.php
```

Test with headers:

```bash
curl -X GET \
  -H "Accept: application/json" \
  http://localhost/7-health/api.php
```

Test OPTIONS request (CORS preflight):

```bash
curl -X OPTIONS http://localhost/7-health/api.php
```

## Integration with Frontend

The frontend can fetch the health status using JavaScript:

```javascript
fetch('http://localhost/7-health/api.php')
  .then(response => response.json())
  .then(data => {
    console.log('Health Status:', data.status);
    console.log('Timestamp:', data.timestamp);
  })
  .catch(error => console.error('Error:', error));
```

## Files

- `api.php` - Main health endpoint that returns JSON status
- `health.php` - Redirect endpoint for convenience
- `test.php` - Unit tests for the API
- `README.md` - This documentation file

## Requirements

- PHP 7.2 or higher
- Web server (Apache, Nginx, etc.)
- No external dependencies

## Error Handling

### Method Not Allowed (405)

If a method other than GET or OPTIONS is used:

```json
{
  "status": "error",
  "message": "Method Not Allowed"
}
```

## Version History

### Version 1.0.0
- Initial release
- Health endpoint implementation
- CORS support
- Unit tests

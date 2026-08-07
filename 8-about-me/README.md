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

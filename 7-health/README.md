# Health Status Endpoint

A simple PHP health check endpoint that returns the health status of the backend service.

**Part of DUAL-30: Create health status monitoring full-stack application**

## Features

- Returns JSON-formatted health status
- Includes response time measurement
- Server timestamp and PHP version information
- CORS enabled for frontend communication
- Simple and lightweight implementation
- Comprehensive unit tests

## Endpoint

### GET /7-health/health.php

Returns the current health status of the backend service.

**Response Format:**
```json
{
  "status": "healthy",
  "message": "Backend service is running",
  "timestamp": "2024-01-15 10:30:45",
  "responseTime": "1.25 ms",
  "php_version": "7.4.3",
  "uptime": "Service is operational"
}
```

## Usage

### Direct Access
Visit `http://localhost/sample-backend-projects/7-health/health.php` in your browser or via curl:

```bash
curl http://localhost/sample-backend-projects/7-health/health.php
```

### Frontend Integration
Fetch from JavaScript:

```javascript
fetch('http://localhost/sample-backend-projects/7-health/health.php')
  .then(response => response.json())
  .then(data => {
    console.log('Health Status:', data.status);
    console.log('Message:', data.message);
  })
  .catch(error => console.error('Error:', error));
```

## Configuration

The `config.php` file contains:
- Health check settings and version
- HTTP response code constants
- `HealthCheck` utility class for custom health checks

## Testing

Run the unit tests:

```bash
php test_health.php
```

The test suite includes:
- HealthCheck class instantiation
- Response time calculation
- Health status determination
- Multiple health checks validation

## Files

- `health.php` - Main health endpoint
- `config.php` - Configuration and HealthCheck class
- `test_health.php` - Unit tests
- `README.md` - This file

## HTTP Methods

- `GET` - Returns health status (✓ supported)
- `OPTIONS` - CORS preflight (✓ supported)
- Other methods - Returns 405 Method Not Allowed

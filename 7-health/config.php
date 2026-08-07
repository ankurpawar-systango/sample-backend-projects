<?php
/**
 * Health Check Configuration
 *
 * Configuration for the health status endpoint
 */

// Health check settings
define("HEALTH_CHECK_ENABLED", true);
define("HEALTH_CHECK_VERSION", "1.0.0");
define("SERVICE_NAME", "Backend Health Service");

// Response codes
define("HTTP_OK", 200);
define("HTTP_METHOD_NOT_ALLOWED", 405);
define("HTTP_SERVER_ERROR", 500);

// Health status constants
define("STATUS_HEALTHY", "healthy");
define("STATUS_UNHEALTHY", "unhealthy");

/**
 * HealthCheck Class
 *
 * Provides utilities for health status checks
 */
class HealthCheck
{
    private $startTime;
    private $checks = [];

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Add a custom health check
     *
     * @param string $name Name of the check
     * @param boolean $passed Whether the check passed
     * @param string $message Optional message
     */
    public function addCheck($name, $passed, $message = "")
    {
        $this->checks[$name] = [
            'passed' => $passed,
            'message' => $message
        ];
    }

    /**
     * Get the response time in milliseconds
     *
     * @return float Response time in milliseconds
     */
    public function getResponseTime()
    {
        $endTime = microtime(true);
        return round(($endTime - $this->startTime) * 1000, 2);
    }

    /**
     * Get all health checks
     *
     * @return array Array of all checks
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * Determine overall health status
     *
     * @return string Overall status
     */
    public function getOverallStatus()
    {
        foreach ($this->checks as $check) {
            if (!$check['passed']) {
                return STATUS_UNHEALTHY;
            }
        }
        return STATUS_HEALTHY;
    }
}

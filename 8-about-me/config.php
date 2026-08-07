<?php
/**
 * About Me Endpoint Configuration
 *
 * Configuration for the about-me endpoint
 */

// Endpoint settings
define("ENDPOINT_ENABLED", true);
define("ENDPOINT_VERSION", "1.0.0");
define("ENDPOINT_NAME", "About Me Service");
define("ENDPOINT_MESSAGE", "This is a sample site");

// Response codes
define("HTTP_OK", 200);
define("HTTP_METHOD_NOT_ALLOWED", 405);
define("HTTP_SERVER_ERROR", 500);

// Status constants
define("STATUS_SUCCESS", "success");
define("STATUS_ERROR", "error");

/**
 * AboutMe Class
 *
 * Provides utilities for the about-me endpoint
 */
class AboutMe
{
    private $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Get the message for the about-me endpoint
     *
     * @return string The about-me message
     */
    public function getMessage()
    {
        return ENDPOINT_MESSAGE;
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
     * Get the current timestamp
     *
     * @return string Current timestamp
     */
    public function getTimestamp()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Verify the endpoint is operational
     *
     * @return boolean True if operational
     */
    public function isOperational()
    {
        return ENDPOINT_ENABLED;
    }
}

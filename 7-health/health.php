<?php
/**
 * Health Status Endpoint
 *
 * Redirect all requests to the API endpoint
 */

// Redirect to the API endpoint
header('Location: ./api.php', true, 301);
exit;

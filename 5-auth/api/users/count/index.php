<?php
/**
 * GET /api/users/count
 *
 * Returns the total count of users in the database.
 * Requires admin authentication.
 */

session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin access required.'
    ]);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

try {
    require_once '../../config.php';
    $db = new Database();

    // Get total user count
    $result = $db->select('users', 'COUNT(*) as count');

    if (is_string($result)) {
        // Error from the database
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $result
        ]);
        exit;
    }

    if (empty($result)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to query user count'
        ]);
        exit;
    }

    $count = intval($result[0]['count']);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => $count,
        'message' => 'User count retrieved successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>

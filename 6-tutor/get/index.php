<?php
// Enable CORS for frontend integration
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include "../config.php";
$db = new Database();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === "GET") {
    // Get query parameter for specific tutor or all tutors
    $tutorId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($tutorId) {
        // Fetch specific tutor
        $tutors = $db->select('tutors', '*', 'id = ?', [$tutorId], 'i');

        if ($tutors && isset($tutors[0])) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $tutors[0],
                'message' => 'Tutor retrieved successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'TUTOR_NOT_FOUND',
                'message' => 'Tutor not found'
            ]);
        }
    } else {
        // Fetch all tutors
        $tutors = $db->select('tutors', '*');

        if (is_array($tutors)) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $tutors,
                'count' => count($tutors),
                'message' => 'All tutors retrieved successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'DATABASE_ERROR',
                'message' => $tutors
            ]);
        }
    }
    exit;
}

// For non-GET requests
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'METHOD_NOT_ALLOWED',
    'message' => 'Only GET requests are allowed'
]);

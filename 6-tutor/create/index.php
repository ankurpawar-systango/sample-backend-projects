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

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Validate required fields
    $errors = [];

    // Get input data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $about = isset($_POST['about']) ? trim($_POST['about']) : '';

    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid';
    } else {
        // Check for email uniqueness
        $existingTutors = $db->select('tutors', '*', 'email = ?', [$email], 's');
        if ($existingTutors && count($existingTutors) > 0) {
            $errors[] = 'Email already exists in the system';
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'VALIDATION_ERROR',
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }

    // Prepare data for insertion
    $tutorData = [
        'name' => $name,
        'email' => $email,
        'bio' => $bio,
        'about' => $about,
        'is_first_tutor' => false
    ];

    // Insert new tutor
    $result = $db->insert('tutors', $tutorData);

    if (is_string($result)) {
        // Error occurred
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'DATABASE_ERROR',
            'message' => $result
        ]);
    } else {
        // Successfully inserted
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $result,
                'name' => $name,
                'email' => $email,
                'bio' => $bio,
                'about' => $about,
                'is_first_tutor' => false
            ],
            'message' => 'Tutor created successfully'
        ]);
    }
    exit;
}

// For non-POST requests
http_response_code(405);
echo json_encode([
    'success' => false,
    'error' => 'METHOD_NOT_ALLOWED',
    'message' => 'Only POST requests are allowed'
]);

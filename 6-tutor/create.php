<?php
/**
 * Admin Panel - Tutor Creation Form
 *
 * Allows administrators to create new tutors by submitting a form.
 * Form validates input and submits to POST /6-tutor/create/ endpoint.
 */

// Initialize message variables
$successMessage = '';
$errorMessage = '';
$formData = [
    'name' => '',
    'email' => '',
    'bio' => '',
    'about' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';

    $db = new Database();

    // Get and sanitize form inputs
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $about = isset($_POST['about']) ? trim($_POST['about']) : '';

    // Validate inputs
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name is required';
    }

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

    if (empty($errors)) {
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
            $errorMessage = 'Error creating tutor: ' . $result;
        } else {
            // Successfully inserted
            $successMessage = "Tutor '$name' created successfully with ID: $result";
            // Reset form data
            $formData = [
                'name' => '',
                'email' => '',
                'bio' => '',
                'about' => ''
            ];
        }
    } else {
        // Validation errors
        $errorMessage = 'Validation failed: ' . implode(', ', $errors);
        // Keep form data for resubmission
        $formData = [
            'name' => $name,
            'email' => $email,
            'bio' => $bio,
            'about' => $about
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Tutor - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            border: none;
        }
        .card-header h1 {
            color: white;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-top: 12px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 15px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            margin-top: 20px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #63398a 100%);
        }
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .form-instructions {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .card-body {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header p-4">
                <h1>Create New Tutor</h1>
                <p class="mb-0" style="color: rgba(255,255,255,0.9); font-size: 14px;">Admin Panel - Tutor Management</p>
            </div>
            <div class="card-body">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <?php echo htmlspecialchars($successMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> <?php echo htmlspecialchars($errorMessage); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div>
                        <label for="name" class="form-label">Name *</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($formData['name']); ?>"
                            required
                            placeholder="Enter tutor's full name">
                        <div class="form-instructions">Required field. Full name of the tutor.</div>
                    </div>

                    <div>
                        <label for="email" class="form-label">Email *</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            required
                            placeholder="Enter tutor's email address">
                        <div class="form-instructions">Required field. Must be unique in the system. Valid email format required.</div>
                    </div>

                    <div>
                        <label for="bio" class="form-label">Bio</label>
                        <textarea
                            class="form-control"
                            id="bio"
                            name="bio"
                            placeholder="Enter tutor's bio (optional)"><?php echo htmlspecialchars($formData['bio']); ?></textarea>
                        <div class="form-instructions">Optional. Brief biography or professional summary.</div>
                    </div>

                    <div>
                        <label for="about" class="form-label">About</label>
                        <textarea
                            class="form-control"
                            id="about"
                            name="about"
                            placeholder="Enter information about the tutor (optional)"><?php echo htmlspecialchars($formData['about']); ?></textarea>
                        <div class="form-instructions">Optional. Additional information about the tutor.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Tutor</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

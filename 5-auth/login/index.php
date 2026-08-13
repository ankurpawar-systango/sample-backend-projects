<?php
// DL-14: Initialize session with consent support
require_once dirname(__DIR__) . ‘/../8-about-me/cookie-helper.php’;
initializeSessionWithConsentSupport();

// Enable CORS for frontend integration
header(‘Access-Control-Allow-Origin: *’);
header(‘Access-Control-Allow-Methods: GET, POST, OPTIONS’);
header(‘Access-Control-Allow-Headers: Content-Type, X-Requested-With’);

if ($_SERVER[‘REQUEST_METHOD’] === ‘OPTIONS’) {
    http_response_code(200);
    exit;
}

// Check if user is already logged in (for page loads, not API calls)
if (!isset($_SERVER[‘HTTP_X_REQUESTED_WITH’]) && isset($_SESSION[‘loggedin’]) && $_SESSION[‘loggedin’] === true) {
    if ($_SESSION[‘user’][‘role’] === ‘admin’) {
        header(‘Location: ../admin/’);
        exit;
    } elseif ($_SESSION[‘user’][‘role’] === ‘user’) {
        header(‘Location: ../’);
        exit;
    } else {
        exit("Bunday role mavjud emas!");
    }
}

include "../config.php";
$db = new Database();

if ($_SERVER[‘REQUEST_METHOD’] === "POST") {
    header(‘Content-Type: application/json’);

    // Handle both form data and JSON input
    $input = $_POST;
    if (empty($_POST) && $_SERVER[‘CONTENT_TYPE’] === ‘application/json’) {
        $input = json_decode(file_get_contents(‘php://input’), true) ?? [];
    }

    $username = strtolower(trim($input[‘username’] ?? ‘’));
    $password = $input[‘password’] ?? ‘’;

    if (empty(trim($username)) || empty(trim($password))) {
        http_response_code(400);
        echo json_encode([
            ‘success’ => false,
            ‘error’ => ‘MISSING_CREDENTIALS’,
            ‘title’ => ‘⚠️ Diqqat!’,
            ‘message’ => "Iltimos, login va parol maydonlarini to’ldiring!"
        ]);
        exit;
    }

    $user = $db->select(‘users’, ‘*’, ‘username = ?’, [$username], ‘s’);

    if ($user && isset($user[0])) {
        $id = $user[0][‘id’];
        $name = $user[0][‘name’];
        $role = $user[0][‘role’];
        $hashedPassword = $user[0][‘password’];

        if (password_verify($password, $hashedPassword)) {
            $_SESSION[‘loggedin’] = true;
            $_SESSION[‘user’] = [
                ‘id’ => $id,
                ‘name’ => $name,
                ‘username’ => $username,
                ‘role’ => $role,
            ];

            http_response_code(200);
            echo json_encode([
                ‘success’ => true,
                ‘token’ => session_id(),
                ‘user’ => [
                    ‘id’ => $id,
                    ‘name’ => $name,
                    ‘username’ => $username,
                    ‘role’ => $role
                ],
                ‘title’ => ‘✅ Muvaffaqiyat!’,
                ‘message’ => ‘Tizimga kirdingiz!’,
                ‘redirect’ => $role === ‘admin’ ? ‘../admin/’ : ‘../’
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode([
                ‘success’ => false,
                ‘error’ => ‘INVALID_PASSWORD’,
                ‘title’ => ‘❌ Xato parol!’,
                ‘message’ => ‘Noto’g’ri parol, qayta urinib ko’ring.’
            ]);
            exit;
        }
    } else {
        http_response_code(404);
        echo json_encode([
            ‘success’ => false,
            ‘error’ => ‘USER_NOT_FOUND’,
            ‘title’ => ‘❌ Foydalanuvchi topilmadi!’,
            ‘message’ => "Bunday foydalanuvchi topilmadi."
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Login</h2>
                        <form id="loginForm" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username"
                                    placeholder="Enter username" />
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Enter password" />
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <p class="text-center mt-3">Don't have an account? <a href="../signup/">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: result.title,
                            text: result.message,
                            confirmButtonText: 'OK',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: result.title,
                            text: result.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Tarmoq xatosi',
                        text: 'Server bilan bog‘lanishda muammo yuz berdi.'
                    });
                    console.error('Fetch error:', error);
                });
        });
    </script>
</body>

</html>
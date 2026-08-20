<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login/');
    exit;
}

// Get user count
$userCount = 0;
$countError = null;

try {
    require_once '../config.php';
    $db = new Database();
    $result = $db->select('users', 'COUNT(*) as count');

    if (!is_string($result) && !empty($result)) {
        $userCount = intval($result[0]['count']);
    } else {
        $countError = is_string($result) ? $result : 'Failed to fetch user count';
    }
} catch (Exception $e) {
    $countError = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-section {
            color: white;
            margin-bottom: 30px;
        }
        .header-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .stat-label {
            color: #667eea;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #333;
            margin: 10px 0;
        }
        .stat-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .card-description {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .action-buttons {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .action-buttons h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .btn-group-custom {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="header-section">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>! Manage your platform here.</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-label">Total Users</div>
                <?php if ($countError): ?>
                    <div class="alert alert-warning mt-2 mb-0" role="alert">
                        <small><?= htmlspecialchars($countError) ?></small>
                    </div>
                <?php else: ?>
                    <div class="stat-number"><?= $userCount ?></div>
                    <div class="card-description">
                        <?php if ($userCount === 1): ?>
                            1 user registered
                        <?php else: ?>
                            <?= $userCount ?> users registered
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <h3>Admin Actions</h3>
            <div class="btn-group-custom">
                <a href="../" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="../logout/" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
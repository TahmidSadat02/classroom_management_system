<?php
session_start();


require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/User.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$userId = $_GET['id'] ?? '';
if (empty($userId)) {
    header('Location: index.php');
    exit;
}


$user = getUserById($userId);
if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Delete User</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <style>
        .admin-warning {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        .admin-user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .admin-user-info div {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="admin-form-container">
        <div class="admin-form-header">
            <h1>Delete User</h1>
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>

        <div class="admin-warning">
            <h3>⚠️ Warning</h3>
            <p>This action cannot be undone! You are about to permanently delete this user account.</p>
        </div>

        <div class="admin-user-info">
            <h3>User to be deleted:</h3>
            <div><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
            <div><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
            <div><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></div>
            <div><strong>Created:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
        </div>

        <form method="POST" action="../../../controllers/admin/userController.php" onsubmit="return confirm('Are you absolutely sure you want to delete this user? This action cannot be undone!');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($user['id']); ?>">

            <div class="admin-form-actions">
                <button type="submit" class="btn btn-danger">Yes, Delete User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>
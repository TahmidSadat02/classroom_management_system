<?php
session_start();


require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/User.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


if (isset($_SESSION['view_user'])) {
    $user = $_SESSION['view_user'];
    unset($_SESSION['view_user']); 
} else {
    
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
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View User</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <style>
       
        .admin-profile { margin-top: 20px; }
        .admin-profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; }
        .admin-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold; }
        .admin-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .admin-info-item { display: flex; flex-direction: column; gap: 5px; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .admin-info-item label { font-weight: bold; color: #495057; font-size: 0.9rem; }
        .admin-info-item span { color: #333; font-size: 1rem; }
        .role { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        .role.teacher { background: #28a745; color: white; }
        .role.student { background: #17a2b8; color: white; }
        .role.admin { background: #6f42c1; color: white; }
        @media (max-width: 768px) { .admin-profile-header { flex-direction: column; text-align: center; } .admin-info-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-form-container large">
        <div class="admin-form-header">
            <h1>User Profile</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">← Back</a>
                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">Edit</a>
            </div>
        </div>

        <div class="admin-profile">
            <div class="admin-profile-header">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p><span class="role <?php echo htmlspecialchars($user['user_type']); ?>"><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></span></p>
                </div>
            </div>

            <div class="admin-info-grid">
                <div class="admin-info-item">
                    <label>User ID:</label>
                    <span><?php echo htmlspecialchars($user['id']); ?></span>
                </div>
                <div class="admin-info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="admin-info-item">
                    <label>First Name:</label>
                    <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                </div>
                <div class="admin-info-item">
                    <label>Last Name:</label>
                    <span><?php echo htmlspecialchars($user['last_name']); ?></span>
                </div>
                <div class="admin-info-item">
                    <label>Role:</label>
                    <span class="role <?php echo htmlspecialchars($user['user_type']); ?>"><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></span>
                </div>
                <div class="admin-info-item">
                    <label>Created:</label>
                    <span><?php echo date('M d, Y g:i A', strtotime($user['created_at'])); ?></span>
                </div>
                <?php if (isset($user['updated_at']) && $user['updated_at']): ?>
                <div class="admin-info-item">
                    <label>Last Updated:</label>
                    <span><?php echo date('M d, Y g:i A', strtotime($user['updated_at'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
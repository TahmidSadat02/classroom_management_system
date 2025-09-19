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
    <title>Edit User</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <style>
       
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .admin-form-container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .admin-form-container.large { max-width: 900px; }
        .admin-form-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef; }
        .admin-form-header h1 { margin: 0; color: #333; font-size: 1.8rem; }
        .form-section { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e9ecef; }
        .form-section:last-of-type { border-bottom: none; }
        .form-section h3 { color: #333; margin-bottom: 1rem; font-size: 1.25rem; border-bottom: 2px solid #667eea; padding-bottom: 0.5rem; }
        .admin-form-group, .form-group { margin-bottom: 15px; }
        .admin-form-group label, .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        .admin-form-group input, .admin-form-group select, .form-group input, .form-group select, .form-group textarea, .form-control { width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px; font-size: 1rem; box-sizing: border-box; }
        .admin-form-row, .form-row { display: flex; gap: 15px; }
        .admin-form-row .admin-form-group, .form-row .form-group { flex: 1; }
        .admin-form-actions, .form-actions { margin-top: 20px; padding-top: 15px; border-top: 1px solid #e9ecef; display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; font-size: 1rem; cursor: pointer; margin-right: 10px; transition: all 0.3s ease; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .info-row { display: flex; gap: 2rem; flex-wrap: wrap; }
        .info-item { display: flex; flex-direction: column; gap: 0.25rem; }
        .info-item label { font-weight: 600; color: #495057; font-size: 0.9rem; }
        .info-item span { color: #333; }
        .password-hint { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        .is-invalid { border-color: #dc3545; }
        .error-message { color: #dc3545; font-size: 0.8rem; margin-top: 0.25rem; padding: 5px; background-color: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; border-radius: 3px; display: none; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2); }
       
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-form-container">
        <div class="admin-form-header">
            <h1>Edit User</h1>
            <a href="index.php" class="btn btn-secondary">← Back</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/admin/userController.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="userId" value="<?php echo htmlspecialchars($user['id']); ?>">

            <div class="form-section">
                <h3>User Information</h3>
                
                <div class="info-row" style="margin-bottom: 20px;">
                    <div class="info-item">
                        <label>User ID:</label>
                        <span><?php echo htmlspecialchars($user['id']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Created:</label>
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>

                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label>First Name *</label>
                        <input type="text" name="firstName" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="admin-form-group">
                        <label>Last Name *</label>
                        <input type="text" name="lastName" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="teacher" <?php echo ($user['user_type'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        <option value="student" <?php echo ($user['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="admin" <?php echo ($user['user_type'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="form-section">
                <h3>Password Management</h3>
                <div class="admin-form-group">
                    <label>
                        <input type="checkbox" id="changePassword" name="changePassword">
                        Change Password
                    </label>
            <div class="admin-form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary">View Details</a>
            </div>
        </form>
    </div>
</body>
</html>
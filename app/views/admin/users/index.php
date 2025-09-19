<?php
session_start();


require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/User.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$users = getAllUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users Management</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-users-container">
        <div class="admin-users-header">
            <h1>Users Management</h1>
            <div>
                <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="create.php" class="btn btn-primary">+ Add User</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <table class="admin-users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <h3>No users found</h3>
                            <p>Start by adding your first user</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="role <?php echo htmlspecialchars($user['user_type']); ?>"><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classId = $_GET['id'] ?? '';
if (empty($classId)) {
    $_SESSION['error'] = 'Class ID is required.';
    header('Location: index.php');
    exit;
}

$class = getClassById($classId);
if (!$class) {
    $_SESSION['error'] = 'Class not found.';
    header('Location: index.php');
    exit;
}
?>
?>
<!DOCTYPE html>
<html>
<head>
    <title>Override Class Settings - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-form-container">
        <div class="form-header">
            <h2>Override Class Settings</h2>
            <p>Class: <strong><?php echo htmlspecialchars($class['class_name']); ?></strong> (<?php echo htmlspecialchars($class['class_code']); ?>)</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/admin/classController.php" class="admin-form">
            <input type="hidden" name="action" value="override">
            <input type="hidden" name="classId" value="<?php echo $classId; ?>">
            
            <div class="form-group">
                <label for="status">Class Status:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">-- Keep Current (<?php echo ucfirst($class['status']); ?>) --</option>
                    <option value="active" <?php echo $class['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $class['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo $class['status'] === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>

            <div class="form-group">
                <label for="max_students">Maximum Students:</label>
                <input type="number" name="max_students" id="max_students" 
                       value="<?php echo $class['max_students']; ?>" 
                       min="0" max="500" class="form-control"
                       placeholder="Current: <?php echo $class['max_students']; ?>">
                <small>Leave empty to keep current limit</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-warning" onclick="return validateForm()">Override Settings</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <a href="view.php?id=<?php echo $classId; ?>" class="btn btn-primary">View Class Details</a>
            </div>
        </form>


        <div class="info-section">
            <h3>Current Class Information</h3>
            <div class="info-grid">
                <div><strong>Status:</strong> <?php echo ucfirst($class['status']); ?></div>
                <div><strong>Max Students:</strong> <?php echo $class['max_students']; ?></div>
                <div><strong>Created:</strong> <?php echo $class['created_at'] ?? 'N/A'; ?></div>
                <div><strong>Teacher ID:</strong> <?php echo $class['teacher_id']; ?></div>
            </div>
        </div>
    </div>
</body>
</html>
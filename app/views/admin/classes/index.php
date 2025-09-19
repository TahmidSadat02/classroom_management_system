<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classes = getAllClassesForAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Class Oversight</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="classes-container">
        <div class="classes-header">
            <h1>Class Oversight</h1>
            <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
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

        <table class="classes-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Teacher</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Today's Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                            <h3>No classes found</h3>
                            <p>No classes are currently in the system</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($class['class_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($class['class_code']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?><br>
                                <small><?php echo $class['course_name'] ? htmlspecialchars($class['course_name']) : 'No course'; ?></small>
                            </td>
                            <td><?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></td>
                            <td><span class="status-badge <?php echo $class['status']; ?>"><?php echo ucfirst($class['status']); ?></span></td>
                            <td>
                                <?php if ($class['today_activities'] > 0): ?>
                                    <span class="activity-indicator"><?php echo $class['today_activities']; ?> activities</span>
                                <?php else: ?>
                                    <small style="color: #6c757d;">No activity</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="../../../controllers/admin/classController.php?action=access&id=<?php echo $class['id']; ?>" class="btn btn-primary">Access</a>
                                <a href="override.php?id=<?php echo $class['id']; ?>" class="btn btn-warning">Override</a>
                                <?php if ($class['status'] === 'active'): ?>
                                    <button onclick="suspendClass(<?php echo $class['id']; ?>)" class="btn btn-danger">Suspend</button>
                                <?php else: ?>
                                    <button onclick="activateClass(<?php echo $class['id']; ?>)" class="btn btn-success">Activate</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    function suspendClass(classId) {
        if (confirm('Are you sure you want to suspend this class? This will make it inactive.')) {
            submitClassAction(classId, 'suspend');
        }
    }
    
    function activateClass(classId) {
        if (confirm('Are you sure you want to activate this class? This will make it active.')) {
            submitClassAction(classId, 'activate');
        }
    }
    
    function submitClassAction(classId, action) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '../../../controllers/admin/classController.php';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        
        var classIdInput = document.createElement('input');
        classIdInput.type = 'hidden';
        classIdInput.name = 'classId';
        classIdInput.value = classId;
        
        form.appendChild(actionInput);
        form.appendChild(classIdInput);
        document.body.appendChild(form);
        form.submit();
    }
    </script>
</body>
</html>

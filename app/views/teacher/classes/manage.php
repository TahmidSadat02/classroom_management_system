<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classId = $_GET['id'] ?? '';
if (empty($classId)) {
    header('Location: index.php');
    exit;
}

$class = getClassById($classId);
if (!$class) {
    $_SESSION['error'] = 'Class not found.';
    header('Location: index.php');
    exit;
}

$enrolledStudents = getEnrolledStudents($classId);
$availableStudents = getAvailableStudents($classId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Class - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Manage class styles */
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .manage-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .class-header { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .class-info { display: flex; justify-content: space-between; align-items: center; }
        .enrollment-sections { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .section h3 { margin: 0 0 15px 0; color: #333; }
        .student-list { max-height: 400px; overflow-y: auto; }
        .student-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e9ecef; }
        .student-item:last-child { border-bottom: none; }
        .btn { display: inline-block; padding: 8px 16px; border: none; border-radius: 5px; text-decoration: none; font-size: 0.9rem; cursor: pointer; margin-right: 5px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .success-message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
        .error-message { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
        .empty-state { text-align: center; padding: 20px; color: #6c757d; }
        @media (max-width: 768px) { .enrollment-sections { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="manage-container">
        <div class="class-header">
            <div class="class-info">
                <div>
                    <h1><?php echo htmlspecialchars($class['class_name']); ?></h1>
                    <p>Class Code: <strong><?php echo htmlspecialchars($class['class_code']); ?></strong> | 
                       Students: <strong><?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></strong></p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">← Back to Classes</a>
                    <a href="view.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
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

        <div class="enrollment-sections">
            <div class="section">
                <h3>Enrolled Students (<?php echo count($enrolledStudents); ?>)</h3>
                <div class="student-list">
                    <?php if (empty($enrolledStudents)): ?>
                        <div class="empty-state">
                            <p>No students enrolled yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($enrolledStudents as $student): ?>
                            <div class="student-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($student['email']); ?></small><br>
                                    <small>Enrolled: <?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></small>
                                </div>
                                <form method="POST" action="../../../controllers/teacher/classController.php" style="display: inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="classId" value="<?php echo $class['id']; ?>">
                                    <input type="hidden" name="studentId" value="<?php echo $student['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Remove this student from the class?')">Remove</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section">
                <h3>Available Students</h3>
                <div class="student-list">
                    <?php if (empty($availableStudents)): ?>
                        <div class="empty-state">
                            <p>No available students to enroll.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($availableStudents as $student): ?>
                            <div class="student-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($student['email']); ?></small>
                                </div>
                                <form method="POST" action="../../../controllers/teacher/classController.php" style="display: inline;">
                                    <input type="hidden" name="action" value="enroll">
                                    <input type="hidden" name="classId" value="<?php echo $class['id']; ?>">
                                    <input type="hidden" name="studentId" value="<?php echo $student['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm" 
                                            <?php echo ($class['enrolled_count'] >= $class['max_students']) ? 'disabled title="Class is full"' : ''; ?>>
                                        Enroll
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

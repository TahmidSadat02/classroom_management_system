<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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
$activities = getClassActivities($classId, 15);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Class Details - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .class-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .class-header { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .class-sections { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .section h3 { margin: 0 0 15px 0; color: #333; border-bottom: 2px solid #667eea; padding-bottom: 5px; }

        .student-list { max-height: 300px; overflow-y: auto; }
        .student-item { display: flex; justify-content: space-between; padding: 8px; border-bottom: 1px solid #e9ecef; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 5px; text-decoration: none; margin-right: 10px; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        @media (max-width: 768px) { .class-sections { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="class-container">
        <div class="class-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1><?php echo htmlspecialchars($class['class_name']); ?></h1>
                    <p><strong>Code:</strong> <?php echo htmlspecialchars($class['class_code']); ?> | 
                       <strong>Course:</strong> <?php echo !empty($class['course_name']) ? htmlspecialchars($class['course_name']) : 'No course'; ?> | 
                       <strong>Students:</strong> <?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-secondary">← Back</a>
                
                </div>
            </div>
        </div>

        
        <div style="margin-top: 20px;">
            <div class="section">
                <h3>Enrolled Students (<?php echo count($enrolledStudents); ?>)</h3>
                <div class="student-list">
                    <?php if (empty($enrolledStudents)): ?>
                        <p style="color: #6c757d; text-align: center; padding: 20px;">No students enrolled</p>
                    <?php else: ?>
                        <?php foreach ($enrolledStudents as $student): ?>
                            <div class="student-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($student['email']); ?></small>
                                </div>
                                <small>Enrolled: <?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

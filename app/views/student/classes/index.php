<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../../models/StudentModel.php';

// student er class gula ber korar jonno function
$studentId = $_SESSION['user_id'];
$classes = getStudentClasses($studentId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Classes</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="admin-container">
        <nav class="sidebar">
            <h2>Student Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="index.php">My Classes</a></li>
                <li><a href="../assignments/index.php">Assignments</a></li>
                <li><a href="../attendance/index.php">Attendance</a></li>
                <li><a href="../materials/index.php">Materials</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <div class="page-header">
                <h2>My Classes</h2>
                <p>View your enrolled classes, courses, and scheduled assignments</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Course</th>
                            <th>Teacher</th>
                            <th>Assignments</th>
                            <th>Materials</th>
                            <th>Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                    No classes found
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
                                        <?php if (!empty($class['course_name'])): ?>
                                            <strong><?php echo htmlspecialchars($class['course_name']); ?></strong><br>
                                            <?php if (!empty($class['course_code'])): ?>
                                                <small><?php echo htmlspecialchars($class['course_code']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <em>No course assigned</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?></td>
                                    <td>
                                        <?php if (!empty($class['assignments'])): ?>
                                            <?php 
                                            $pending = 0;
                                            $submitted = 0;
                                            $overdue = 0;
                                            foreach ($class['assignments'] as $assignment) {
                                                if ($assignment['status'] == 'pending') $pending++;
                                                elseif ($assignment['status'] == 'submitted') $submitted++;
                                                elseif ($assignment['status'] == 'overdue') $overdue++;
                                            }
                                            ?>
                                            <div style="font-size: 0.9em;">
                                                <?php if ($pending > 0): ?>
                                                    <span class="role admin" style="background: #ffc107; color: #212529; margin: 2px;"><?php echo $pending; ?> Pending</span>
                                                <?php endif; ?>
                                                <?php if ($submitted > 0): ?>
                                                    <span class="role student" style="margin: 2px;"><?php echo $submitted; ?> Submitted</span>
                                                <?php endif; ?>
                                                <?php if ($overdue > 0): ?>
                                                    <span class="role teacher" style="margin: 2px;"><?php echo $overdue; ?> Overdue</span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="../assignments/index.php?class_id=<?php echo $class['id']; ?>" 
                                               style="font-size: 0.8em; color: #667eea;">View Details →</a>
                                        <?php else: ?>
                                            <em style="color: #6c757d;">No assignments</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($class['recent_materials'])): ?>
                                            <div style="font-size: 0.9em;">
                                                <span class="role student" style="margin: 2px;"><?php echo count($class['recent_materials']); ?> Materials</span>
                                            </div>
                                            <?php if (count($class['recent_materials']) > 0): ?>
                                                <div style="font-size: 0.8em; color: #6c757d; margin-top: 5px;">
                                                    Latest: <?php echo htmlspecialchars($class['recent_materials'][0]['title']); ?>
                                                    <br><small><?php echo date('M d', strtotime($class['recent_materials'][0]['created_at'])); ?></small>
                                                </div>
                                            <?php endif; ?>
                                            <a href="../materials/index.php?class_id=<?php echo $class['id']; ?>" 
                                               style="font-size: 0.8em; color: #667eea;">View All →</a>
                                        <?php else: ?>
                                            <em style="color: #6c757d;">No materials</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($class['enrollment_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
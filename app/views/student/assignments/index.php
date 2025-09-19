<?php
session_start();
//session use kortesi jate login user user er data nite pari

// check kortesi je user login kora kina ebong user type "student" kina
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

// database connection r assignment model er database include kortesi
require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';

// logged in student er id session theke nitesi
$studentId = $_SESSION['user_id'];
$assignments = getStudentAssignments($studentId); // oi student er assignments gula database theke fetch kortesi

?>
<!DOCTYPE html>
<html>
<head>
    <title>Assignments</title>
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
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="index.php">Assignments</a></li>
                <li><a href="../attendance/index.php">Attendance</a></li>
                <li><a href="../materials/index.php">Materials</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
            </ul>
        </nav>
        
        <main class="content">
            <div class="page-header">
                <h2>Assignments</h2>
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
                            <th>Assignment</th>
                            <th>Class</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                                    No assignments found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                        <?php if ($assignment['description']): ?>
                                            <br><small style="color: #6c757d;"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)); ?><?php echo strlen($assignment['description']) > 100 ? '...' : ''; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['class_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></td>
                                    <td>
                                        <?php if ($assignment['is_submitted']): ?>
                                            <span class="role admin">Submitted</span>
                                        <?php elseif (strtotime($assignment['due_date']) < time()): ?>
                                            <span class="role teacher">Overdue</span>
                                        <?php else: ?>
                                            <span class="role student">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$assignment['is_submitted'] && strtotime($assignment['due_date']) >= time()): ?>
                                            <a href="submit.php?id=<?php echo $assignment['id']; ?>" class="btn btn-primary">Submit</a>
                                        <?php else: ?>
                                            <span style="color: #6c757d;">-</span>
                                        <?php endif; ?>
                                    </td>
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
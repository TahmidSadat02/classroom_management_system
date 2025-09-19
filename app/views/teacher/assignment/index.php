<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$teacherId = $_SESSION['user_id'];
$assignments = getTeacherAssignments($teacherId);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Assignments</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .teacher-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .content {
            flex: 1;
            padding: 30px;
        }

        .assignments-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .assignments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .assignments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .assignments-table th,
        .assignments-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="teacher-container">
        <nav class="sidebar">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
                <li><a href="../classes/index.php">My Classes</a></li>
                <li><a href="../content/index.php">Upload Content</a></li>
                <li><a href="../assignment/index.php">Assignments</a></li>
                <li><a href="../grades/index.php">Submit Grades</a></li>
                <li><a href="../attendance/index.php">Take Attendance</a></li>
                <li><a href="../../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="content">
            <div class="assignments-container">
                <div class="assignments-header">
                    <h1>Assignments</h1>
                    <a href="create.php" class="btn btn-primary">+ Create Assignment</a>
                </div>

                <?php if (empty($assignments)): ?>
                    <div class="empty-state">
                        <h3>No assignments created yet</h3>
                        <p>Start by creating your first assignment</p>
                        <a href="create.php" class="btn btn-primary">Create Assignment</a>
                    </div>
                <?php else: ?>
                    <table class="assignments-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Deadline</th>
                                <th>Points</th>
                                <th>Submissions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($assignment['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($assignment['class_name'] ?? 'No Class'); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['max_marks']); ?></td>
                                    <td>
                                        <span class="submission-count"><?php echo $assignment['submission_count']; ?></span>
                                        <?php if ($assignment['submission_count'] > 0): ?>
                                            <a href="submissions.php?id=<?php echo $assignment['id']; ?>" class="btn btn-success btn-sm">View</a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($assignment['visibility']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $assignment['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>
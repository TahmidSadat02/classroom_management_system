<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacherId = $_SESSION['user_id'];


$assignment = getAssignmentById($assignmentId);
if (!$assignment || $assignment['teacher_id'] != $teacherId) {
    header('Location: index.php?error=' . urlencode('Assignment not found or access denied'));
    exit;
}


$assignmentStats = getAssignmentSubmissionStats($assignmentId);
$submissions = getAssignmentSubmissions($assignmentId);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assignment Submissions - <?php echo htmlspecialchars($assignment['title']); ?></title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 10px;
            display: block;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background: #34495e;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .assignment-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .submissions-table th,
        .submissions-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
        }

        .submissions-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .btn {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            background: white;
            border-radius: 8px;
        }

        .submission-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-link {
            color: #007bff;
            text-decoration: none;
        }

        .file-link:hover {
            text-decoration: underline;
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
            <div class="page-header">
                <h1>Assignment Submissions</h1>
                <a href="index.php" class="btn btn-secondary">Back to Assignments</a>
            </div>

            <div class="assignment-info">
                <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
                <p><strong>Class:</strong> <?php echo htmlspecialchars($assignmentStats['class_name'] ?? 'No Class'); ?></p>
                <p><strong>Due Date:</strong> <?php echo date('M d, Y g:i A', strtotime($assignment['due_date'])); ?></p>
                <?php if ($assignment['description']): ?>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assignmentStats['total_students']; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assignmentStats['submitted_count']; ?></div>
                    <div class="stat-label">Submitted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assignmentStats['pending_count']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo $assignmentStats['total_students'] > 0 ? round(($assignmentStats['submitted_count'] / $assignmentStats['total_students']) * 100) : 0; ?>%
                    </div>
                    <div class="stat-label">Completion Rate</div>
                </div>
            </div>

            <?php if (empty($submissions)): ?>
                <div class="empty-state">
                    <h3>No submissions yet</h3>
                    <p>Students haven't submitted this assignment yet.</p>
                </div>
            <?php else: ?>
                <table class="submissions-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Email</th>
                            <th>Submission Text</th>
                            <th>File</th>
                            <th>Submitted At</th>
                            <th>Grade</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($submission['student_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($submission['student_email']); ?></td>
                                <td>
                                    <?php if ($submission['submission_text']): ?>
                                        <div class="submission-text" title="<?php echo htmlspecialchars($submission['submission_text']); ?>">
                                            <?php echo htmlspecialchars(substr($submission['submission_text'], 0, 100)); ?>
                                            <?php echo strlen($submission['submission_text']) > 100 ? '...' : ''; ?>
                                        </div>
                                    <?php else: ?>
                                        <em>No text submission</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($submission['file_path']): ?>
                                        <a href="../../../../uploads/assignments/<?php echo htmlspecialchars($submission['file_path']); ?>" 
                                           class="file-link" target="_blank">
                                            <?php echo htmlspecialchars($submission['file_path']); ?>
                                        </a>
                                    <?php else: ?>
                                        <em>No file</em>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y g:i A', strtotime($submission['submitted_at'])); ?></td>
                                <td>
                                    <?php if ($submission['grade'] !== null): ?>
                                        <?php echo htmlspecialchars($submission['grade']); ?>/<?php echo htmlspecialchars($assignment['max_marks']); ?>
                                    <?php else: ?>
                                        <em>Not graded</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
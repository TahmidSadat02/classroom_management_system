<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$teacherId = $_SESSION['user_id'];
$classes = getTeacherClasses($teacherId);
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Classes</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
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
            margin: 0 0 20px 0;
            text-align: center;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin-bottom: 10px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 0;
        }

        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 10px;
        }

        .content {
            flex: 1;
            padding: 30px;
            background: #f4f4f4;
        }

        .classes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .classes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .classes-header h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
        }

        .classes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .classes-table th,
        .classes-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .classes-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }

        .classes-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-badge.active {
            background: #28a745;
            color: white;
        }

        .status-badge.inactive {
            background: #6c757d;
            color: white;
        }

        .status-badge.completed {
            background: #17a2b8;
            color: white;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .teacher-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
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
            <div class="classes-container">
                <div class="classes-header">
                    <h1>My Classes</h1>
                    <div>
                        <a href="create.php" class="btn btn-primary">+ Create Class</a>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <table class="classes-table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Class Code</th>
                            <th>Course</th>
                            <th>Students</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <h3>No classes found</h3>
                                    <p>Start by creating your first class</p>
                                    <a href="create.php" class="btn btn-primary">Create Your First Class</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($class['class_code']); ?></code></td>
                                    <td><?php echo isset($class['course_name']) && $class['course_name'] ? htmlspecialchars($class['course_name']) : '<em>No course assigned</em>'; ?></td>
                                    <td><?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></td>
                                    <td><?php echo htmlspecialchars($class['semester'] . ' ' . $class['academic_year']); ?></td>
                                    <td><span class="status-badge <?php echo htmlspecialchars($class['status']); ?>"><?php echo ucfirst(htmlspecialchars($class['status'])); ?></span></td>
                                    <td>
                                        <a href="manage.php?id=<?php echo $class['id']; ?>" class="btn btn-success btn-sm">Manage</a>
                                        <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
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
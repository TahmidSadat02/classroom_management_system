<?php
session_start();


require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/Course.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}


$courses = getAllCourses();




?>
<!DOCTYPE html>
<html>

<head>
    <title>Courses Management</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .admin-courses-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-courses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .admin-courses-header h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
        }

        .admin-courses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .admin-courses-table th,
        .admin-courses-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .admin-courses-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }

        .admin-courses-table tbody tr:hover {
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

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-info {
            background: #17a2b8;
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

        .status-badge.archived {
            background: #dc3545;
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

        .info-message {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #bee5eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .course-code {
            font-weight: bold;
            color: #667eea;
        }

        .credits {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <div class="admin-courses-container">
        <div class="admin-courses-header">
            <h1>Courses Management</h1>
            <div>
                <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="create.php" class="btn btn-primary">+ Add Course</a>
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

        <?php if (isset($_SESSION['info'])): ?>
            <div class="info-message">
                <?php echo htmlspecialchars($_SESSION['info']);
                unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>

        <table class="admin-courses-table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Department</th>
                    <th>Credits</th>
                    <th>Teacher</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <h3>No courses found</h3>
                            <p>Start by adding your first course</p>
                            <a href="create.php" class="btn btn-primary">Create First Course</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['department']); ?></td>
                            <td><span class="credits"><?php echo htmlspecialchars($course['credits']); ?> Credits</span></td>
                            <td><?php
                                $teacherName = getTeacherName($course['teacher_id']);
                                echo $teacherName ? htmlspecialchars($teacherName) : '<em>Unassigned</em>';
                                ?></td>
                            <td><?php echo htmlspecialchars($course['semester'] . ' ' . $course['academic_year']); ?></td>
                            <td><span class="status-badge <?php echo htmlspecialchars($course['status']); ?>"><?php echo ucfirst(htmlspecialchars($course['status'])); ?></span></td>
                            <td>
                                <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-warning btn-sm" title="Edit Course">Edit</a>
                                <a href="delete.php?id=<?php echo $course['id']; ?>" class="btn btn-danger btn-sm" title="Delete Course">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
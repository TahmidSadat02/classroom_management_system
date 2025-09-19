<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$teacherId = $_SESSION['user_id'];

$classes = getTeacherClasses($teacherId); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gradebook</title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
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
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #667eea;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn:hover {
            background: #4953b8;
        }

        .btn2 {
            display: inline-block;
            padding: 8px 15px;
            background: #E4D00A;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn2:hover {
            background: #8B8000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f1f1f1;
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
            <div class="card">
                <h1>Gradebook - My Classes</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <div style="color:red;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div style="color:green;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (!empty($classes)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Class Code</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($class['class_code']); ?></td>
                                    <td>
                                        <a class="btn" href="view.php?class_id=<?php echo $class['id']; ?>">Add Grade</a>
                                        <a class="btn2" href="viewGrade.php?class_id=<?php echo $class['id']; ?>">View Student</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No classes found. Create a class first.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

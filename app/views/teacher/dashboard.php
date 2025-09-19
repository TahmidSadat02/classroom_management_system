<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../models/ClassModel.php';


$teacherId = $_SESSION['user_id'];
$classes = getTeacherClasses($teacherId);
$totalClasses = count($classes);
$activeClasses = count(array_filter($classes, fn($class) => $class['status'] === 'active'));

$sql = "SELECT COUNT(DISTINCT e.student_id) AS total_students
        FROM enrollments e
        INNER JOIN classes c ON e.class_id = c.id
        WHERE c.teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalStudents = $row['total_students'] ?? 0;
$stmt->close();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../../../public/css/style.css">
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

        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .stat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            min-width: 200px;
        }

        .stat h3 {
            font-size: 2rem;
            margin: 0 0 10px 0;
        }

        .stat p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .teacher-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .stats {
                flex-direction: column;
            }
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="teacher-container">
        <nav class="sidebar">
            <h2>Teacher Panel</h2>
            <ul>
                <li><a href="dashboard.php">
                    <img src="../../../public/assets/menu.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Dashboard
                </a></li>
                <li><a href="classes/index.php">
                    <img src="../../../public/assets/class.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    My Classes
                </a></li>
                <li><a href="content/index.php">
                    <img src="../../../public/assets/content.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Upload Content
                </a></li>
                <li><a href="assignment/index.php">
                    <img src="../../../public/assets/assignment.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Assignment
                </a></li>
                <li><a href="grades/index.php">
                    <img src="../../../public/assets/grade.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Submit Grades
                </a></li>
                <li><a href="attendance/index.php">
                    <img src="../../../public/assets/attendance.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Take Attendance
                </a></li>
                <li><a href="../auth/logout.php">
                    <img src="../../../public/assets/logout.png" alt="Dashboard" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;">
                    Logout
                </a></li>
            </ul>
        </nav>

        <main class="content">
            <h1>Teacher Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>

            <div class="stats">
                <div class="stat">
                    <h3><?php echo $totalClasses; ?></h3>
                    <p>Total Classes</p>
                </div>
                <div class="stat">
                    <h3><?php echo $activeClasses; ?></h3>
                    <p>Active Classes</p>
                </div>
                <div class="stat">
                    <h3><?php echo $totalStudents; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
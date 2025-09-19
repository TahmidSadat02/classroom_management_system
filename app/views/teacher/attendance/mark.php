<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classId = $_GET['class_id'] ?? null;

if (!$classId) {
    $_SESSION['error'] = "Class ID missing.";
    header("Location: index.php");
    exit;
}

if (!empty($_SESSION['students'])) {
    $students = $_SESSION['students'];
    unset($_SESSION['students']);
} else {
    $classId = intval($classId);
    $sql = "SELECT u.id, u.first_name, u.last_name, u.email
            FROM enrollments e
            INNER JOIN users u ON e.student_id = u.id
            WHERE e.class_id = $classId AND e.status = 'enrolled'";

    $result = $conn->query($sql);
    $students = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f1f1f1;
        }

        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background: #4953b8;
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
                <h1>Mark Attendance</h1>
                <?php if (empty($students)): ?>
                    <p>No students enrolled in this class.</p>
                <?php else: ?>
                    <form method="POST" action="../../../controllers/teacher/attendanceController.php">
                        <input type="hidden" name="action" value="save_attendance">
                        <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($classId); ?>">

                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td>
                                            <select name="status[<?php echo $student['id']; ?>]" required>
                                                <option value="present">Present</option>
                                                <option value="absent">Absent</option>
                                                <option value="late">Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[<?php echo $student['id']; ?>]" placeholder="Optional notes">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <br>
                        <button type="submit" class="btn">Save Attendance</button>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>
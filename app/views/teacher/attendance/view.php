<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';

// Ensure DB connection
if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classId = $_GET['class_id'] ?? null;
if (!$classId) {
    $_SESSION['error'] = "No class selected.";
    header("Location: index.php");
    exit;
}

$sql = "SELECT a.id, a.student_id, a.class_id, a.date AS attendance_date, a.status, a.notes, a.updated_at,
               CONCAT(u.first_name, ' ', u.last_name) AS student_name,
               u.email,
               c.class_name
        FROM attendance a
        JOIN users u ON a.student_id = u.id AND u.user_type = 'student'
        JOIN classes c ON a.class_id = c.id
        WHERE a.class_id = ?
        ORDER BY a.date DESC, a.updated_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();
$attendanceRecords = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Attendance</title>
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
            text-align: center;
            margin-bottom: 20px;
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
            background: #34495e;
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
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-save {
            background: #2ecc71;
            color: white;
        }

        .btn-export {
            background: #f39c12;
            color: white;
            float: right;
            text-decoration: none;
            padding: 8px 14px;
        }

        input[type="date"] {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        input[type="text"] {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 100%;
            box-sizing: border-box;
        }

        select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
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

            <?php if (!empty($_SESSION['success'])): ?>
                <div style="padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom:15px;">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div style="padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; margin-bottom:15px;">
                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h1>Attendance for Class: <?php echo htmlspecialchars($attendanceRecords[0]['class_name'] ?? ''); ?></h1>
                <a href="../../../controllers/teacher/exportAttendance.php?class_id=<?php echo urlencode($classId); ?>" class="btn-export">Export to CSV</a>

                <?php if (empty($attendanceRecords)): ?>
                    <p>No attendance records for this class yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $a): ?>
                                <tr>
                                    <form action="../../../controllers/teacher/attendanceController.php" method="POST" style="margin:0;">
                                        <td><?php echo htmlspecialchars($a['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($a['email']); ?></td>
                                        <td>
                                            <!-- name matches controller: attendance_date -->
                                            <input type="date" name="attendance_date" value="<?php echo htmlspecialchars($a['attendance_date']); ?>">
                                        </td>
                                        <td>
                                            <select name="status">
                                                <option value="present" <?php if ($a['status'] == 'present') echo 'selected'; ?>>Present</option>
                                                <option value="absent" <?php if ($a['status'] == 'absent') echo 'selected'; ?>>Absent</option>
                                                <option value="late" <?php if ($a['status'] == 'late') echo 'selected'; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="notes" value="<?php echo htmlspecialchars($a['notes']); ?>"></td>
                                        <td><?php echo htmlspecialchars($a['updated_at']); ?></td>
                                        <td>
                                            <input type="hidden" name="action" value="update_attendance">
                                            <input type="hidden" name="attendance_id" value="<?php echo intval($a['id']); ?>">
                                            <input type="hidden" name="class_id" value="<?php echo intval($classId); ?>">
                                            <button type="submit" class="btn btn-save">Save</button>
                                        </td>
                                    </form>
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
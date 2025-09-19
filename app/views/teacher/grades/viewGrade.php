<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';

// Ensure $conn is initialized
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

$sql = "SELECT g.id, g.student_id, g.class_id, g.marks, g.grade, g.assessment_type, g.updated_at,
               CONCAT(u.first_name, ' ', u.last_name) AS student_name,
               c.class_name
        FROM grades g
        JOIN users u ON g.student_id = u.id AND u.user_type = 'student'
        JOIN classes c ON g.class_id = c.id
        WHERE g.class_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();
$grades = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$maxMarks = 0;
foreach ($grades as $g) {
    if ($g['marks'] > $maxMarks) {
        $maxMarks = $g['marks'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Student Grades</title>
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
        }

        .bar-container {
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            width: 100%;
            height: 24px;
        }

        .bar {
            height: 100%;
            background: #3498db;
            text-align: right;
            padding-right: 5px;
            color: white;
            font-size: 12px;
            line-height: 24px;
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
                <h1>Grades for Class: <?php echo htmlspecialchars($grades[0]['class_name'] ?? ''); ?></h1>
                <a href="../../../controllers/teacher/exportGrades.php?class_id=<?php echo $classId; ?>" class="btn btn-export">Export to CSV</a>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Assessment Type</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <form action="../../../controllers/teacher/gradeController.php" method="POST">
                                    <td><?php echo htmlspecialchars($g['student_name']); ?></td>
                                    <td><input type="number" step="0.01" name="marks" value="<?php echo htmlspecialchars($g['marks']); ?>"></td>
                                    <td><input type="text" name="grade" value="<?php echo htmlspecialchars($g['grade']); ?>"></td>
                                    <td>
                                        <select name="assessment_type">
                                            <option value="assignment" <?php if ($g['assessment_type'] == 'assignment') echo 'selected'; ?>>Assignment</option>
                                            <option value="quiz" <?php if ($g['assessment_type'] == 'quiz') echo 'selected'; ?>>Quiz</option>
                                            <option value="midterm" <?php if ($g['assessment_type'] == 'midterm') echo 'selected'; ?>>MidTerm</option>
                                            <option value="final" <?php if ($g['assessment_type'] == 'final') echo 'selected'; ?>>Final</option>
                                        </select>
                                    </td>
                                    <td><?php echo htmlspecialchars($g['updated_at']); ?></td>
                                    <td>
                                        <input type="hidden" name="action" value="update_grade"> <!-- Added action field -->
                                        <input type="hidden" name="grade_id" value="<?php echo $g['id']; ?>">
                                        <input type="hidden" name="class_id" value="<?php echo $classId; ?>">
                                        <button type="submit" class="btn btn-save">Save</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h2>Student Grade Report (Bar Chart)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Marks</th>
                            <th>Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($g['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($g['marks']); ?></td>
                                <td>
                                    <div class="bar-container">
                                        <?php
                                        $percent = $maxMarks > 0 ? ($g['marks'] / $maxMarks) * 100 : 0;
                                        ?>
                                        <div class="bar" style="width:<?php echo $percent; ?>%">
                                            <?php echo $g['marks']; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>
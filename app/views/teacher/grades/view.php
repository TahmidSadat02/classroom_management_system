<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$teacherId = $_SESSION['user_id'];
$classId = $_GET['class_id'] ?? null;

if (!$classId) {
    $_SESSION['error'] = 'No class selected.';
    header('Location: index.php');
    exit;
}

$class = getClassById($classId);
if (!$class) {
    $_SESSION['error'] = 'Class not found.';
    header('Location: index.php');
    exit;
}

$sql = "SELECT u.id as student_id, u.first_name, u.last_name
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.class_id = $classId AND e.status = 'enrolled'";
$result = $conn->query($sql);

$students = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gradebook - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="../../../../public/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
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
            padding: 8px 15px;
            background: #667eea;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            border: none;
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
                <h1>Gradebook - <?php echo htmlspecialchars($class['class_name']); ?></h1>

                <?php if (isset($_SESSION['error'])): ?>
                    <div style="color:red;"><?php echo htmlspecialchars($_SESSION['error']);
                                            unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div style="color:green;"><?php echo htmlspecialchars($_SESSION['success']);
                                                unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (!empty($students)): ?>
                    <form action="../../../controllers/teacher/gradeController.php" method="POST">
                        <input type="hidden" name="action" value="save_grades">
                        <input type="hidden" name="class_id" value="<?php echo $classId; ?>">

                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Assessment Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                        <td>
                                            <input type="number"
                                                name="marks[<?php echo $student['student_id']; ?>]"
                                                min="0" max="100" required
                                                oninput="calculateGrade(this)">
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="grade[<?php echo $student['student_id']; ?>]"
                                                readonly
                                                placeholder="Grade will auto-fill">
                                        </td>
                                        <td>
                                            <select name="assessment_type[<?php echo $student['student_id']; ?>]">
                                                <option value="Assignment">Assignment</option>
                                                <option value="Quiz">Quiz</option>
                                                <option value="Midterm">Midterm</option>
                                                <option value="Final">Final</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <br>
                        <button type="submit" class="btn">Save Grades</button>
                    </form>
                <?php else: ?>
                    <p>No students enrolled in this class yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function calculateGrade(input) {
            let marks = parseFloat(input.value);
            let gradeField = input.closest("tr").querySelector("input[name^='grade']");

            if (isNaN(marks)) {
                gradeField.value = "";
                return;
            }

            let grade = "F"; // default
            if (marks >= 90) grade = "A+";
            else if (marks >= 85) grade = "A";
            else if (marks >= 80) grade = "B+";
            else if (marks >= 75) grade = "B";
            else if (marks >= 70) grade = "C+";
            else if (marks >= 65) grade = "C";
            else if (marks >= 60) grade = "D+";
            else if (marks >= 50) grade = "D";
            else grade = "F";

            gradeField.value = grade;
        }
    </script>

</body>

</html>
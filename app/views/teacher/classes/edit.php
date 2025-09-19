<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$classId = $_GET['id'] ?? '';
if (!$classId) {
    $_SESSION['error'] = 'No class selected for editing.';
    header('Location: index.php');
    exit;
}

$class = getClassById($classId);
if (!$class) {
    $_SESSION['error'] = 'Class not found.';
    header('Location: index.php');
    exit;
}

// For course dropdown
require_once __DIR__ . '/../../../models/Course.php';
$courses = getAllCourses();

$schedule = $class['schedule'] ?? [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Class</title>
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

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea,
        input[type="time"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #667eea;
            color: white;
            cursor: pointer;
        }

        .btn:hover {
            background: #4953b8;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
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
            <div class="form-container">
                <h1>Edit Class</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']);
                                                unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']);
                                                    unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <form action="../../../controllers/teacher/classController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="classId" value="<?php echo htmlspecialchars($class['id']); ?>">

                    <div class="form-group">
                        <label for="className">Class Name</label>
                        <input type="text" id="className" name="className" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="classCode">Class Code</label>
                        <input type="text" id="classCode" name="classCode" value="<?php echo htmlspecialchars($class['class_code']); ?>" disabled>
                        <small style="color:#888;">Class code cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($class['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="courseId">Course</label>
                        <select id="courseId" name="courseId" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php if ($class['course_id'] == $course['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($class['semester']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="academicYear">Academic Year</label>
                        <input type="text" id="academicYear" name="academicYear" value="<?php echo htmlspecialchars($class['academic_year']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="maxStudents">Max Students</label>
                        <input type="number" id="maxStudents" name="maxStudents" value="<?php echo htmlspecialchars($class['max_students']); ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Schedule</label>
                        <label>Days (hold Ctrl to select multiple):</label>
                        <select name="days[]" multiple>
                            <?php
                            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $selectedDays = $schedule['days'] ?? [];
                            foreach ($daysOfWeek as $day): ?>
                                <option value="<?php echo $day; ?>" <?php if (in_array($day, $selectedDays)) echo 'selected'; ?>>
                                    <?php echo $day; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="start_time">Start Time:</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($schedule['start_time'] ?? ''); ?>">
                        <label for="end_time">End Time:</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($schedule['end_time'] ?? ''); ?>">
                        <label for="room">Room:</label>
                        <input type="text" id="room" name="room" value="<?php echo htmlspecialchars($schedule['room'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php if ($class['status'] === 'active') echo 'selected'; ?>>Active</option>
                            <option value="inactive" <?php if ($class['status'] === 'inactive') echo 'selected'; ?>>Inactive</option>
                            <option value="completed" <?php if ($class['status'] === 'completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Update Class</button>
                    <a href="index.php" class="btn" style="background:#6c757d;">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
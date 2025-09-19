<?php
session_start();

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/AssignmentModel.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

$assignmentId = $_GET['id'] ?? '';
if (!$assignmentId) {
    $_SESSION['error'] = 'No assignment selected for editing.';
    header('Location: index.php');
    exit;
}

$assignment = getAssignmentById($assignmentId);
if (!$assignment) {
    $_SESSION['error'] = 'Assignment not found.';
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Assignment</title>
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
        input[type="date"],
        input[type="number"],
        select,
        textarea {
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
                <h1>Edit Assignment</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']);
                                                unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']);
                                                    unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <form action="../../../controllers/teacher/assignmentController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="assignmentId" value="<?php echo htmlspecialchars($assignment['id']); ?>">

                    <div class="form-group">
                        <label for="title">Assignment Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($assignment['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="date" id="deadline" name="deadline"
                            value="<?php echo isset($assignment['due_date']) ? date('Y-m-d', strtotime($assignment['due_date'])) : ''; ?>"
                            required>
                    </div>


                    <div class="form-group">
                        <label for="max_points">Max Points</label>
                        <input type="number" id="max_points" name="max_points" value="<?php echo htmlspecialchars($assignment['max_marks']); ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="visible" <?php echo (isset($assignment['status']) && $assignment['status'] === 'visible') ? 'selected' : ''; ?>>Visible</option>
                            <option value="hidden" <?php echo (isset($assignment['status']) && $assignment['status'] === 'hidden') ? 'selected' : ''; ?>>Hidden</option>
                        </select>
                    </div>

                    <button type="submit" class="btn">Update Assignment</button>
                    <a href="index.php" class="btn" style="background:#6c757d;">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
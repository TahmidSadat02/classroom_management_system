<?php
session_start();

// Check if user is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../../auth/login.php?error=' . urlencode('Access denied'));
    exit;
}

require_once __DIR__ . '/../../../models/Database.php';
require_once __DIR__ . '/../../../models/ClassModel.php';

$teacherId = $_SESSION['user_id'];
$contentId = $_GET['id'] ?? '';

if (empty($contentId)) {
    $_SESSION['error'] = 'Content ID is required.';
    header('Location: index.php');
    exit;
}

// Get content details
$content = getContentById($contentId);
if (!$content || $content['teacher_id'] != $teacherId) {
    $_SESSION['error'] = 'Content not found or access denied.';
    header('Location: index.php');
    exit;
}

$classes = getTeacherClasses($teacherId);

$topics = getContentTopics($teacherId);

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Content - Teacher</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
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
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 24px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }

        .btn:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .alert {
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .file-info h4 {
            margin: 0 0 10px 0;
            color: #495057;
        }
    </style>
</head>

<body>
    <div class="container">
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
            <h1>Edit Content</h1>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($content['file_name']): ?>
                <div class="file-info">
                    <h4>Current File</h4>
                    <p><strong>File:</strong> <?php echo htmlspecialchars($content['file_name']); ?></p>
                    <p><strong>Size:</strong> <?php echo number_format($content['file_size'] / 1024, 1); ?> KB</p>
                    <p><strong>Type:</strong> <?php echo strtoupper($content['file_type']); ?></p>
                    <p><em>Note: To change the file, delete this content and create a new one with file upload.</em></p>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="../../controllers/teacher/contentController.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="content_id" value="<?php echo $content['id']; ?>">

                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($content['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($content['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $content['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="topic">Topic</label>
                        <input type="text" id="topic" name="topic" value="<?php echo htmlspecialchars($content['topic']); ?>" list="topics">
                        <datalist id="topics">
                            <?php foreach ($topics as $topic): ?>
                                <option value="<?php echo htmlspecialchars($topic['topic_name']); ?>">
                                <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label for="visibility">Visibility</label>
                        <select id="visibility" name="visibility">
                            <option value="public" <?php echo $content['visibility'] == 'public' ? 'selected' : ''; ?>>Public</option>
                            <option value="private" <?php echo $content['visibility'] == 'private' ? 'selected' : ''; ?>>Private</option>
                            <option value="scheduled" <?php echo $content['visibility'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="available_from">Available From</label>
                        <input type="datetime-local" id="available_from" name="available_from"
                            value="<?php echo $content['available_from'] ? date('Y-m-d\TH:i', strtotime($content['available_from'])) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="available_until">Available Until</label>
                        <input type="datetime-local" id="available_until" name="available_until"
                            value="<?php echo $content['available_until'] ? date('Y-m-d\TH:i', strtotime($content['available_until'])) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">Update Content</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>

</html>
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

// Get teacher's classes
$classes = getTeacherClasses($teacherId);

// Get topics
$topics = getContentTopics($teacherId);

// Handle success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Topics - Teacher</title>
    <style>
        /* Copy styles from index.php for consistency */
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
        
        .btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #218838;
        }
        
        .content-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .alert {
            padding: 10px;
            margin: 10px 0;
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
            <h1>Manage Topics</h1>
            <p>Organize your content materials by topics for better structure.</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <a href="index.php" class="btn">← Back to Content</a>
            
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Topic Name</th>
                            <th>Description</th>
                            <th>Class</th>
                            <th>Sort Order</th>
                            <th>Materials Count</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topics)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    No topics found. Create topics from the Content Creation page to organize your materials!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topics as $topic): ?>
                                <?php
                                // Count materials for this topic
                                $materialCount = 0;
                                $materials = getTeacherContent($teacherId);
                                foreach ($materials as $material) {
                                    if ($material['topic'] == $topic['topic_name']) {
                                        $materialCount++;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($topic['topic_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($topic['description'] ?: 'No description'); ?></td>
                                    <td>
                                        <?php 
                                        if ($topic['class_id']) {
                                            foreach ($classes as $class) {
                                                if ($class['id'] == $topic['class_id']) {
                                                    echo htmlspecialchars($class['class_name']);
                                                    break;
                                                }
                                            }
                                        } else {
                                            echo 'All Classes';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $topic['sort_order']; ?></td>
                                    <td><?php echo $materialCount; ?> materials</td>
                                    <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>